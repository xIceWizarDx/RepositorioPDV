<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cadastro\Pessoa;
use App\Models\Cadastro\Vendedor;
use App\Models\Cadastro\TabelaAuxiliar\FormaPagamento;

class PdvController extends Controller
{
    /**
     * Recupera formas de pagamento ativas.
     * Erros de consulta são registrados e uma collection vazia é retornada.
     */
    private function getFormasPagamentoAtivas()
    {
        try {
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
        } catch (\Throwable $e) {
            Log::error('[PdvController@getFormasPagamentoAtivas] erro ao buscar formas de pagamento', [
                'message' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    /**
     * Exibe a tela principal do PDV.
     */
    public function index()
    {
        try {
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
                    ->map(fn ($v) => (int) $v)
                    ->all();

                $paymentConditions[$forma->id] = empty($conds) ? [0] : $conds;
            }

            Log::debug('[PdvController@index] paymentConditions final:', $paymentConditions);

            return view('pdv', compact(
                'clientes',
                'vendedores',
                'formasPagamento',
                'paymentConditions'
            ));
        } catch (\Throwable $e) {
            Log::error('[PdvController@index] erro ao carregar dados', ['message' => $e->getMessage()]);
            return view('pdv')->withErrors('Erro ao carregar dados do PDV.');
        }
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
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([], 422);
        }

        $term = trim($request->get('q', ''));
        $empresaId = (int) session('empresa.id', 0);

        Log::debug('[PdvController@searchProducts] Termo de busca:', ['q' => $term, 'empresaId' => $empresaId]);

        try {
            $produtos = \App\Models\Cadastro\Produto::orderBy('descricao')
                ->when($term, function ($q) use ($term) {
                    $q->where(function ($w) use ($term) {
                        $w->where('descricao', 'like', '%' . $term . '%')
                            ->orWhere('modelo', 'like', '%' . $term . '%')
                            ->orWhere('cEAN', 'like', $term . '%')
                            ->orWhere('codigo_ref', 'like', $term . '%');
                    });
                })
                ->limit(20)
                ->get();

            $precos = \App\Models\Cadastro\ProdutoPreco::where('empresa_id', $empresaId)
                ->whereIn('produto_id', $produtos->pluck('id'))
                ->get()
                ->keyBy('produto_id');

            Log::debug('[PdvController@searchProducts] Produtos encontrados:', $produtos->pluck('id', 'descricao')->toArray());

            $results = $produtos->map(fn ($p) => [
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
        } catch (\Throwable $e) {
            Log::error('[PdvController@searchProducts] erro:', ['message' => $e->getMessage()]);
            return response()->json([], 500);
        }
    }

    public function storeEmitirNota(Request $request)
    {
        Log::debug('===== [PdvController@storeEmitirNota] Início do debug =====');
        Log::debug('Payload completo:', $request->all());

        try {
            $data = $request->validate([
                'total'                    => 'required|numeric|min:0.01',
                'pagamento_personalizado'  => 'boolean',
                'detPag'                   => 'array',
                'detPag.*.tPag'            => 'required_with:detPag|string',
                'detPag.*.vPag'            => 'required_with:detPag|numeric|min:0.01',
            ]);

            $detPag = $data['detPag'] ?? [];
            if (!$data['pagamento_personalizado']) {
                // Quando a venda for à vista ignoramos detalhes duplicados
                $detPag = [];
            }

            $formasBanco   = $this->getFormasPagamentoAtivas();
            $codigosValidos = $formasBanco->pluck('tPag_code')->all();

            $valorPago = 0.0;
            foreach ($detPag as $i => $pag) {
                if (!in_array($pag['tPag'], $codigosValidos, true)) {
                    throw new \RuntimeException("Forma de pagamento inválida: {$pag['tPag']}");
                }
                $valorPago += (float) $pag['vPag'];
            }

            if (abs($valorPago - $data['total']) > 0.01) {
                throw new \RuntimeException('Soma dos pagamentos difere do total da venda.');
            }

            // Disparo fictício da geração de NFC-e.
            Log::info('[PdvController@storeEmitirNota] Nota fiscal emitida.', ['detPag' => $detPag]);

            return response()->json([
                'status'  => 'OK',
                'message' => 'Nota emitida com sucesso.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[PdvController@storeEmitirNota] erro:', ['message' => $e->getMessage()]);
            return response()->json([
                'status'  => 'NOK',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
