<?php

namespace App\Http\Controllers\Venda;

use App\Helpers\Helper;
use App\Models\ApiFiscal;
use App\Models\Cadastro\Pessoa;
use App\Models\Cadastro\ProdutoEstoque;
use App\Models\Cadastro\ProdutoPreco;
use App\Models\Cadastro\TabelaAuxiliar\FormaPagamento;
use App\Models\Financeiro\CaixaAbertura;
use App\Models\Financeiro\CaixaMovimento;
use App\Models\Financeiro\Receita;
use App\Models\Fiscal\NFeNFCe;
use App\Models\Venda\Orcamento;
use App\Models\Venda\OrcamentoFinalizacao;
use App\Models\Venda\OrcamentoItens;
use App\Models\Venda\OrcamentoParcela;
use App\Models\Venda\TipoMovimento;
use App\Models\Parametro\ParametroFinanceiro;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;
use setasign\Fpdi\PdfParser\CrossReference\FixedReader;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use ZipArchive;


class OrcamentoController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('venda.orcamento.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function table(Request $request)
    {
        try {

            $empresa_id = intval($request->session()->get('empresa')['id']);
            $orcamento_id = intval($request->orcamento_id);
            $nome_razao = trim($request->nome_razao);
            $tipo_date = $request->tipo_date;
            $date1 = trim($request->date1);
            $date2 = trim($request->date2);
            $vendedor_id = intval($request->vendedor_id);
            $status = trim($request->status);

            $nf = NFeNFCe::where('orcamento_id', $orcamento_id)->first();

            $orcamentos = DB::connection('db_client')
                ->table('orcamentos as o')
                ->select([
                    'o.*',
                    DB::raw('concat(c.nome_razao) as cliente'),
                    DB::raw('concat(v.nome_razao) as vendedor'),
                    'tm.descricao as tipo_movimento',
                    'nf.id as nf_id',
                    'nf.ide_mod',
                    'nf.is_transmitido',
                    'nf.is_autorizado',
                    'nf.is_cancelado',
                    'nf.ide_nNF'
                ])
                ->join('pessoas as c', 'c.id', '=', 'o.cliente_id')
                ->join('pessoas as v', 'v.id', '=', 'o.vendedor_id')
                ->leftJoin('tipos_movimentos as tm', 'o.tipo_movimento_id', '=', 'tm.id')
                ->leftJoin('fiscal_nf as nf', 'o.id', '=', 'nf.orcamento_id')
                ->where('o.empresa_id', $empresa_id)
                ->orderBy('o.id','desc');

            if ($orcamento_id > 0)
                $orcamentos->where('o.id', $orcamento_id);
            if (!empty($nome_razao)) {
                $orcamentos->where(function ($query) use ($nome_razao) {
                    $query->whereRaw('c.nome_razao like ?', ["%$nome_razao%"]);
                    $query->orWhereRaw('c.cpf_cnpj = ?', ["$nome_razao"]);
                    $query->orWhereRaw('o.comprador like ?', ["%$nome_razao%"]);
                });
            }

            if (!empty($tipo_date) && !empty($date1) && !empty($date2)) {
                if (Helper::checkDateUS($date1) && Helper::checkDateUS($date2)){
                    if ($tipo_date == 'CADASTRO') {
                        $orcamentos->whereRaw('date_format(o.created_at, "%Y-%m-%d") >= ?', ["$date1"]);
                        $orcamentos->whereRaw('date_format(o.created_at, "%Y-%m-%d") <= ?', ["$date2"]);
                    }elseif ($tipo_date == 'FATURADO') {
                        $orcamentos->whereRaw('date_format(o.dh_faturado, "%Y-%m-%d") >= ?', ["$date1"]);
                        $orcamentos->whereRaw('date_format(o.dh_faturado, "%Y-%m-%d") <= ?', ["$date2"]);
                    }elseif ($tipo_date == 'ESTORNADO') {
                        $orcamentos->whereRaw('date_format(o.dh_estornado, "%Y-%m-%d") >= ?', ["$date1"]);
                        $orcamentos->whereRaw('date_format(o.dh_estornado, "%Y-%m-%d") <= ?', ["$date2"]);
                    }elseif ($tipo_date == 'CANCELADO') {
                        $orcamentos->whereRaw('date_format(o.dh_cancelado, "%Y-%m-%d") >= ?', ["$date1"]);
                        $orcamentos->whereRaw('date_format(o.dh_cancelado, "%Y-%m-%d") <= ?', ["$date2"]);
                    }
                }
            }

            if ($vendedor_id > 0)
                $orcamentos->whereRaw('o.vendedor_id = ?', ["$vendedor_id"]);
            if (!empty($status))
                $orcamentos->where('o.status', $status);

            $orcamentos = $orcamentos->paginate(60);

            // verifica se tem finalizacao
            foreach($orcamentos as $orcamento){
                $f = OrcamentoFinalizacao::where('orcamento_id', $orcamento->id)->first();
                if (empty($f))
                    $orcamento->has_finalizacao = 'NOK';
                else
                    $orcamento->has_finalizacao = 'OK';
            }

            if ($orcamentos->currentPage() > 1) {
                return view('venda.orcamento.trs')
                    ->with('orcamentos', $orcamentos)
                    >with('nf', $nf);
            }

            return view('venda.orcamento.table')
                ->with('orcamentos', $orcamentos)
                ->with('nf', $nf);

        }catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $pessoa_id = intval(auth('web')->user()->pessoa_id);

        $vendedor = DB::connection('db_client')->table('vendedores','v')
            ->join('pessoas as p', 'v.pessoa_id', '=', 'p.id')
            ->select(['v.pessoa_id','p.nome_razao'])
            ->where('v.empresa_id', $empresa_id)
            ->where('v.pessoa_id', $pessoa_id)
            ->first();    

        return response()->json([
            'status' => 'OK',
            'form' => view('venda.orcamento.create')
                ->with('vendedor', $vendedor)
                ->render()
        ]);
    }

    public function excluir_item()
    {

        return response()->json([
            'status' => 'OK',
            'form' => view('venda.orcamento.excluir_item')
                ->render()
        ]);
    }

    /**
     * @param Orcamento $orcamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_faturar(Orcamento $orcamento)
    {

        $orcamento = Orcamento::find($orcamento->id);

        if (empty($orcamento)){
            return Helper::msg_nok('Orçamento não encontrado');
        }

        if ($orcamento->status == 'FATURADO'){
            return Helper::msg_nok(
               'ORÇAMENTO JÁ FATURADO',
               'Não é possível continuar'
            );
        }

        if ($orcamento->status == 'CANCELADO'){
            return Helper::msg_nok(
                'ORÇAMENTO CANCELADO',
                'Não é possível faturar'
            );
        }

        if (empty($orcamento->finalizacao)){
            return Helper::msg_nok(
                'ORÇAMENTO SEM FINALIZAÇÃO',
                'Edite o orçamento e finalize antes de faturar'
            );
        }
        $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)->orderBy('sequencial','asc');
        if (count($parcelas->get()) <= 0) {
            return Helper::msg_nok(
                'SEM PARCELAS GERADAS',
                'Edite o orçamento paga gerar as parcelas'
            );
        }

        $tot_liquido = doubleval(number_format($orcamento->total_liquido,2,'.',''));
        $tot_parcelas = doubleval(number_format($parcelas->sum('valor'),2, '.',''));

        if ($tot_liquido !== $tot_parcelas) {
            return Helper::msg_nok(
                'TOTAL DE PRODUTOS DIFERENTE DE TOTAL DE PARCELAS',
                'Favor editar o orçamento e finalizar para corrigir'
            );
        }

        $formas = explode('/', $orcamento->finalizacao->forma_pagamento);
        $formas_com_titulo = [];
        foreach ($formas as $forma) {
            $ret = FormaPagamento::where('id', $forma)->first();
            array_push($formas_com_titulo, array($forma => $ret->descricao));
        }

        return response()->json([
            'status' => 'OK',
            'form' => view('venda.orcamento_faturar.index')
                ->with('orcamento', $orcamento)
                ->with('formas', $formas_com_titulo)
                ->with('parcelas', $parcelas->get())
                ->render()
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_faturar(Request $request)
    {

        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $user_id = intval(auth('web')->user()->id);

        $fields = $request->all();
        $orcamento_id = intval($fields['orcamento_id']);
        $tipo_movimento_id = intval($fields['tipo_movimento_id']);

        $tipo_movimento = DB::connection('db_client')
            ->table('tipos_movimentos')
            ->where('id', $tipo_movimento_id)
            ->first();

        if (empty($tipo_movimento))
            return Helper::msg_nok('Tipo de movimento não encontrado');


        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $orcamento_id)
            ->first();

        $valided = $this->valid_faturar($orcamento);

        if (!empty($valided)) {
            return $valided;
        }

        $caixa_abertura_id = CaixaAbertura::caixaAbertoId();

        $mov_estoque = false;
        $mov_financeiro = false;
        if (intval($tipo_movimento->movimenta_estoque) == 1)
            $mov_estoque = true;
        if (intval($tipo_movimento->movimenta_financeiro) == 1)
            $mov_financeiro = true;

        DB::beginTransaction();
        try{

            // GERAR FINANCEIRO
            if ($mov_financeiro){

                $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)
                    ->get();

                if (count($parcelas) <= 0){
                    return Helper::msg_nok('Não foram definidas parcelas para este orçamento');
                }

                // FAZ OS LANCAMENTOS NO RECEITA
                $id_primeiro_lcto = 0;
                $next = 0;
                foreach ($parcelas as $parcela){

                    $lcto = [];
                    $lcto['id_primeiro_lcto'] = 0;
                    $lcto['empresa_id'] = $empresa_id;
                    $lcto['pessoa_id'] = $orcamento->cliente_id;
                    $lcto['tipo'] = 'RECEITA';
                    $lcto['categoria'] = 'NORMAL';
                    $lcto['valor'] = number_format($parcela->valor,2,'.','');
                    $lcto['saldo'] = $lcto['valor'];
                    $lcto['valor_original'] = $lcto['valor'];
                    $lcto['desconto_valor'] = 0;
                    $lcto['desconto_porc'] = 0;
                    $lcto['acrescimo_valor'] = 0;
                    $lcto['acrescimo_porc'] = 0;
                    $lcto['multa'] = 0;
                    $lcto['porc_juros_dia'] = 0;
                    $lcto['vencimento'] = $parcela->vencimento;
                    $lcto['vencimento_original'] = $parcela->vencimento;
                    $lcto['controle_contabil_id'] = 55; //venda a clientes
                    $lcto['parcela_inicio'] = $parcela->sequencial;
                    $lcto['parcela_fim'] = count($parcelas);
                    $lcto['origem'] = 'VENDA';
                    $lcto['forma_pagamento_id'] = $parcela->forma_pagamento_id;
                    $lcto['doc_origem_id'] = $orcamento->id;
                    $lcto['is_origem_externa'] = 1;
                    $lcto['user_idCreated'] = $user_id;
                    $lcto['user_idUpdated'] = $user_id;

                    $receita = Receita::create($lcto);

                    if (!empty($receita)){

                        if ($next == 0) {
                            $id_primeiro_lcto = $receita->id;
                            $next++;
                        }

                        $updated = $receita->update([
                            'id_primeiro_lcto' => $id_primeiro_lcto
                        ]);

                        if (!$updated){
                            DB::rollBack();
                            return Helper::msg_nok('Houve um problema ao tentar atualizar o ID de contra partida');
                            break;
                        }
                    }else{
                        DB::rollBack();
                        return Helper::msg_nok('Financeiro não foi gerado.');
                        break;
                    }

                    // SE TIVER ENTRADA OU FOR AVISTA, LIQUIDAR E LANCAR NO CAIXA
                    if (intval($parcela->avista) == 1){

                        // LIQUIDA A PARCELA
                        $liquidacao = [];
                        $liquidacao['id_primeiro_lcto'] = 0;
                        $liquidacao['id_contrapartida'] = $receita->id;
                        $liquidacao['empresa_id'] = $empresa_id;
                        $liquidacao['pessoa_id'] = $receita->pessoa_id;
                        $liquidacao['tipo'] = 'RECEITA';
                        $liquidacao['categoria'] = 'LIQUIDACAO';
                        $liquidacao['valor'] = $receita->saldo;
                        $liquidacao['saldo'] = 0;
                        $liquidacao['valor_original'] = $receita->saldo;
                        $liquidacao['desconto_valor'] = 0;
                        $liquidacao['desconto_porc'] = 0;
                        $liquidacao['acrescimo_valor'] = 0;
                        $liquidacao['acrescimo_porc'] = 0;
                        $liquidacao['multa'] = 0;
                        $liquidacao['porc_juros_dia'] = 0;
                        $liquidacao['vencimento'] = $receita->vencimento;
                        $liquidacao['vencimento_original'] = $receita->vencimento_original;
                        $liquidacao['centro_custo_id'] = $receita->centro_custo_id;
                        $liquidacao['controle_contabil_id'] = $receita->controle_contabil_id;
                        $liquidacao['parcela_inicio'] = 1;
                        $liquidacao['parcela_fim'] = 1;
                        $liquidacao['origem'] = $receita->origem;
                        $liquidacao['forma_pagamento_id'] = $receita->forma_pagamento_id;
                        $liquidacao['forma_pagamento_idPagRec'] = $receita->forma_pagamento_id;
                        $liquidacao['caixa_abertura_id'] = $caixa_abertura_id;
                        $liquidacao['is_origem_externa'] = $receita->is_origem_externa;
                        $liquidacao['user_idCreated'] = $user_id;
                        $liquidacao['user_idUpdated'] = $user_id;
                        $liquidacao['user_idRecPag'] = $user_id;
                        $liquidacao['dh_RecPag'] = date('Y-m-d H:i:s');

                        $liquidado = Receita::create($liquidacao);
                        if (!empty($liquidado)) {
                            // atualiza o id contra partida
                            $update = $liquidado->update([
                                'id_primeiro_lcto' => $liquidado->id
                            ]);

                            if (!$update) {
                                DB::rollBack();
                                return Helper::msg_nok('Houve um problema ao tentar atualziar o ID de contra partida da liquidação');
                                break;
                            }
                        }else{
                            DB::rollBack();
                            return Helper::msg_nok('Problema ao tentar gerar o lançamento de liquidação');
                            break;
                        }

                        // ATUALIZA O LANCAMENTO ORIGINAL COMO LIQUIDADO
                        $updated = $receita->update([
                            'saldo' => 0,
                            'user_idRecPag' => $user_id,
                            'dh_RecPag' => date('Y-m-d H:i:s')
                        ]);
                        if (!$updated){
                            DB::rollBack();
                            return Helper::msg_nok('Problema ao tentar atualizar o lançamento origem');
                            break;
                        }

                        // LANÇA NO CAIXA
                        $caixa_movimento['sequencial'] = 1;
                        $caixa_movimento['empresa_id'] = $empresa_id;
                        $caixa_movimento['caixa_abertura_id'] = $caixa_abertura_id;
                        $caixa_movimento['user_id_lancamento'] = $user_id;
                        $caixa_movimento['dh_lancamento'] = date('Y-m-d H:i:s');
                        $caixa_movimento['forma_pagamento_id'] = $parcela->forma_pagamento_id;
                        $caixa_movimento['tipo'] = 'CREDITO';
                        $caixa_movimento['valor'] = $parcela->valor;
                        $caixa_movimento['historico'] = "RECEBIMENTO ENTRADA OU A VISTA ORCAMENTO ID: " . $parcela->orcamento_id .
                            ' | cliente: ' . $receita->pessoa->id . ' - ' . $receita->pessoa->nome_razao;
                        $caixa_movimento['is_origem_externo'] = 1;

                        $movimento_created = CaixaMovimento::create($caixa_movimento);
                        if (empty($movimento_created)){
                            DB::rollBack();
                            return Helper::msg_nok('Problema ao tentar lançar no caixa');
                            break;
                        }
                    }
                }
            }

            // BAIXA ESTOQUE
            if ($mov_estoque){
                $itens = OrcamentoItens::where('orcamento_id', $orcamento->id)->get();
                if (count($itens) > 0) {
                    foreach ($itens as $item){
                        $produtoEstoque = ProdutoEstoque::where('empresa_id', $empresa_id)
                            ->where('produto_id', $item->produto_id)
                            ->first();
                        if (!empty($produtoEstoque)){
                            $produtoEstoque->estoque = doubleval($produtoEstoque->estoque) - doubleval($item->quantidade);
                            $produtoEstoque->save();
                        }
                    }
                }
            }

            $updated = $orcamento->update([
                'status' => 'FATURADO',
                'tipo_movimento_id' => $tipo_movimento->id,
                'user_idFaturado' => $user_id,
                'dh_faturado' => date('Y-m-d H:i:s')
            ]);

            if (!$updated) {
                DB::rollBack();
                return Helper::msg_nok(
                    'NÃO FOI POSSÍVEL FATURAR',
                    'Houve algum problem ao tentar atualizar os valores do orçamento'
                );
            }

            DB::commit();
            return Helper::msg_ok('Orçamento faturado com sucesso.');


        }catch (\Exception $e) {
            DB::rollBack();
            return Helper::msg_exception($e->getMessage());
        }

    }

    /**
     * @param Orcamento $orcamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_cancelar(Orcamento $orcamento)
    {

        $orcamento = Orcamento::find($orcamento->id);

        if (empty($orcamento)){
            return Helper::msg_nok('Orçamento não encontrado');
        }

        if ($orcamento->status == 'FATURADO') {
            return Helper::msg_nok(
                'ORÇAMENTO FATURADO',
                'Para cancelar, é necesário estornar primeiro'
            );
        }

        if ($orcamento->status == 'CANCELADO'){
            return Helper::msg_nok('Orçamento já esta cancelado');
        }

        return response()->json([
           'status' => 'OK',
           'view' => view('venda.orcamento_cancelar.index')
                ->with('orcamento', $orcamento)
                ->render()
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_cancelar(Request $request)
    {

        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $user_id = intval(auth('web')->user()->id);

        $fields = $request->all();
        $orcamento_id = intval($fields['orcamento_id']);

        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $orcamento_id)
            ->first();

        if (empty($orcamento)) {
            return Helper::msg_nok('Orçamento não encontrado');
        }

        if ($orcamento->status == 'FATURADO') {
            return Helper::msg_nok(
               'ORÇAMENTO FATURADO',
               'Para cancelar, é necesário estornar primeiro'
            );
        }

        if ($orcamento->status == 'CANCELADO'){
            return Helper::msg_nok('Orçamento já esta cancelado');
        }

        $updated = $orcamento->update([
            'user_idCancelado' => $user_id,
            'dh_cancelado' => date('Y-m-d H:i:s'),
            'status' => 'CANCELADO'
        ]);

        if ($updated) {
            return Helper::msg_ok('Orçamento cancelado com sucesso.');
        }else{
            return Helper::msg_nok('Houve algum problema ao tentar cancelar este orçamento');
        }

    }

    /**
     * @param Orcamento $orcamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_estornar(Orcamento $orcamento)
    {

        $orcamento = Orcamento::find($orcamento->id);

        if (empty($orcamento)){
            return Helper::msg_nok('Orçamento não encontrado');
        }
        if ($orcamento->status == 'ABERTO') {
            return Helper::msg_nok('Orçamento em Aberto, não existe movimento para estornar');
        }
        if ($orcamento->status == 'CANCELADO') {
            return Helper::msg_nok('Orçamento Cancelado, não é possível fazer nenhum tipo de processo');
        }
        if ($orcamento->status == 'ESTORNADO') {
            return Helper::msg_nok('Orçamento já está Estornado');
        }

        $nf = NFeNFCe::where('empresa_id', $orcamento->empresa_id)
            ->where('orcamento_id', $orcamento->id)
            ->where('is_transmitido', 1)
            ->where('is_cancelado', 0)
            ->first();

        if (!empty($nf)) {
            return Helper::msg_nok('Venda com nota fiscal gerada, cancele a nota fiscal para poder estornar a venda.');
        }

        return response()->json([
            'status' => 'OK',
            'view' => view('venda.orcamento_estornar.index')
                ->with('orcamento', $orcamento)
                ->render()
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_estornar(Request $request)
    {
        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $user_id = intval(auth('web')->user()->id);

        $fields = $request->all();
        $orcamento_id = intval($fields['orcamento_id']);

        // ORCAMENTO
        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $orcamento_id)
            ->first();

        if (empty($orcamento)){
            return Helper::msg_nok('Orçamento não encontrado');
        }

        // PARCELAS
        $orcamento_parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)
            ->get();

        if (count($orcamento_parcelas) <= 0){
            return Helper::msg_nok(
                'NÃO EXISTE PARCELAS DEFINIDAS PARA ESTE ORÇAMENTO',
                'Não é possível continuar'
            );
        }

        // ITENS
        $orcamento_itens = OrcamentoItens::where('orcamento_id', $orcamento->id)
            ->get();

        if (count($orcamento_itens) <= 0){
            return Helper::msg_nok(
                'NÃO EXISTE ITENS PARA ESTE ORÇAMENTO',
                'Não é possível continuar'
            );
        }

        if ($orcamento->status == 'ABERTO') {
            return Helper::msg_nok('Orçamento em Aberto, não existe movimento para estornar');
        }
        if ($orcamento->status == 'CANCELADO') {
            return Helper::msg_nok('Orçamento Cancelado, não é possível fazer nenhum tipo de processo');
        }
        if ($orcamento->status == 'ESTORNADO') {
            return Helper::msg_nok('Orçamento já está Estornado');
        }

        $tipo_movimento = TipoMovimento::find($orcamento->tipo_movimento_id);
        if (empty($tipo_movimento)){
            return Helper::msg_nok('Tipo de movimento não definido');
        }

        $mov_financeiro = $tipo_movimento->movimenta_financeiro;
        $mov_estoque = $tipo_movimento->movimenta_estoque;

        DB::beginTransaction();
        try {

            // DESFAZ O FINANCEIRO
            if ($mov_financeiro == 1) {

                // PEGA O LANCAMENTO 1
                $lancamento_principal = Receita::where('empresa_id', $empresa_id)
                                            ->where('tipo', 'RECEITA')
                                            ->where('is_origem_externa', 1)
                                            ->where('origem','VENDA')
                                            ->where('parcela_inicio', 1)
                                            ->where('categoria','!=','LIQUIDACAO')
                                            ->where('doc_origem_id', $orcamento->id)
                                            ->first();

                if (empty($lancamento_principal)){
                    return Helper::msg_nok('Não foi encontrado o lançamento principal no financeiro');
                }

                // PEGA O VALOR PARA VOLTAR AO CAIXA
                $rows = Receita::where('empresa_id', $empresa_id)
                    ->select('id')
                    ->where('id_primeiro_lcto', $lancamento_principal->id)
                    ->get();
                $ids = [];
                foreach ($rows as $row){
                    array_push($ids, $row->id);
                }

                $valor = Receita::where('empresa_id', $empresa_id)
                    ->where('tipo', 'RECEITA')
                    ->where('origem','VENDA')
                    ->where('categoria', 'LIQUIDACAO')
                    ->whereIn('id_contrapartida', $ids)
                    ->sum('valor');

                $liquidacoes = Receita::where('empresa_id', $empresa_id)
                    ->select([
                        'id',
                        'caixa_abertura_id',
                        'forma_pagamento_idPagRec',
                        'valor'
                    ])
                    ->where('tipo', 'RECEITA')
                    ->where('origem','VENDA')
                    ->where('categoria', 'LIQUIDACAO')
                    ->whereIn('id_contrapartida', $ids);

                if (doubleval($valor) > 0) {

                    $caixa_abertura_id = CaixaAbertura::caixaAbertoId();
                    $parcelas_vista = false;
                    $caixa_diff = false;
                    foreach($orcamento_parcelas as $parcela){
                        if ($parcela->avista == 1){
                            $parcelas_vista = true;
                            break;
                        }
                    }

                    if ($parcelas_vista){
                        foreach ($liquidacoes->get() as $liquidacao){
                            if ($liquidacao->caixa_abertura_id != $caixa_abertura_id){
                                $caixa_diff = true;
                                break;
                            }
                        }
                    }

                    if (!CaixaAbertura::isCaixaAberto()){
                        if ($parcelas_vista)
                            return Helper::msg_nok('Existe valor para estornar no caixa, mas o caixa esta fechado');

                    }

                    // FAZ O LANCAMENTO NO CAIXA
                    foreach ($liquidacoes->get() as $liquidacao) {

                        $caixa_movimento['sequencial'] = 1;
                        $caixa_movimento['empresa_id'] = $empresa_id;
                        $caixa_movimento['caixa_abertura_id'] = $caixa_abertura_id;
                        $caixa_movimento['user_id_lancamento'] = $user_id;
                        $caixa_movimento['dh_lancamento'] = date('Y-m-d H:i:s');
                        $caixa_movimento['forma_pagamento_id'] = $liquidacao->forma_pagamento_idPagRec;
                        $caixa_movimento['tipo'] = 'DEBITO';
                        $caixa_movimento['valor'] = $liquidacao->valor;
                        $caixa_movimento['historico'] = "ESTORNO orcamento id: " . $orcamento->id .
                            ' | cliente: ' . $orcamento->cliente->id . ' - ' . $orcamento->cliente->nome_razao;
                        $caixa_movimento['is_origem_externo'] = 1;

                        $created = CaixaMovimento::create($caixa_movimento);
                        if (empty($created)){
                            DB::rollBack();
                            break;
                        }
                    }
                }

                // EXCLUIR AS LIQUIDACOES
                $liquidacoes->delete();

                // EXCLUIR OS LANCAMENTOS
                Receita::where('empresa_id', $empresa_id)
                    ->where('tipo', 'RECEITA')
                    ->where('is_origem_externa', 1)
                    ->where('origem','VENDA')
                    ->where('doc_origem_id', $orcamento->id)
                    ->delete();
            }

            // MARCA O ORCAMENTO COMO ESTORNADO
            $updated = $orcamento->update([
                'status' => 'ESTORNADO',
                'user_idEstornado' => $user_id,
                'dh_estornado' => date('Y-m-d H:i:s')
            ]);
            if (!$updated) {
                DB::rollBack();
                Helper::msg_nok('Houve algum problema ao concluir o estorno');
            }

            // VOLTA O ESTOQUE
            if ($mov_estoque == 1) {
                foreach ($orcamento_itens as $item){
                    $produtoEstoque = ProdutoEstoque::where('empresa_id', $empresa_id)
                        ->where('produto_id', $item->produto_id)
                        ->first();
                    if (!empty($produtoEstoque)){
                        $produtoEstoque->estoque = doubleval($produtoEstoque->estoque) +
                            doubleval($item->quantidade);
                        $updated = $produtoEstoque->save();
                        if (!$updated){
                            DB::rollBack();
                            break;
                        }
                    }else{
                        DB::rollBack();
                        break;
                    }
                }
            }

            DB::commit();
            return Helper::msg_ok("Orçamento estornado com sucesso.");

        }catch (\Exception $e) {
            DB::rollBack();
            return Helper::msg_exception($e->getMessage());
        }

    }

    /**
     * @param $orcamento
     * @return \Illuminate\Http\JsonResponse|null
     */
    private function valid_faturar($orcamento)
    {

        // ORCAMENTO NAO ENCONTRADO
        if (empty($orcamento)) {
            return Helper::msg_nok('Orçamento não encontrado');
        }

        // CHECAR SE EXISTE CAIXA ABERTO
        if (!CaixaAbertura::isCaixaAberto()) {

            $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)
                ->get();
            if (count($parcelas) > 0){
                $exists_avista = false;
                foreach($parcelas as $parcela) {
                    if (intval($parcela->avista) == 1){
                        $exists_avista = true;
                        break;
                    }
                }
                if ($exists_avista){
                    return Helper::msg_nok(
                        'CAIXA FECHADO',
                        'Não encontramos nenhum caixa aberto para este usuário.'
                    );
                }
            }

        }
        return null;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fields = $request->all();

        $validator = Validator::make($fields, [
            'cliente_id' => 'required',
            'comprador' => 'nullable|max:60',
            'vendedor_id' => 'required'
        ]);

        if ($validator->fails()) {
            return Helper::msg_validation($validator->errors());
        }

        if (empty($fields['orcamento_itens'])) {
            return Helper::msg_nok2('NENHUM ITEM FOI ADICIONADO','Selecione os produtos e adicione no carrinho para continuar');
        }

        if ($fields['preco_item'] == 'PRAZO'){

        $total_bruto = 0;
        foreach ($fields['orcamento_itens'] as $k => $v) {
            $fields['orcamento_itens'][$k]['quantidade'] = str_replace(',', '.', $v['quantidade']);
            $total_bruto += $fields['orcamento_itens'][$k]['quantidade'] * $v['preco_prazo'];
            $modo_pag = 'PRAZO';
        }
    }

        if ($fields['preco_item'] == 'VISTA'){

        $total_bruto = 0;
        foreach ($fields['orcamento_itens'] as $k => $v) {
            $fields['orcamento_itens'][$k]['quantidade'] = str_replace(',', '.', $v['quantidade']);
            $total_bruto += $fields['orcamento_itens'][$k]['quantidade'] * $v['preco_vista'];
            $modo_pag = 'VISTA';
        }
    }

        $empresa_id = intval($request->session()->get('empresa')['id']);

        $fields['empresa_id'] = $empresa_id;
        $fields['status'] = 'ABERTO';
        $fields['total_bruto'] = number_format($total_bruto, 2,'.', '');
        $fields['desconto_valor'] = 0;
        $fields['desconto_porc'] = 0;
        $fields['total_liquido'] = number_format($total_bruto, 2,'.', '');
        $fields['valor_recebido'] = 0;
        $fields['valor_troco'] = 0;
        $fields['observacao'] = $modo_pag;

        try {

            DB::beginTransaction();

            if (!empty($fields['orcamento_id'])) {
                // SE JA EXISTE, SO ATUALIZA
                $orcamento = Orcamento::find($fields['orcamento_id']);
                if (!empty($orcamento)) {
                    $orcamento->cliente_id = $fields['cliente_id'];
                    $orcamento->comprador = $fields['comprador'];
                    $orcamento->vendedor_id = $fields['vendedor_id'];
                    $orcamento->total_bruto = $fields['total_bruto'];
                    $orcamento->total_liquido = $orcamento->total_bruto - $orcamento->desconto_valor;
                    $orcamento->modo_pdv = intval($fields['modo_pdv']);
                    $orcamento->update();
                }
            } else {
                // SE NAO EXISTIR, CRIAR UM NOVO
                $orcamento = Orcamento::create($fields);
            }

            if (!empty($orcamento)) {

                // LIMPA A TABELA DE ITENS
                OrcamentoItens::where('orcamento_id', $orcamento->id)->delete();

                // CADASTRAR OS ITENS
                $seq = 1;
                try{

                    if ($fields['preco_item'] == 'PRAZO'){

                    foreach ($fields['orcamento_itens'] as $item) {
                        OrcamentoItens::create([
                            'orcamento_id' => $orcamento->id,
                            'produto_id' => $item['produto_id'],
                            'sequencial' => $seq,
                            'quantidade' => $item['quantidade'],
                            'preco' => number_format($item['preco_prazo'], 2,'.',''),
                            'desconto' => 0,
                            'subtotal' => number_format($item['quantidade'] * $item['preco_prazo'], 2, '.', '')
                        ]);
                        $seq++;
                    }
                }

                if ($fields['preco_item'] == 'VISTA'){

                    foreach ($fields['orcamento_itens'] as $item) {
                        OrcamentoItens::create([
                            'orcamento_id' => $orcamento->id,
                            'produto_id' => $item['produto_id'],
                            'sequencial' => $seq,
                            'quantidade' => $item['quantidade'],
                            'preco' => number_format($item['preco_vista'], 2,'.',''),
                            'desconto' => 0,
                            'subtotal' => number_format($item['quantidade'] * $item['preco_vista'], 2, '.', '')
                        ]);
                        $seq++;
                    }
                }

                }catch (\Exception $e) {
                    DB::rollBack();
                    return Helper::msg_exception($e->getMessage());
                }

                DB::commit();
                return response()->json([
                    'status' => 'OK',
                    'insert_id' => $orcamento->id,
                    'message' => 'Orçamento Cadastrado'
                ]);

            } else {
                return Helper::msg_nok('Orçamento não Cadastrado/Alterado');
            }

        } catch (Exception $e) {
            DB::rollBack();
            return Helper::msg_exception($e->getMessage());
        }

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Venda\Orcamento $orcamento
     * @return \Illuminate\Http\Response
     */
    public function show(Orcamento $orcamento)
    {
        //
    }

    /**
     * @param Orcamento $orcamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_finalizacao(Orcamento  $orcamento)
    {
        $orcamento = Orcamento::find($orcamento->id);

        if (empty($orcamento)){
            return Helper::msg_nok('Orçamento não encontrado');
        }

        if (empty($orcamento->finalizacao)){
            return Helper::msg_nok('ORÇAMENTO SEM FINALIZAÇÃO','Edite o orçamento e finalize');
        }

        $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)->orderBy('sequencial','asc');
        if (count($parcelas->get()) <= 0) {
            return Helper::msg_nok('SEM PARCELAS GERADAS','Edite o orçamento para gerar as parcelas');
        }

        $formas = explode('/', $orcamento->finalizacao->forma_pagamento);
        $formas_com_title = [];
        foreach ($formas as $forma) {
            $ret = FormaPagamento::where('id', $forma)->first();
            array_push($formas_com_title,array($forma => $ret->descricao));
        }

        return response()->json([
            'status' => 'OK',
            'form' => view('venda.orcamento_finalizar.show')
                ->with('orcamento', $orcamento)
                ->with('formas', $formas_com_title)
                ->with('parcelas', $parcelas->get())
                ->render()
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Venda\Orcamento $orcamento
     * @return \Illuminate\Http\Response
     */
    public function edit(Orcamento $orcamento)

    {
        try {

            $orcamento = Orcamento::find($orcamento->id);

            if (!empty($orcamento)) {

                $itens = DB::connection('db_client')
                    ->table('orcamentos_itens as i')
                    ->join('produtos as p', 'i.produto_id', '=', 'p.id')
                    ->join('produtos_precos as e', 'i.produto_id', '=', 'e.produto_id')
                    ->select([
                        'p.id as produto_id',
                        'p.descricao as produto_text',
                        'e.preco_vista as preco_vista',
                        'e.preco_prazo as preco_prazo',
                        'i.quantidade'
                    ])
                    ->where('i.orcamento_id', $orcamento->id)
                    ->where('e.empresa_id', $orcamento->empresa_id)
                    ->get();    

                return response()->json([
                    'status' => 'OK',
                    'itens' => $itens,
                    'form' => view('venda.orcamento.edit')
                        ->with('orcamento', $orcamento)
                        ->render()
                ]);

            } else {
                return Helper::msg_nok('Orçamento não encontrado');
            }

        } catch (\Exception $e) {
            return Helper::msg_exception($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Venda\Orcamento $orcamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Orcamento $orcamento)
    {

        $orcamento = Orcamento::find($orcamento->id);

        if (empty($orcamento)){
            return Helper::msg_nok2('Orçamentos não encontrado');
        }
        if ($orcamento->status == 'FATURADO'){
            return Helper::msg_nok2('ORÇAMENTO JÁ FATURADO','Não é possível alterar');
        }
        if ($orcamento->status == 'CANCELADO'){
            return Helper::msg_nok2('ORÇAMENTO CANCELADO','Não é possível alterar');
        }

        $fields = $request->all();

        $validator = Validator::make($fields, [
            'cliente_id' => 'required',
            'comprador' => 'nullable|max:60',
            'vendedor_id' => 'required'
        ]);

        if ($validator->fails()) {
            return Helper::msg_validation($validator->errors());
        }

        if (empty($fields['orcamento_itens'])) {
            return Helper::msg_nok2('NENHUM ITEM FOI ADICIONADO','Não é possível continuar');
        }

        if ($fields['preco_item'] == 'PRAZO'){

        $total_bruto = 0;
        foreach ($fields['orcamento_itens'] as $k => $v) {
            $fields['orcamento_itens'][$k]['quantidade'] = str_replace(',', '.', $v['quantidade']);
            $total_bruto += $fields['orcamento_itens'][$k]['quantidade'] * $v['preco_prazo'];
            $modo_pag = 'PRAZO';
        }

    }

    if ($fields['preco_item'] == 'VISTA'){

        $total_bruto = 0;
        foreach ($fields['orcamento_itens'] as $k => $v) {
            $fields['orcamento_itens'][$k]['quantidade'] = str_replace(',', '.', $v['quantidade']);
            $total_bruto += $fields['orcamento_itens'][$k]['quantidade'] * $v['preco_vista'];
            $modo_pag = 'VISTA';
        }

    }

        $fields['total_bruto'] = number_format($total_bruto, 2,'.', '');
        $fields['total_liquido'] = number_format($total_bruto, 2,'.', '');

        try {

            DB::beginTransaction();

            if (!empty($orcamento)) {
                $orcamento->cliente_id = $fields['cliente_id'];
                $orcamento->comprador = $fields['comprador'];
                $orcamento->vendedor_id = $fields['vendedor_id'];
                $orcamento->total_bruto = $fields['total_bruto'];
                $orcamento->total_liquido = $fields['total_liquido'] - $orcamento->desconto_valor;
                $orcamento->observacao = $modo_pag;
                $orcamento->modo_pdv = intval($fields['modo_pdv']);
                $orcamento->update();
            }

            if (!empty($orcamento)) {

                // LIMPA A TABELA DE ITENS
                OrcamentoItens::where('orcamento_id', $orcamento->id)->delete();

                // CADASTRAR OS ITENS
                $seq = 1;
                if ($fields['preco_item'] == 'PRAZO'){

                foreach ($fields['orcamento_itens'] as $item) {
                    OrcamentoItens::create([
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $item['produto_id'],
                        'sequencial' => $seq,
                        'quantidade' => $item['quantidade'],
                        'preco' => number_format($item['preco_prazo'], 2, '.', ''),
                        'desconto' => 0,
                        'subtotal' => number_format($item['quantidade'] * $item['preco_prazo'], 2, '.', '')
                    ]);
                    $seq++;
                }
            }

            if ($fields['preco_item'] == 'VISTA'){

                foreach ($fields['orcamento_itens'] as $item) {
                    OrcamentoItens::create([
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $item['produto_id'],
                        'sequencial' => $seq,
                        'quantidade' => $item['quantidade'],
                        'preco' => number_format($item['preco_vista'], 2, '.', ''),
                        'desconto' => 0,
                        'subtotal' => number_format($item['quantidade'] * $item['preco_vista'], 2, '.', '')
                    ]);
                    $seq++;
                }
            }

                DB::commit();

                return response()->json([
                    'status' => 'OK',
                    'insert_id' => $orcamento->id,
                    'message' => 'Orçamento Atualizado.'
                ]);

            } else {
                return Helper::msg_nok('Orçamento não alterado');
            }

        } catch (Exception $e) {
            DB::rollBack();
            return Helper::msg_exception($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Venda\Orcamento $orcamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Orcamento $orcamento)
    {
        //
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function total_vendas_hoje(){
        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);

        $total = DB::connection('db_client')
            ->table('orcamentos', 'o')
            ->where('o.empresa_id', $empresa_id)
            ->where('o.status', 'FATURADO')
            ->where('o.tipo_movimento_id', 1)
            ->whereRaw('DATE_FORMAT(o.dh_faturado, "%Y-%m-%d") = ?', [date('Y-m-d')])
            ->sum('o.total_liquido');

        return response()->json([
            'status' => 'OK',
            'message' => doubleval($total)
        ]);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function vendas_mes_grafico(){
        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);

        $totais = [];
        $meses = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        for ($i=0; $i<12; $i++){

            $total = DB::connection('db_client')
                ->table('orcamentos', 'o')
                ->where('o.empresa_id', $empresa_id)
                ->where('o.status', 'FATURADO')
                ->where('o.tipo_movimento_id', 1)
                ->whereRaw('DATE_FORMAT(o.dh_faturado, "%Y") = ?', [date('Y')])
                ->whereRaw('DATE_FORMAT(o.dh_faturado, "%m") = ?', [$meses[$i]])
                ->sum('o.total_liquido');

            array_push($totais,$total);

        }

        return response()->json([
            'status' => 'OK',
            'meses' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho','julho','agosto','setembro','outubro','novembro','dezembro'],
            'totais' => $totais
        ]);

    }

    public function orcamentos_criados_graf(){
        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);

        $totais = [];
        $meses = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        for ($i=0; $i<12; $i++){

            $total = DB::connection('db_client')
                ->table('orcamentos', 'o')
                ->where('o.empresa_id', $empresa_id)
                ->where('o.status', 'FATURADO')
                ->where('o.tipo_movimento_id', 1)
                ->whereRaw('DATE_FORMAT(o.dh_faturado, "%Y") = ?', [date('Y')])
                ->whereRaw('DATE_FORMAT(o.dh_faturado, "%m") = ?', [$meses[$i]])
                ->count();

            array_push($totais,$total);

        }

        return response()->json([
            'status' => 'OK',
            'meses' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho','julho','agosto','setembro','outubro','novembro','dezembro'],
            'totais' => $totais
        ]);

    }

    public function email_create(Request $request){

        try {

            $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);

            $param = ParametroFinanceiro::where('empresa_id', $empresa_id)->first();

            if(empty($param)){
                $param_email = '';
            }else{
                if($param->resp_finan == 1){
                $param_email = $param->email_contador;
                }else{
                $param_email = $param->email_empresa; 
                }
            }

            $orcamento = Orcamento::find($request->id_orc_qual);

            $nf = NFeNFCe::where('orcamento_id', $request->id_orc_qual)->first();

            if (!empty($orcamento)) {   

                return response()->json([
                    'status' => 'OK',
                    'data' => view('venda.orcamento.email_create')
                        ->with('orcamento', $orcamento)
                        ->with('nf', $nf)
                        ->with('param_email', $param_email)
                        ->render()
                ]);

            } else {
                return Helper::msg_nok('Erro ao carregar a central de envio de E-mail!');
            }

        } catch (\Exception $e) {
            return Helper::msg_exception($e->getMessage());
        }

    }

    public function email_store(Request $request){

        try {

            $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);

            $fields = $request->all();

            $orcamento = Orcamento::find($request->id_orc);

            $nf = NFeNFCe::where('orcamento_id', $request->id_orc)->first();

        if($fields['tabActive'] == 'tabEmailClientes'){

            //Verifica se o o user deixou os campos em branco
            if(empty($fields['email_cliente'])){

                return response()->json([
                    'status' => 'NOK',
                    'data' => 'O campo "e-mail" ficou em branco, por favor preencha para continuar.'
                ]);

            }else if(empty($fields['assunto_email'])){
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Você deixou o campo "assunto" em branco, preencha para continuar.'
                ]);

            }else if (empty($fields['story'])){
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Você não colocou nenhuma "mensagem" para o cliente, escreva ou deixe a padrão.'
                ]);

            }

            //Dados complementares para função
            $email_empresa = auth('web')->user()->empresa->email;
            $cpf_cnpj = session()->get('empresa')['cpf_cnpj'];

            //Transforma o arquivo em PDF para enviar ao cliente

                if (!empty($nf)) {

                    $params = [
                        'chave' => trim($nf->chave)
                    ];

                    $curl = ApiFiscal::getCurl(
                        '/fiscal/danfe',
                        'POST',
                        $params
                    );

                    if (!empty($curl)){

                        $response = curl_exec($curl);

                        curl_close($curl);

                    }else{
                        return response()->json([
                            'status' => 'NOK',
                            'data' => 'Não foi possível gerar sua nota para envio, por favor entre em contato com o suporte.'
                        ]);
                    }

                } else {
                    return response()->json([
                        'status' => 'NOK',
                        'data' => 'Nota não encontrada para envio, por favor verifique os dados ou entre em contato com suporte.'
                    ]);
                }
            //------------------------------------------------------------------------------------------------------------------------

            //Local aonde ficou salvo o PDF para envio    
            $arquivo_pdf = $_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/Pdf/$cpf_cnpj/$nf->chave-nfe.pdf";
            //-----------------------------------------------------------------------------------------    

            //Se a copia para o email do usuario estiver ativo manda uma copia do e-mail
            if(intval($fields['copia_email']) == 0){

            $enviar = Mail::send('mail.envio', ['mens' => $fields['story']], function($m)use($request, $arquivo_pdf) {
                $m->from('naoresponder@empirescloud.com.br', 'Empires Cloud');
                $m->to(trim($request->email_cliente));
                $m->subject($request->assunto_email);
                $m->attach($arquivo_pdf);
            });

            }else{
                $enviar = Mail::send('mail.envio', ['mens' => $fields['story']], function($m)use($request, $arquivo_pdf, $email_empresa) {
                    $m->from('naoresponder@empirescloud.com.br', 'Empires Cloud');
                    $m->to(trim($request->email_cliente));
                    $m->to($email_empresa);
                    $m->subject($request->assunto_email);
                    $m->attach($arquivo_pdf);
                });

             }

             $nf->update([
                'is_email_enviado' => 1
               ]);


            if (!$enviar) {   

                return response()->json([
                    'status' => 'OK',
                    'data' => 'E-mail enviado com sucesso.'
                ]);

            } else {
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Não foi possível enviar seu e-mail.'
                ]);
            }

        }else if($fields['tabActive'] == 'tabEmailContador'){

            //Verifica se o o user deixou os campos em branco
            if(empty($fields['email_contador'])){

                return response()->json([
                    'status' => 'NOK',
                    'data' => 'O campo "e-mail" ficou em branco, por favor preencha para continuar.'
                ]);

            }else if(empty($fields['assunto_contador'])){
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Você deixou o campo "assunto" em branco, preencha para continuar.'
                ]);

            }else if (empty($fields['story_contador'])){
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Você não colocou nenhuma "mensagem" para o cliente, escreva ou deixe a padrão.'
                ]);

            }

            //Obtendo o ano e o mês que foi lançado para buscar no sistema

            //ANO
            for($i = 0; $i < 1; $i++){
                $a1 = ($nf->ide_dhEmi[$i]);
            }
            for($i = 0; $i < 2; $i++){
                $a2 = ($nf->ide_dhEmi[$i]);
            }
            for($i = 0; $i < 3; $i++){
                $a3 = ($nf->ide_dhEmi[$i]);
            }
            for($i = 0; $i < 4; $i++){
                $a4 = ($nf->ide_dhEmi[$i]);
            }

            //MÊS
            for($i = 0; $i < 6; $i++){
                $a5 = ($nf->ide_dhEmi[$i]);
                }
                for($i = 0; $i < 7; $i++){
                $a6 = ($nf->ide_dhEmi[$i]);
            }

            //----------------------------------------------------------------
            //Dados complementares para função
            $cpf_cnpj = session()->get('empresa')['cpf_cnpj'];

            //Local aonde ficou salvo o PDF para envio    
            $arquivo_xml = $_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/NotasFiscais/$cpf_cnpj/$a1$a2$a3$a4$a5$a6/$nf->chave-nfe.xml";
            //-----------------------------------------------------------------------------------------    

            if($request->modo_envio == 0){

            $enviar_xml = Mail::send('mail.envio', ['mens' => $fields['story_contador']], function($m)use($request, $arquivo_xml) {
                $m->from('naoresponder@empirescloud.com.br', 'Empires Cloud');
                $m->to(trim($request->email_contador));
                $m->subject($request->assunto_contador);
                $m->attach($arquivo_xml);
            });

            if (!$enviar_xml) {   

                return response()->json([
                    'status' => 'OK',
                    'data' => 'E-mail enviado com sucesso.'
                ]);

            } else {
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Não foi possível enviar seu e-mail.'
                ]);
            }

            }else if($request->modo_envio == 1){

                $eventos = $request->eventos_xml_contador;
                // Criar instancia de ZipArchive
                $zip = new ZipArchive;
                $zip_ev = new ZipArchive;
                $zip_final = new ZipArchive;
                $zip_temp = new ZipArchive;

                $fileName_temp = '-XML.zip';
                $fileName = '-XML.zip'; // nome do zip
                $fileName_ev = '-XML_EV.zip'; // nome do zip - EVENTOS
                $fileName_final = '-XML_&_EVENTOS.zip'; //nome do zip com os arquivos de eventos e XML do mÊs

                //Teste para verificar se tem alguma pasta de eventos do cliente
                $teste_cliente = $_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/Eventos/$cpf_cnpj/$request->ano_xml_contador$request->meses_xml_contador/";
                $teste_cliente2 = $_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/NotasFiscais/$cpf_cnpj/$request->ano_xml_contador$request->meses_xml_contador/";

                $zipPath = $_SERVER['DOCUMENT_ROOT']. "/../../ApiFiscal/Release/XmlFiscal/XML_Gerados/".$cpf_cnpj.'-'.$request->meses_xml_contador.'.'.$request->ano_xml_contador.($fileName); // path do zip onde fica salvo para o download
                $zipPath_temp = $_SERVER['DOCUMENT_ROOT']. "/../../ApiFiscal/Release/XmlFiscal/Temp_download/".$cpf_cnpj.'-'.$request->meses_xml_contador.'.'.$request->ano_xml_contador.($fileName_temp);
                $zipPath_ev = $_SERVER['DOCUMENT_ROOT']. "/../../ApiFiscal/Release/XmlFiscal/Temp_download/".$cpf_cnpj.'-'.$request->meses_xml_contador.'.'.$request->ano_xml_contador.($fileName_ev); // path do zip onde fica salvo para o download
                $zipPath_final = $_SERVER['DOCUMENT_ROOT']. "/../../ApiFiscal/Release/XmlFiscal/Download_ev_xml/".$cpf_cnpj.'-'.$request->meses_xml_contador.'.'.$request->ano_xml_contador.($fileName_final); // path do zip onde fica salvo o ultimo arquivo para ser baixado

                if($eventos == 'SEM'){
    
                    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE)
                    {
            
                        if (file_exists($teste_cliente2)){
            
                      // arquivos que serao adicionados ao zip
                      $files = File::files($_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/NotasFiscais/$cpf_cnpj/$request->ano_xml_contador$request->meses_xml_contador/");
                
                            foreach ($files as $key => $value) {
                                // nome/diretorio do arquivo dentro do zip
                                $relativeNameInZipFile = basename($value);
                        
                                // adicionar arquivo ao zip
                                $zip->addFile($value, $relativeNameInZipFile);
                                
                              }
                            
                              // concluir a operacao
                              $zip->close();

                              $enviar_xml = Mail::send('mail.envio', ['mens' => $fields['story_contador']], function($m)use($request, $arquivo_xml, $zipPath) {
                                $m->from('naoresponder@empirescloud.com.br', 'Empires Cloud');
                                $m->to(trim($request->email_contador));
                                $m->subject($request->assunto_contador);
                                $m->attach($zipPath);
                            });

                            if (!$enviar_xml) {   

                                return response()->json([
                                    'status' => 'OK',
                                    'data' => 'E-mail enviado com sucesso.'
                                ]);
                
                            } else {
                                return response()->json([
                                    'status' => 'NOK',
                                    'data' => 'Não foi possível enviar seu e-mail.'
                                ]);
                            }
            
                    }else{
            
                        return response()->json([
                            'status' => 'NOK',
                            'data' => 'Não foram encontrados XML para seu período, retorne e tente enviar de outro mês.'
                        ]);
                    }   
                    }

                }else if($eventos == 'COM'){
                

                if ($zip_ev->open($zipPath_ev, ZipArchive::CREATE) === TRUE)
                {
                        if (file_exists($teste_cliente)){

                        // arquivos que serao adicionados ao zip
                        $files_ev = File::files($_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/Eventos/$cpf_cnpj/$request->ano_xml_contador$request->meses_xml_contador/");

                        foreach ($files_ev as $key_ev => $value_ev) {
                        // nome/diretorio do arquivo dentro do zip
                        $relativeNameInZipFile_ev = basename($value_ev);

                        // adicionar arquivo ao zip
                        $zip_ev->addFile($value_ev, $relativeNameInZipFile_ev);

                    }

                        // concluir a operacao
                        $zip_ev->close();

                    }else{

                        return response()->json([
                            'status' => 'NOK',
                            'data' => 'Retorne a pesquisa e mande seus XML -> SEM EVENTOS NFe <-'
                        ]);
                        

                    }
                }

                if ($zip_temp->open($zipPath_temp, ZipArchive::CREATE) === TRUE)
                {
                        if (file_exists($teste_cliente2)){

                    // arquivos que serao adicionados ao zip
                    $files_temp = File::files($_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/NotasFiscais/$cpf_cnpj/$request->ano_xml_contador$request->meses_xml_contador/");

                            foreach ($files_temp as $key_temp => $value_temp) {
                                // nome/diretorio do arquivo dentro do zip
                                $relativeNameInZipFile_temp = basename($value_temp);
                        
                                // adicionar arquivo ao zip
                                $zip_temp->addFile($value_temp, $relativeNameInZipFile_temp);
                                
                            }
                            
                            // concluir a operacao
                            $zip_temp->close();

                            }else{

                                return response()->json([
                                    'status' => 'NOK',
                                    'data' => 'Não foram encontrados XML para seu período, retorne e tente enviar de outro mês.'
                                ]);
                    }
                }

                if ($zip_final->open($zipPath_final, ZipArchive::CREATE) === TRUE)
                {

                        // arquivos que serao adicionados ao zip
                        $files_final = File::files($_SERVER['DOCUMENT_ROOT'] . "/../../ApiFiscal/Release/XmlFiscal/Temp_download/");

                        foreach ($files_final as $key_final => $value_final) {
                        // nome/diretorio do arquivo dentro do zip
                        $relativeNameInZipFile_final = basename($value_final);

                        // adicionar arquivo ao zip
                        $zip_final->addFile($value_final, $relativeNameInZipFile_final);

                    }

                        // concluir a operacao
                        $zip_final->close();

                        $enviar_xml = Mail::send('mail.envio', ['mens' => $fields['story_contador']], function($m)use($request, $arquivo_xml, $zipPath_final) {
                            $m->from('naoresponder@empirescloud.com.br', 'Empires Cloud');
                            $m->to(trim($request->email_contador));
                            $m->subject($request->assunto_contador);
                            $m->attach($zipPath_final);
                        });

                        //removemos o arquivo zip após download
                            unlink($zipPath_temp);
                            unlink($zipPath_ev);

                        if (!$enviar_xml) {   

                            return response()->json([
                                'status' => 'OK',
                                'data' => 'E-mail enviado com sucesso.'
                            ]);
            
                        } else {
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Não foi possível enviar seu e-mail.'
                            ]);
                        }


                } 

                }
            }

        }

        } catch (\Exception $e) {
            return Helper::msg_exception($e->getMessage());
        }

    }

    public function print(Orcamento $orcamento)
    {
        $orcamento = Orcamento::with(['cliente', 'vendedor'])->find($orcamento->id);

        if (!$orcamento) {
            abort(404);
        }

        $itens = DB::connection('db_client')
            ->table('orcamentos_itens as i')
            ->join('produtos as p', 'i.produto_id', '=', 'p.id')
            ->select('p.descricao', 'i.quantidade', 'i.preco', 'i.subtotal')
            ->where('i.orcamento_id', $orcamento->id)
            ->orderBy('i.sequencial')
            ->get();

        return view('venda.orcamento.print', [
            'orcamento' => $orcamento,
            'itens' => $itens,
        ]);
    }

}
