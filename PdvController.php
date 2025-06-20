<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cadastro\Pessoa;
use App\Models\Cadastro\Vendedor;
use App\Models\Cadastro\TabelaAuxiliar\FormaPagamento;

class PdvController extends Controller
{
    private function getFormasPagamentoAtivas()
    {
        $formas = FormaPagamento::select('id', 'descricao', 'sequencial', 'idNFCe', 'is_active', 'livre_pag')
            ->where('is_active', '>', 0)
            ->orderBy('sequencial', 'asc')
            ->get()
            ->map(function ($f) {
                $f->tPag_code = $f->idNFCe;
                $f->pagamento_personalizado = (bool) $f->livre_pag;

                return $f;
            });

        Log::debug('[PdvController@getFormasPagamentoAtivas] Formas ativadas:', $formas->toArray());

        return $formas;
    }

    public function index()
    {
        $empresaId = (int) session('empresa.id', 0);
        Log::debug('[PdvController@index] Empresa ID da sessão:', ['empresaId' => $empresaId]);

        $clientes = Pessoa::where('is_active', 1)
            ->orderBy('fantasia', 'asc')
            ->get();
        Log::debug('[PdvController@index] Clientes carregados:', $clientes->pluck('id', 'fantasia')->toArray());

        $vendedores = Vendedor::with('pessoa')
            ->where('empresa_id', $empresaId)
            ->get();
        Log::debug('[PdvController@index] Vendedores carregados:', $vendedores->pluck('id')->toArray());

        $formasPagamento = $this->getFormasPagamentoAtivas();
        $rawConditions = DB::connection('db_client')
            ->table('formas_pagamentos_cp')
            ->select(['descricao_id', 'pag_mod'])
            ->whereIn('descricao_id', $formasPagamento->pluck('id')->all())
            ->orderBy('sequencia', 'asc')
            ->get();
        Log::debug('[PdvController@index] Condições brutas do banco:', $rawConditions->toArray());

        $paymentConditions = [];

        foreach ($formasPagamento as $forma) {
            $conds = $rawConditions
                ->where('descricao_id', $forma->id)
                ->pluck('pag_mod')
                ->map(fn($v) => (int) $v)
                ->all();

            if (empty($conds)) {
                $conds = [0];
            }

            $paymentConditions[$forma->id] = $conds;
        }

        Log::debug('[PdvController@index] paymentConditions final:', $paymentConditions);

        return view('pdv', compact(
            'clientes',
            'vendedores',
            'formasPagamento',
            'paymentConditions'
        ));
    }

    public function outraView()
    {
        $formasPagamento = $this->getFormasPagamentoAtivas();
        return view('minhaoutraview', compact('formasPagamento'));
    }

    public function formasPagamentoApi()
    {
        $formasPagamento = $this->getFormasPagamentoAtivas();
        Log::debug('[PdvController@formasPagamentoApi] Retornando JSON de formasPagamento:', $formasPagamento->toArray());
        return response()->json($formasPagamento, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function debugView(Request $request)
    {
        $dados = $request->input('dados', []);
        $detPag = $request->input('detPag', []);
        $formasPagamento = $this->getFormasPagamentoAtivas();

        Log::debug('[PdvController@debugView] dados recebidos:', ['dados' => $dados]);
        Log::debug('[PdvController@debugView] detPag recebidos:', ['detPag' => $detPag]);

        return view('pdv-debug', compact('dados', 'formasPagamento', 'detPag'));
    }

    public function searchProducts(Request $request)
    {
        $term = trim($request->get('q', ''));
        $empresaId = (int) session('empresa.id', 0);

        Log::debug('[PdvController@searchProducts] Termo de busca:', ['q' => $term, 'empresaId' => $empresaId]);

        $produtos = \App\Models\Cadastro\Produto::orderBy('descricao')
            ->when(
                $term,
                fn($q) => $q
                    ->where('descricao', 'like', "%{$term}%")
                    ->orWhere('modelo', 'like', "%{$term}%")
                    ->orWhere('cEAN', 'like', "{$term}%")
                    ->orWhere('codigo_ref', 'like', "{$term}%")
            )
            ->limit(20)
            ->get();

        $precos = \App\Models\Cadastro\ProdutoPreco::where('empresa_id', $empresaId)
            ->whereIn('produto_id', $produtos->pluck('id'))
            ->get()
            ->keyBy('produto_id');

        Log::debug('[PdvController@searchProducts] Produtos encontrados:', $produtos->pluck('id', 'descricao')->toArray());

        $results = $produtos->map(fn($p) => [
            'id'          => $p->id,
            'text'        => trim("{$p->descricao} {$p->modelo}"),
            'preco_vista' => optional($precos->get($p->id))->preco_vista,
            'preco_prazo' => optional($precos->get($p->id))->preco_prazo,
            'estoque'     => \App\Models\Cadastro\Produto::getEstoqueOfProduto($empresaId, $p->id),
            'codigo_ref'  => $p->codigo_ref,
            'cEAN'        => $p->cEAN,
        ]);

        Log::debug('[PdvController@searchProducts] Resultado JSON:', $results->toArray());

        return response()->json($results);
    }

    public function storeEmitirNota(Request $request)
    {
        Log::debug('===== [PdvController@storeEmitirNota] Início do debug =====');
        Log::debug('Payload completo:', $request->all());
        Log::debug('detPag raw:', ['detPag' => $request->input('detPag')]);
        $detPag = $request->input('detPag');
        if ($detPag instanceof \stdClass) {
            $detPag = (array) $detPag;
            Log::debug('detPag convertido de stdClass para array:', $detPag);
        }
        Log::debug('Tipo de detPag após conversão:', ['tipo' => gettype($detPag)]);
        $custom = $request->boolean('pagamento_personalizado');
        Log::debug('pagamento_personalizado:', ['custom' => $custom]);
        if (! $custom) {
            Log::debug('Pagamento à vista: ignorando duplicatas');
            $detPag = [];
        }

        $formasBanco = $this->getFormasPagamentoAtivas()->toArray();
        Log::debug('Formas de pagamento no banco:', $formasBanco);

        $debugMsg = [
            'payload'     => $request->all(),
            'detPag'      => $detPag,
            'formasBanco' => $formasBanco,
            'erroValidacao' => null,
            'detPagTipo'  => gettype($detPag),
        ];

        if (!is_array($detPag)) {
            $debugMsg['erroValidacao'] = 'detPag não é array';
            Log::error('detPag não é array!', ['detPag' => $detPag]);
            return response()->json(['status' => 'NOK', 'data' => $debugMsg]);
        }

        foreach ($detPag as $i => $pag) {
            Log::debug("Processando detPag[{$i}]:", ['valor' => $pag]);
            if (is_object($pag)) {
                $pag = (array) $pag;
                Log::debug("detPag[{$i}] convertido de objeto para array:", $pag);
            }
            if (!is_array($pag) || !isset($pag['tPag']) || !isset($pag['vPag'])) {
                $debugMsg['erroValidacao'] = "detPag[{$i}] inválido: " . json_encode($pag);
                Log::error("Erro de validação em detPag[{$i}]:", ['pag' => $pag]);
                return response()->json(['status' => 'NOK', 'data' => $debugMsg]);
            }
        }

        Log::debug('detPag processado sem erros:', $detPag);

        return response()->json([
            'status'          => 'OK',
            'data'            => 'Nota emitida com sucesso (debug completo).',
            'detPag_recebido' => $detPag,
            'formasBanco'     => $formasBanco,
            'debugMsg'        => $debugMsg
        ]);
    }

    public function getStocks(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        $empresaId = (int) session('empresa.id', 0);

        $estoques = [];
        foreach ($ids as $id) {
            $estoques[$id] = \App\Models\Cadastro\Produto::getEstoqueOfProduto($empresaId, $id);
        }

        return response()->json($estoques);
    }
}
