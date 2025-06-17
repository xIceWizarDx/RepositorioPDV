<?php

namespace App\Http\Controllers\Venda;

use App\Helpers\Helper;
use App\Helpers\GTIN;
use App\Helpers\Param;
use App\Models\Cadastro\TabelaAuxiliar\FormaPagamento;
use App\Models\Venda\Orcamento;
use App\Models\Venda\OrcamentoFinalizacao;
use App\Models\Parametro\ParametroFiscal;
use App\Models\Cadastro\ContasBancos;
use App\Models\Venda\OrcamentoItens;
use App\Models\ApiFiscal;
use App\Models\Venda\OrcamentoParcela;
use App\Models\Cadastro\PerfilFiscal;
use App\Models\Financeiro\Receita;
use App\Models\Financeiro\CaixaMovimento;
use App\Models\Fiscal\NFeNFCeItens;
use App\Models\Cadastro\Produto;
use App\Models\Cadastro\TabelaAuxiliar\IBPTax;
use App\Models\Parametro\ParametroPorUsuario;
use App\Models\Parametro\ParametroFinanceiro;
use App\Models\Cadastro\TabelaAuxiliar\ModoPagar;
use App\Models\Cadastro\TabelaAuxiliar\ObsFiscal;
use App\Models\Cadastro\TabelaAuxiliar\NaturezaOperacao;
use App\Models\Cadastro\ProdutoEstoque;
use App\Models\Financeiro\CaixaAbertura;
use App\Models\Fiscal\NFeNFCe;
use App\Models\Cadastro\Pessoa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrcamentoFinalizacaoController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $empresa_id = intval($request->session()->get('empresa')['id']);

        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $request->orcamento_id)
            ->first();

        $finalizacao = OrcamentoFinalizacao::where('orcamento_id', $orcamento->id)->first();

        // VERIFICA SE TEM ALGUM PRODUTO COM PREÇO LIVRE
        $orcamento_itens_preco = OrcamentoItens::where('orcamento_id', $orcamento->id)->get();

        $tem_preco_livre = 0;

        for ($i = 1; $i <= count($orcamento_itens_preco); $i++) {

            $caca_produtos = OrcamentoItens::where('sequencial', [$i])->where('orcamento_id', $orcamento->id)->first();

            $produtos_preco = Produto::where('id', $caca_produtos->produto_id)->first();

            /*$produtos_preco = DB::connection('db_client')
            ->table('orcamentos_itens as o')
            ->select([
                'o.*',
                'p.*'
            ])
            ->join('produtos as p', 'p.id', '=', 'o.produto_id')
            ->where('p.preco_indefinido', 1)
            ->where('o.sequencial', [$i])
            ->where('o.orcamento_id', $orcamento->id)
            ->first();*/

            $tem_preco_livre += $produtos_preco->preco_indefinido + 0;
        }

        $param_finan = ParametroFinanceiro::where('empresa_id', $empresa_id)->first();

        if (empty($param_finan))

            $param_finan =  0;

        else
            $param_finan =  $param_finan->controle_finan;



        $validar = $this->status_empresa($empresa_id);

        $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)->get();

        $formas_pagamentos = FormaPagamento::orderBy('sequencial', 'asc')->where('is_active', '>', 0)->get();

        $mod_pagar = ModoPagar::orderBy('sequencia', 'asc')->where('descricao_id', '=', 'BOL')->get();

        if (!empty($orcamento)) {
            return response()->json([
                'status' => 'OK',
                'form' => view('venda.orcamento_finalizar.create')
                    ->with('orcamento', $orcamento)
                    ->with('finalizacao', $finalizacao)
                    ->with('parcelas', $parcelas)
                    ->with('tem_preco_livre', $tem_preco_livre)
                    ->with('formas_pagamentos', $formas_pagamentos)
                    ->with('mod_pagar', $mod_pagar)
                    ->with('validar', $validar)
                    ->with('param_finan', $param_finan)
                    ->render()
            ]);
        } else {
            return response()->json([
                'status' => 'NOK',
                'message' => 'Orçamento não encontrado.'
            ]);
        }
    }

    private function status_empresa($empresa_id)
    {


        $param_finan = ParametroFinanceiro::where('empresa_id', $empresa_id)->first();

        if (!empty($param_finan) && ($param_finan->gastos_fixo && $param_finan->gastos_variados) > 0) {

            //Calculo para saber em qual situação a empresa da pessoa está
            $dia_hj = date('d');

            $verificar = date('t');

            $mess = date('m');

            $total_mes = DB::connection('db_client')
                ->table('orcamentos', 'o')
                ->where('o.empresa_id', $empresa_id)
                ->where('o.status', 'FATURADO')
                ->where('o.tipo_movimento_id', 1)
                ->whereRaw('DATE_FORMAT(o.dh_faturado, "%m") = ?', [$mess])
                ->sum('o.total_liquido');

            $controle_int = ($param_finan->gastos_fixo + $param_finan->gastos_variados) / $verificar;

            $controle_mes = $total_mes / $dia_hj;

            $controle_crit = $controle_int / 2; //Se as vendas da empresa estão abaixo de 50% do recomendado por dia

            //Inicio da verificação de status da empresa
            if ($verificar == 31) {

                if ($dia_hj < 5) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 25 && $dia_hj < 31)) {
                    $status = 'Controlado';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 25 && $dia_hj < 31)) {
                    $status = 'Crítico';
                } else if (($controle_mes < $controle_int && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 25 && $dia_hj < 31)) {
                    $status = 'Alerta';
                }
            } else if ($verificar == 30) {

                if ($dia_hj < 5) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 25 && $dia_hj < 30)) {
                    $status = 'Controlado';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 25 && $dia_hj < 30)) {
                    $status = 'Crítico';
                } else if (($controle_mes < $controle_int && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 25 && $dia_hj < 30)) {
                    $status = 'Alerta';
                }
            } else if ($verificar == 28) {

                if ($dia_hj < 5) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Controlado';
                } else if (($controle_mes >= $controle_int && $dia_hj >= 25 && $dia_hj < 28)) {
                    $status = 'Controlado';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Crítico';
                } else if (($controle_mes <= $controle_crit && $dia_hj >= 25 && $dia_hj < 28)) {
                    $status = 'Crítico';
                } else if (($controle_mes < $controle_int && $dia_hj >= 5 && $dia_hj < 10)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 10 && $dia_hj < 15)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 15 && $dia_hj < 20)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 20 && $dia_hj < 25)) {
                    $status = 'Alerta';
                } else if (($controle_mes < $controle_int && $dia_hj >= 25 && $dia_hj < 28)) {
                    $status = 'Alerta';
                }
            }
        } else {

            $status = 'Controlado';
        }

        return $status;
    }

    public function oque_fazer(Request $request)
    {

        $empresa_id = intval($request->session()->get('empresa')['id']);

        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $request->orcamento_id)
            ->first();

        $finalizacao = OrcamentoFinalizacao::where('orcamento_id', $orcamento->id)
            ->first();

        $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)
            ->get();

        $contas = ContasBancos::where('conta_padrao', 1)->first();


        if (empty($contas)) {
            $tem_conta = 0;
        } else {
            $tem_conta = 1;
        }


        //VERIFICA SE TEM PAGAMENTO EM TRANSFERENCIA BANCARIA
        $final = OrcamentoParcela::where('forma_pagamento_id', 'TRA')->where('orcamento_id', $orcamento->id)->get();

        if (count($final) > 0) {
            $aparece = 1;
        } else {
            $aparece = 0;
        }


        $formas_pagamentos = FormaPagamento::orderBy('sequencial', 'asc')->where('is_active', '>', 0)->get();

        if (!empty($orcamento)) {
            return response()->json([
                'status' => 'OK',
                'form' => view('venda.orcamento_finalizar.oque_fazer')
                    ->with('orcamento', $orcamento)
                    ->with('finalizacao', $finalizacao)
                    ->with('parcelas', $parcelas)
                    ->with('formas_pagamentos', $formas_pagamentos)
                    ->with('aparece', $aparece)
                    ->with('tem_conta', $tem_conta)
                    ->render()
            ]);
        } else {
            return response()->json([
                'status' => 'NOK',
                'message' => 'Orçamento não encontrado.'
            ]);
        }
    }

    public function clicou_faturou(Orcamento $orcamento, Request $request)
    {

        $empresa_id = intval($request->session()->get('empresa')['id']);

        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $orcamento->id)
            ->first();

        $finalizacao = OrcamentoFinalizacao::where('orcamento_id', $orcamento->id)
            ->first();

        $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)
            ->get();

        $formas_pagamentos = FormaPagamento::orderBy('sequencial', 'asc')->where('is_active', '>', 0)->get();

        $perfis = PerfilFiscal::where('empresa_id', $empresa_id)->where('is_padrao', '>', 0)->get();

        // SE EXISTE NOTA ABERTA
        $nota = NFeNFCe::where('empresa_id', $empresa_id)
            ->where('orcamento_id', $orcamento->id)
            ->where('is_transmitido', 0)
            ->where('is_cancelado', 0)
            ->first();

        return response()->json([
            'status' => 'OK',
            'data' => view('venda.orcamento_finalizar.clicou_faturou')
                ->with('orcamento', $orcamento)
                ->with('finalizacao', $finalizacao)
                ->with('parcelas', $parcelas)
                ->with('formas_pagamentos', $formas_pagamentos)
                ->with('nota', $nota)
                ->with('perfis', $perfis)
                ->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function pesquisa_forma_pagamento(Request $request)
    {

        $fields = $request->all();

        $mod_pagar = FormaPagamento::where('sequencial', $fields['forma'])->first();

        if (empty($mod_pagar)) {
            $mod_pagar = FormaPagamento::where('id', $fields['fpgto'])->first();
        } else {
            $mod_pagar = FormaPagamento::where('sequencial', $fields['forma'])->first();
        }


        if ($mod_pagar->livre_pag > 0 && $mod_pagar->is_active > 0) {

            $modo_forms = ModoPagar::where('descricao_id', $mod_pagar->id)->first();

            if (!empty($modo_forms)) {
                $modo = ModoPagar::where('descricao_id', $mod_pagar->id)->get('pag_mod');
                $modo_forms_id = ModoPagar::where('descricao_id', $mod_pagar->id)->get('descricao_id');

                $mod_pagar = $modo_forms->descricao_id;
                $form_pag = $modo;
                $modo_forms_id = $modo_forms_id;
                $passou = 'OK';
            } else {
                $mod_pagar = $mod_pagar->id;
                $form_pag = '';
                $modo_forms_id = '';
                $passou = 'NOK2';
            }

            return response()->json([
                'mod_pagar' => $mod_pagar,
                'modo_forms_id' => $modo_forms_id,
                'form_pag' => $form_pag,
                'passou' => $passou,
                'data' => 'Nenhuma condição cadastrada para a forma de pagamento. Desative o pagamento livre ou adicione uma ou mais formas!'
            ]);
        } else {

            $modo_forms_vere = ModoPagar::where('descricao_id', $mod_pagar->id)->first();

            if (empty($modo_forms_vere) && $mod_pagar->is_active < 1) {
                $mod_pagar = $mod_pagar->id;
                $form_pag = '';
                $modo_forms_id = '';
                $passou = 'NOK3';
            } else {
                $mod_pagar = $mod_pagar->id;
                $form_pag = '';
                $modo_forms_id = '';
                $passou = 'NOK';
            }

            return response()->json([
                'mod_pagar' => $mod_pagar,
                'form_pag' => $form_pag,
                'passou' => $passou,
                'data' => 'Forma de pagamento não está ativa, por favor escolha outra ou ative a mesma.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function store_emitir_notanfce(Request $request)
    {
        //Base de informações para complemento dos demais campos        
        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $param = ParametroPorUsuario::where('user_id', auth()->id())->first();
        $perfis = PerfilFiscal::where('empresa_id', $empresa_id)->where('is_padrao', '>', 0)->first();
        $orcamento_id = intval($request->input('orcamento_id'));

        $nNF = NFeNFCe::where('empresa_id', $empresa_id)
            ->where('ide_mod', 65)
            ->where('ide_serie', $param->fiscal_serie_nota)
            ->max('ide_nNF');

        if (empty($nNF)) {
            $nNF = 1;
        } else {
            $nNF = intval($nNF) + 1;
        }

        $perfil_fiscal_id = $perfis->id;
        $mod = 65; //Emissão de NF-e, por isso o codigo é 55.
        $numero_nota = $nNF; //Tem que ser de modo manual a procura pois os modos são definidos na hora de clicar.
        $consumidor_final = !empty($request->boolean('consumidor_final')) ? (int)$request->boolean('consumidor_final') : 0;
        $ide_idDest = !empty($request->boolean('ide_idDest')) ? $request->boolean('ide_idDest') : 0;

        $finalizade_id = 1;
        $natureza_operacao_id = 1;
        $consumidor_nome = empty($request->input('consumidor_nome')) ? '' : trim($request->input('consumidor_nome'));
        $consumidor_cpf = empty($request->input('consumidor_cpf')) ? '' : trim($request->input('consumidor_cpf'));

        $total_frete = $request->input('total_frete');
        $total_seguro = $request->input('total_seguro');
        $total_outros = $request->input('total_outros');

        $total_frete = !empty($total_frete) ? str_replace(['.', ','], ['', '.'], $total_frete) : 0;
        $total_seguro = !empty($total_seguro) ? str_replace(['.', ','], ['', '.'], $total_seguro) : 0;
        $total_outros = !empty($total_outros) ? str_replace(['.', ','], ['', '.'], $total_outros) : 0;

        // VALIDACOES
        if (empty($mod)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Selecione o TIPO DE DOCUMENTO'
            ]);
        }
        if (empty($numero_nota)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'É necessário um numero de nota fiscal'
            ]);
        }
        if (!empty($consumidor_cpf)) {
            $consumidor_cpf = str_replace(['.', '-'], '', $consumidor_cpf);
        }

        // VALIDAR A SERIE
        $param = ParametroPorUsuario::where('user_id', auth()->id())->first();
        if (empty($param)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Acesse parametros -> fiscal e defina a série ( obs. coloque 0 caso não houver nenhum )'
            ]);
        } else if (trim($param->fiscal_serie_nota) == '') {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Acesse parametros -> fiscal e defina a série ( obs. coloque 0 caso não houver nenhum )'
            ]);
        } else {
            $serie = $param->fiscal_serie_nota;
        }

        // ORCAMENTO
        $orcamento = Orcamento::where('id', $orcamento_id)->first();
        if (empty($orcamento)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Não foi possível encontrar o orçamento em nossa base de dados'
            ]);
        }
        if ($orcamento->status != 'FATURADO') {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Só é possível emitir nota fiscal com orçamento faturado'
            ]);
        }
        if ($orcamento->tipo_movimento_id != 1) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Só é possível emitir nota fiscal com vendas'
            ]);
        }

        // ORCAMENTO ITENS
        $orcamento_itens = OrcamentoItens::where('orcamento_id', $orcamento->id)
            ->get();

        $orcamento_itens_quant = $orcamento_itens->count();

        if (count($orcamento_itens) <= 0) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'ITENS NÃO ENCONTRADO PARA ESTA VENDA'
            ]);
        }

        $perfil = PerfilFiscal::where('id', $perfil_fiscal_id)->first();
        if (empty($perfil)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Não foi possível encontrar em nossa base de dados'
            ]);
        }

        $natureza_operacao = NaturezaOperacao::where('id', $natureza_operacao_id)->first();
        if (empty($natureza_operacao)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Não foi possível encontrar em nossa base de dados'
            ]);
        }
        $natureza_operacao = $natureza_operacao->descricao;

        if ($mod == 55 && $orcamento->cliente->id == 1) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Altere o cliente da venda ou selecione NFCe'
            ]);
        }

        // ------------------------------------
        //   GERA A ESTRUTURA NA NOTA FISCAL
        // ------------------------------------
        $nf = [];
        $nf['empresa_id'] = $empresa_id;
        $nf['orcamento_id'] = $orcamento->id;
        $nf['fiscal_perfil_id'] = $perfil_fiscal_id;
        $nf['fiscal_natureza_operacao_id'] = $natureza_operacao_id;
        $nf['ide_cUF'] = $orcamento->empresa->cidade->estado_id;
        $nf['ide_cNF'] = $numero_nota + 1; //mt_rand(11111111,99999999);
        $nf['ide_natOp'] = Helper::sanitizeString($natureza_operacao);
        $nf['ide_indPag'] = $this->isAVista($orcamento->id) ? 0 : 1;
        $nf['ide_mod'] = $mod;
        $nf['ide_serie'] = $serie;
        $nf['ide_nNF'] = $numero_nota;
        $nf['ide_lote'] = $numero_nota;
        $nf['ide_dhEmi'] = date('Y-m-d H:i:s');

        if ($mod == 55)
            $nf['ide_dhSaiEnt'] = date('Y-m-d H:i:s');

        $nf['ide_tpNF'] = 1; //0 entrada | 1 saida
        $nf['ide_idDest'] = ($ide_idDest == 1) ? 2 : 1;
        $nf['ide_cMunFG'] = $orcamento->empresa->cidade->cod_ibge;
        $nf['ide_tpImp'] = ($mod == 65) ? 4 : 1;
        $nf['ide_tpEmis'] = 1;
        $nf['ide_tpAmb'] = Param::getFiscalAmbiente();
        $nf['ide_finNFe'] = $finalizade_id;
        $nf['ide_indFinal'] = $consumidor_final;
        $nf['ide_indPres'] = 1;
        $nf['ide_procEmi'] = 0;
        $nf['ide_verProc'] = 'v1';

        // EMITENTE
        $nf['emit_cnpj_cpf'] = $orcamento->empresa->cpf_cnpj;
        $nf['emit_xNome'] = Helper::sanitizeString($orcamento->empresa->razao);
        $nf['emit_xFant'] = !empty($orcamento->empresa->fantasia) ? Helper::sanitizeString($orcamento->empresa->fantasia) : null;
        $nf['emit_xLgr'] = Helper::sanitizeString($orcamento->empresa->endereco);
        $nf['emit_nro'] = $orcamento->empresa->numero;
        $nf['emit_xCpl'] = !empty($orcamento->empresa->complemento) ? Helper::sanitizeString($orcamento->empresa->complemento) : null;
        $nf['emit_xBairro'] = Helper::sanitizeString($orcamento->empresa->bairro);
        $nf['emit_cMun'] = $orcamento->empresa->cidade->cod_ibge;
        $nf['emit_xMun'] = Helper::sanitizeString($orcamento->empresa->cidade->cidade);
        $nf['emit_UF'] = $orcamento->empresa->cidade->uf;
        $nf['emit_CEP'] = $orcamento->empresa->cep;
        $nf['emit_cPais'] = 1058;
        $nf['emit_xPais'] = 'BRASIL';
        $nf['emit_fone'] = !empty($orcamento->empresa->tel_fixo) ? $orcamento->empresa->tel_fixo : null;
        $nf['emit_IE'] = $orcamento->empresa->ie;
        $nf['emit_IEST'] = null;
        $nf['emi_IM'] = $orcamento->empresa->im;
        $nf['emi_CNAE'] = !empty($orcamento->empresa->cnae) ? $orcamento->empresa->cnae : null;
        $nf['emi_CRT'] = $perfil->crt_id;

        // DESTINATARIO
        if ($mod == 55) {
            $nf['dest_cpf_cnpj'] = $orcamento->cliente->cpf_cnpj;
            $nf['dest_xNome'] = !empty($orcamento->cliente->nome_razao) ? Helper::sanitizeString($orcamento->cliente->nome_razao) : null;
            $nf['dest_xLgr'] = Helper::sanitizeString($orcamento->cliente->endereco);
            $nf['dest_nro'] = $orcamento->cliente->numero;
            $nf['dest_xCpl'] = !empty($orcamento->cliente->dest_xCpl) ? Helper::sanitizeString($orcamento->cliente->complemento) : null;
            $nf['dest_xBairro'] = Helper::sanitizeString($orcamento->cliente->bairro);
            $nf['dest_cMun'] = $orcamento->cliente->cidade->cod_ibge;
            $nf['dest_xMun'] = Helper::sanitizeString($orcamento->cliente->cidade->cidade);
            $nf['dest_UF'] = $orcamento->cliente->cidade->uf;
            $nf['dest_CEP'] = !empty($orcamento->cliente->cep) ? $orcamento->cliente->cep : null;
            $nf['dest_cPais'] = 1058;
            $nf['dest_xPais'] = 'BRASIL';
            $nf['dest_fone'] = !empty($orcamento->cliente->tel_fixo) ? $orcamento->cliente->tel_fixo : null;

            $nf['dest_ISUF'] = !empty($orcamento->cliente->isuf) ? $orcamento->cliente->isuf : null;
            $nf['dest_IM'] = !empty($orcamento->cliente->im) ? $orcamento->cliente->im : null;
            $nf['dest_email'] = !empty($orcamento->cliente->email) ? $orcamento->cliente->email : null;
            $nf['autXML_cpfcnpj'] = $nf['dest_cpf_cnpj'];
        }

        $nf['dest_indIEDest'] = $orcamento->cliente->indIEDest;
        if ($orcamento->cliente->indIEDest == 1 || $orcamento->cliente->indIEDest == 2)
            $nf['dest_IE'] = $orcamento->cliente->ie;
        else
            $nf['dest_IE'] = null;
        $nf['consumidor_nome'] = !empty($consumidor_nome) ? Helper::sanitizeString($consumidor_nome) : null;
        $nf['consumidor_cpf'] = !empty($consumidor_cpf) ? Helper::sanitizeString($consumidor_cpf) : null;
        $nf['total_frete'] = $total_frete;
        $nf['total_seguro'] = $total_seguro;
        $nf['total_outro'] = $total_outros;
        $nf['total_desconto'] = $orcamento->desconto_valor;
        $nf['user_idCreated'] = auth()->id();
        $nf['user_idUpdated'] = auth()->id();

        $obs_fiscal = ObsFiscal::where('empresa_id', $empresa_id)->first();
        if (!empty($obs_fiscal))
            $nf['obs_fiscal'] = $obs_fiscal->obs;

        $nf['obs_orcamento'] = !empty($orcamento->observacao) ? $orcamento->observacao : null;

        DB::beginTransaction();

        try {

            $nf_updated = false;
            if (empty($fiscal_nf)) {
                $nf_created = NFeNFCe::create($nf);
            } else {
                $nf_updated = $fiscal_nf->update($nf);
            }

            if (!empty($nf_created) || $nf_updated) {

                if ($nf_updated) {
                    $nf_created = NFeNFCe::find($fiscal_nf->id);
                }

                // MARCA O ORCAMENTO COMO XML GERADO
                $orcamento->user_idXMLGerado = auth()->id();
                $orcamento->dh_xml_gerado = date('Y-m-d H:i:s');
                $orcamento->is_xml_gerado = 1;
                $orcamento->save();

                // GERAR OS ITENS
                $uf_emi = $orcamento->empresa->cidade->uf;

                // DELETA OS ITENS
                NFeNFCeItens::where('nf_id', $nf_created->id)->delete();


                foreach ($orcamento_itens as $item) {

                    $produto = Produto::find($item->produto_id);

                    $ibptax = IBPTax::where('uf', $uf_emi)
                        ->where('ncm', $produto->ncm)
                        ->first();

                    $nf_item = [];
                    $nf_item['nf_id'] = $nf_created->id;
                    $nf_item['produto_id'] = $produto->id;
                    $nf_item['nItem'] = $item->sequencial;
                    $nf_item['cProd'] = $produto->id;
                    if (empty($produto->cEAN)) {
                        $nf_item['cEAN'] = 'SEM GTIN';
                    } else {
                        if (GTIN::validate($produto->cEAN))
                            $nf_item['cEAN'] = $produto->cEAN;
                        else
                            $nf_item['cEAN'] = 'SEM GTIN';
                    }
                    $nf_item['xProd'] = Helper::sanitizeString($produto->descricao);
                    $nf_item['NCM'] = $produto->ncm;
                    //$nf_item['NVE'] = '';
                    //$nf_item['EXTIPI'] = '';
                    if ($ide_idDest == 1)
                        $nf_item['CFOP'] = $perfil->cfop_fora_uf;
                    else
                        $nf_item['CFOP'] = $perfil->cfop_dentro_uf;
                    $nf_item['uCom'] = $produto->unidadeComercial->sigla;
                    $nf_item['qCom'] = $item->quantidade;
                    $nf_item['vUnCom'] = $item->preco;
                    $nf_item['vProd'] = number_format($item->quantidade * $item->preco, 2, '.', '');
                    if (empty($produto->cEANTrib)) {
                        if (!empty($produto->cEAN)) {
                            if (GTIN::validate($produto->cEAN))
                                $nf_item['cEANTrib'] = $produto->cEAN;
                            else
                                $nf_item['cEANTrib'] = null;
                        } else {
                            $nf_item['cEANTrib'] = null;
                        }
                    } else {
                        if (GTIN::validate($produto->cEANTrib))
                            $nf_item['cEANTrib'] = $produto->cEANTrib;
                        else
                            $nf_item['cEANTrib'] = null;
                    }
                    $nf_item['uTrib'] = $produto->unidadeTributada->sigla;
                    $nf_item['qTrib'] = number_format($item->quantidade * $produto->qTrib, 2, '.', '');
                    $nf_item['vUnTrib'] = $nf_item['vProd'] / $nf_item['qTrib'];

                    $nf_item['vDesc'] = $item->desconto;

                    $nf_item['vFrete'] = number_format($total_frete / $orcamento_itens_quant, 2, '.', '');
                    $nf_item['vSeg'] = 0;
                    $nf_item['vOutro'] = 0;

                    $nf_item['indTot'] = 1;
                    $nf_item['orig'] = $perfil->st_origem_id;
                    $nf_item['cst_id'] = $perfil->st_icms_id;
                    $nf_item['csosn_id'] = $perfil->csosn_id;
                    $nf_item['pCredSN'] = empty($perfil->aliq_cred_sn) ? 0 : $perfil->aliq_cred_sn;
                    $nf_item['modBCST'] = empty($perfil->modbcst_id) ? 0 : $perfil->modbcst_id;
                    $nf_item['pMVAST'] = empty($perfil->aliq_mvast) ? 0 : $perfil->aliq_mvast;
                    $nf_item['pRedBCST'] = empty($perfil->aliq_red_bc_st) ? 0 : $perfil->aliq_red_bc_st;
                    $nf_item['pICMSST'] = empty($perfil->aliq_icms_st) ? 0 : $perfil->aliq_icms_st;
                    $nf_item['modBC'] = empty($perfil->modbc_id) ? 0 : $perfil->modbc_id;
                    $nf_item['pRedBC'] = empty($perfil->aliq_red_bc) ? 0 : $perfil->aliq_red_bc;
                    $nf_item['pICMS'] = empty($perfil->aliq_icms) ? 0 : $perfil->aliq_icms;

                    if (!empty($ibptax)) {
                        $nf_item['pTribEst'] = $ibptax->aliq_estadual;
                        $nf_item['pTribFed'] = $ibptax->aliq_nacional;
                    } else {
                        $nf_item['pTribEst'] = 0;
                        $nf_item['pTribFed'] = 0;
                    }

                    $nf_item['cst_pis'] = $perfil->st_pis_id;
                    $nf_item['cst_cofins'] = $perfil->st_cofins_id;
                    $nf_item['pPIS'] = $perfil->aliq_pis;
                    $nf_item['pCOFINS'] = $perfil->aliq_cofins;

                    $nf_item['comb_cod_anp'] = $produto->comb_cod_anp;
                    $nf_item['comb_desc_anp'] = $produto->comb_desc_anp;
                    $nf_item['comb_uf_anp'] = $produto->comb_uf_anp;
                    $nf_item['comb_perc_glp'] = $produto->comb_perc_glp;
                    $nf_item['comb_perc_gnn'] = $produto->comb_perc_gnn;

                    try {

                        $item_created = NFeNFCeItens::create($nf_item);

                        if (empty($item_created)) {
                            DB::rollBack();
                            break;
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        break;
                    }
                }

                DB::commit();

                //--------------------------------
                // ENVIA PARA A API GERAR O XML
                //--------------------------------
                $param_fiscal = ParametroFiscal::where('empresa_id', $empresa_id)->first();
                $nf_itens = NFeNFCeItens::where('nf_id', $nf_created->id)->get();
                $orc_parcelas = OrcamentoParcela::where('orcamento_id', $orcamento_id)->get();
                if (!empty($param_fiscal)) {

                    $content = [
                        'hashCert' => $param_fiscal->cert_hash,
                        'passCert' => $param_fiscal->cert_pass,
                        'idCsc' => $param_fiscal->IdCSC,
                        'csc' => $param_fiscal->CSC,
                        'nf' => $nf_created,
                        'nf_itens' => $nf_itens,
                        'orc_parcelas' => $orc_parcelas
                    ];

                    $curl = ApiFiscal::getCurl(
                        '/fiscal/gerar_nota',
                        'POST',
                        $content
                    );

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $response = json_decode($response, true);

                    if ($response['status'] == 'OK') {

                        // ATUALIZA TRANSMITIDO
                        $row = [];
                        $row['chave'] = $response['chave'];
                        $row['cStat'] = $response['cStat'];
                        $row['nRec'] = (trim($response['nRec']) != '') ? $response['nRec'] : null;

                        $row['prot_cStat'] = $response['prot_cStat'];
                        $row['prot_nProt'] = $response['prot_nProt'];
                        $row['prot_xMotivo'] = $response['prot_xMotivo'];

                        $row['is_transmitido'] = intval($response['bEnviado']);

                        if (intval($row['cStat']) == 100 || $row['prot_cStat'] == 100)
                            $row['is_autorizado'] = 1;

                        $updated = $nf_created->update($row);

                        if ($updated) {
                            return response()->json([
                                'status' => 'OK',
                                'nf_id' => $nf_created->id,
                                'data' => 'Sua NFC-e ' . $nf_created->ide_nNF . ' foi criada com sucesso!'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Houve algum problema ao atualizar com o retorno da nota fiscal.'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => 'NOK',
                            'data' => $response['message']
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 'NOK',
                        'data' => 'Acesse parametros->fiscal'
                    ]);
                }

                return response()->json([
                    'status' => 'OK',
                    'data' => 'XML Gerado com sucesso.'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'XML da nota fiscal não foi gerado'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::msg_nok($e->getMessage());
        }
    }

    public function store_emitir_nota(Request $request)
    {

        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $orcamento_id = intval($request->input('orcamento_id'));
        $param = ParametroPorUsuario::where('user_id', auth()->id())->first();
        $perfis = PerfilFiscal::where('empresa_id', $empresa_id)->where('is_padrao', '>', 0)->first();
        $perfil_fiscal_id = $perfis->id;
        $mod = 55;

        $nNF = NFeNFCe::where('empresa_id', $empresa_id)
            ->where('ide_mod', 55)
            ->where('ide_serie', $param->fiscal_serie_nota)
            ->max('ide_nNF');

        if (empty($nNF)) {
            $nNF = 1;
        } else {
            $nNF = intval($nNF) + 1;
        }



        $numero_nota = $nNF;
        $consumidor_final = !empty($request->boolean('consumidor_final')) ? (int)$request->boolean('consumidor_final') : 0;
        $ide_idDest = !empty($request->boolean('ide_idDest')) ? $request->boolean('ide_idDest') : 0;

        $obs_fiscal_novo = $request->input('gerarxml_obs');

        $finalizade_id = 1;
        $natureza_operacao_id = 1;
        $nf_referenciada = '';
        $consumidor_nome = empty($request->input('consumidor_nome')) ? '' : trim($request->input('consumidor_nome'));
        $consumidor_cpf = empty($request->input('consumidor_cpf')) ? '' : trim($request->input('consumidor_cpf'));

        $modalidade_frete = 9;
        $transporte_frete = '';
        $total_frete = $request->input('total_frete');
        $total_seguro = $request->input('total_seguro');
        $total_outros = $request->input('total_outros');

        $total_frete = !empty($total_frete) ? str_replace(['.', ','], ['', '.'], $total_frete) : 0;
        $total_seguro = !empty($total_seguro) ? str_replace(['.', ','], ['', '.'], $total_seguro) : 0;
        $total_outros = !empty($total_outros) ? str_replace(['.', ','], ['', '.'], $total_outros) : 0;

        if ($finalizade_id == '4' || $finalizade_id == '2' || $finalizade_id == '3') {
            $nf_nu = NFeNFCe::where('id', $nf_referenciada)->first();
            $nf_nu_final = $nf_nu->chave;
        }

        // VALIDACOES
        if (empty($mod)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Selecione o TIPO DE DOCUMENTO'
            ]);
        }
        if (empty($numero_nota)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'É necessário um numero de nota fiscal'
            ]);
        }
        if (empty($nf_referenciada) && ($finalizade_id == '4' || $finalizade_id == '2' || $finalizade_id == '3')) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'É necessário um numero de nota fiscal'
            ]);
        }
        if (!empty($consumidor_cpf)) {
            $consumidor_cpf = str_replace(['.', '-', '/'], '', $consumidor_cpf);
        }

        // VALIDAR A SERIE
        $param = ParametroPorUsuario::where('user_id', auth()->id())->first();
        if (empty($param)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Acesse parametros -> fiscal e defina a série ( obs. coloque 0 caso não houver nenhum )'
            ]);
        } else if (trim($param->fiscal_serie_nota) == '') {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Acesse parametros -> fiscal e defina a série ( obs. coloque 0 caso não houver nenhum )'
            ]);
        } else {
            $serie = $param->fiscal_serie_nota;
        }

        // ORCAMENTO
        $orcamento = Orcamento::find($orcamento_id);
        if (empty($orcamento)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Não foi possível encontrar o orçamento em nossa base de dados'
            ]);
        }
        if ($orcamento->status != 'FATURADO') {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Só é possível emitir nota fiscal com orçamento faturado'
            ]);
        }
        if ($orcamento->tipo_movimento_id != 1) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Só é possível emitir nota fiscal com vendas'
            ]);
        }

        // ORCAMENTO ITENS
        $orcamento_itens = OrcamentoItens::where('orcamento_id', $orcamento->id)
            ->get();

        $orcamento_itens_quant = $orcamento_itens->count();

        if (count($orcamento_itens) <= 0) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'ITENS NÃO ENCONTRADO PARA ESTA VENDA'
            ]);
        }

        $perfil = PerfilFiscal::find($perfil_fiscal_id);
        if (empty($perfil)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Não foi possível encontrar em nossa base de dados'
            ]);
        }

        $natureza_operacao = NaturezaOperacao::find($natureza_operacao_id);
        if (empty($natureza_operacao)) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Não foi possível encontrar em nossa base de dados'
            ]);
        }
        $natureza_operacao = $natureza_operacao->descricao;

        if ($mod == 55 && $orcamento->cliente->id == 1) {
            return response()->json([
                'status' => 'NOK',
                'data' => 'Altere o cliente da venda ou selecione NFCe'
            ]);
        }

        $fiscal_nf = NFeNFCe::where('empresa_id', $empresa_id)
            ->where('orcamento_id', $orcamento_id)
            ->where('is_transmitido', 0)
            ->where('is_cancelado', 0)
            ->first();

        // REFRESH NO NUMERO DA NOTA
        if (empty($fiscal_nf)) {
            $nNF = NFeNFCe::where('empresa_id', $empresa_id)
                ->where('ide_mod', $mod)
                ->where('ide_serie', $serie)
                ->max('ide_nNF');

            if (empty($nNF)) {
                $numero_nota = 1;
            } else {
                $numero_nota = intval($nNF) + 1;
            }
        }

        // ------------------------------------
        //   GERA A ESTRUTURA NA NOTA FISCAL
        // ------------------------------------
        $nf = [];
        $nf['empresa_id'] = $empresa_id;
        $nf['orcamento_id'] = $orcamento->id;
        $nf['fiscal_perfil_id'] = $perfil_fiscal_id;
        $nf['fiscal_natureza_operacao_id'] = $natureza_operacao_id;
        $nf['ide_cUF'] = $orcamento->empresa->cidade->estado_id;
        $nf['ide_cNF'] = $numero_nota + 1; //mt_rand(11111111,99999999);
        $nf['ide_natOp'] = Helper::sanitizeString($natureza_operacao);
        $nf['ide_indPag'] = $this->isAVista($orcamento->id) ? 0 : 1;
        $nf['ide_mod'] = $mod;
        $nf['ide_serie'] = $serie;
        $nf['ide_nNF'] = $numero_nota;
        $nf['ide_lote'] = $numero_nota;
        $nf['ide_dhEmi'] = date('Y-m-d H:i:s');

        if ($mod == 55)
            $nf['ide_dhSaiEnt'] = date('Y-m-d H:i:s');

        $nf['ide_tpNF'] = 1; //0 entrada | 1 saida
        $nf['ide_idDest'] = ($ide_idDest == 1) ? 2 : 1;
        $nf['ide_cMunFG'] = $orcamento->empresa->cidade->cod_ibge;
        $nf['ide_tpImp'] = ($mod == 65) ? 4 : 1;
        $nf['ide_tpEmis'] = 1;
        $nf['ide_tpAmb'] = Param::getFiscalAmbiente();
        $nf['ide_finNFe'] = $finalizade_id;
        $nf['ide_indFinal'] = $consumidor_final;
        $nf['ide_indPres'] = 1;
        $nf['ide_procEmi'] = 0;
        $nf['ide_verProc'] = 'v1';
        $nf['chave_ref'] =  !empty($nf_nu_final) ? $nf_nu_final : null;

        // EMITENTE
        $nf['emit_cnpj_cpf'] = $orcamento->empresa->cpf_cnpj;
        $nf['emit_xNome'] = Helper::sanitizeString($orcamento->empresa->razao);
        $nf['emit_xFant'] = !empty($orcamento->empresa->fantasia) ? Helper::sanitizeString($orcamento->empresa->fantasia) : null;
        $nf['emit_xLgr'] = Helper::sanitizeString($orcamento->empresa->endereco);
        $nf['emit_nro'] = $orcamento->empresa->numero;
        $nf['emit_xCpl'] = !empty($orcamento->empresa->complemento) ? Helper::sanitizeString($orcamento->empresa->complemento) : null;
        $nf['emit_xBairro'] = Helper::sanitizeString($orcamento->empresa->bairro);
        $nf['emit_cMun'] = $orcamento->empresa->cidade->cod_ibge;
        $nf['emit_xMun'] = Helper::sanitizeString($orcamento->empresa->cidade->cidade);
        $nf['emit_UF'] = $orcamento->empresa->cidade->uf;
        $nf['emit_CEP'] = $orcamento->empresa->cep;
        $nf['emit_cPais'] = 1058;
        $nf['emit_xPais'] = 'BRASIL';
        $nf['emit_fone'] = !empty($orcamento->empresa->tel_fixo) ? $orcamento->empresa->tel_fixo : null;
        $nf['emit_IE'] = $orcamento->empresa->ie;
        $nf['emit_IEST'] = null;
        $nf['emi_IM'] = $orcamento->empresa->im;
        $nf['emi_CNAE'] = !empty($orcamento->empresa->cnae) ? $orcamento->empresa->cnae : null;
        $nf['emi_CRT'] = $perfil->crt_id;

        // DESTINATARIO
        if ($mod == 55) {
            $nf['dest_cpf_cnpj'] = $orcamento->cliente->cpf_cnpj;
            $nf['dest_xNome'] = !empty($orcamento->cliente->nome_razao) ? Helper::sanitizeString($orcamento->cliente->nome_razao) : null;
            $nf['dest_xLgr'] = Helper::sanitizeString($orcamento->cliente->endereco);
            $nf['dest_nro'] = $orcamento->cliente->numero;
            $nf['dest_xCpl'] = !empty($orcamento->cliente->dest_xCpl) ? Helper::sanitizeString($orcamento->cliente->complemento) : null;
            $nf['dest_xBairro'] = Helper::sanitizeString($orcamento->cliente->bairro);
            $nf['dest_cMun'] = $orcamento->cliente->cidade->cod_ibge;
            $nf['dest_xMun'] = Helper::sanitizeString($orcamento->cliente->cidade->cidade);
            $nf['dest_UF'] = $orcamento->cliente->cidade->uf;
            $nf['dest_CEP'] = !empty($orcamento->cliente->cep) ? $orcamento->cliente->cep : null;
            $nf['dest_cPais'] = 1058;
            $nf['dest_xPais'] = 'BRASIL';
            $nf['dest_fone'] = !empty($orcamento->cliente->tel_fixo) ? $orcamento->cliente->tel_fixo : null;

            $nf['dest_ISUF'] = !empty($orcamento->cliente->isuf) ? $orcamento->cliente->isuf : null;
            $nf['dest_IM'] = !empty($orcamento->cliente->im) ? $orcamento->cliente->im : null;
            $nf['dest_email'] = !empty($orcamento->cliente->email) ? $orcamento->cliente->email : null;
            $nf['autXML_cpfcnpj'] = $nf['dest_cpf_cnpj'];
        }

        $nf['dest_indIEDest'] = $orcamento->cliente->indIEDest;
        if ($orcamento->cliente->indIEDest == 1 || $orcamento->cliente->indIEDest == 2)
            $nf['dest_IE'] = $orcamento->cliente->ie;
        else
            $nf['dest_IE'] = null;
        $nf['consumidor_nome'] = !empty($consumidor_nome) ? Helper::sanitizeString($consumidor_nome) : null;
        $nf['consumidor_cpf'] = !empty($consumidor_cpf) ? Helper::sanitizeString($consumidor_cpf) : null;
        $nf['total_frete'] = $total_frete;
        $nf['total_seguro'] = $total_seguro;
        $nf['total_outro'] = $total_outros;
        $nf['total_desconto'] = $orcamento->desconto_valor;
        $nf['modalidade_frete'] = $modalidade_frete;
        $nf['user_idCreated'] = auth()->id();
        $nf['user_idUpdated'] = auth()->id();

        if (!empty($obs_fiscal_novo)) {
            $nf['obs_fiscal'] = $request->input('gerarxml_obs');
        } else {
            $obs_fiscal = ObsFiscal::where('empresa_id', $empresa_id)->first();
            if (!empty($obs_fiscal)) {
                $nf['obs_fiscal'] = $obs_fiscal->obs;
            } else {
                $nf['obs_fiscal'] = '';
            }
        }

        $nf['obs_orcamento'] = !empty($orcamento->observacao) ? $orcamento->observacao : null;


        //Gera todas as informações para enviar a API fiscal a respeito do frete
        $info_frete = [];
        $info_frete['nome_razao']  = '';
        $info_frete['endereco']  = '';
        $info_frete['municipio']  = '';
        $info_frete['uf']  = '';
        $info_frete['cnpj_cpf']  = '';
        $info_frete['ie']  = '';



        DB::beginTransaction();
        try {

            $nf_updated = false;
            if (empty($fiscal_nf)) {
                $nf_created = NFeNFCe::create($nf);
            } else {
                $nf_updated = $fiscal_nf->update($nf);
            }

            if (!empty($nf_created) || $nf_updated) {

                if ($nf_updated) {
                    $nf_created = NFeNFCe::find($fiscal_nf->id);
                }

                // MARCA O ORCAMENTO COMO XML GERADO
                $orcamento->user_idXMLGerado = auth()->id();
                $orcamento->dh_xml_gerado = date('Y-m-d H:i:s');
                $orcamento->is_xml_gerado = 1;
                $orcamento->save();

                // GERAR OS ITENS
                $uf_emi = $orcamento->empresa->cidade->uf;

                // DELETA OS ITENS
                NFeNFCeItens::where('nf_id', $nf_created->id)->delete();


                foreach ($orcamento_itens as $item) {

                    $produto = Produto::find($item->produto_id);

                    $ibptax = IBPTax::where('uf', $uf_emi)
                        ->where('ncm', $produto->ncm)
                        ->first();

                    $nf_item = [];
                    $nf_item['nf_id'] = $nf_created->id;
                    $nf_item['produto_id'] = $produto->id;
                    $nf_item['nItem'] = $item->sequencial;
                    $nf_item['cProd'] = $produto->id;
                    if (empty($produto->cEAN)) {
                        $nf_item['cEAN'] = 'SEM GTIN';
                    } else {
                        if (GTIN::validate($produto->cEAN))
                            $nf_item['cEAN'] = $produto->cEAN;
                        else
                            $nf_item['cEAN'] = 'SEM GTIN';
                    }
                    $nf_item['xProd'] = Helper::sanitizeString($produto->descricao);
                    $nf_item['NCM'] = $produto->ncm;
                    //$nf_item['NVE'] = '';
                    //$nf_item['EXTIPI'] = '';
                    if ($ide_idDest == 1)
                        $nf_item['CFOP'] = $perfil->cfop_fora_uf;
                    else
                        $nf_item['CFOP'] = $perfil->cfop_dentro_uf;
                    $nf_item['uCom'] = $produto->unidadeComercial->sigla;
                    $nf_item['qCom'] = $item->quantidade;
                    $nf_item['vUnCom'] = $item->preco;
                    $nf_item['vProd'] = number_format($item->quantidade * $item->preco, 2, '.', '');
                    if (empty($produto->cEANTrib)) {
                        if (!empty($produto->cEAN)) {
                            if (GTIN::validate($produto->cEAN))
                                $nf_item['cEANTrib'] = $produto->cEAN;
                            else
                                $nf_item['cEANTrib'] = null;
                        } else {
                            $nf_item['cEANTrib'] = null;
                        }
                    } else {
                        if (GTIN::validate($produto->cEANTrib))
                            $nf_item['cEANTrib'] = $produto->cEANTrib;
                        else
                            $nf_item['cEANTrib'] = null;
                    }
                    $nf_item['uTrib'] = $produto->unidadeTributada->sigla;
                    $nf_item['qTrib'] = number_format($item->quantidade * $produto->qTrib, 2, '.', '');
                    $nf_item['vUnTrib'] = $nf_item['vProd'] / $nf_item['qTrib'];

                    $nf_item['vDesc'] = $item->desconto;

                    $nf_item['vFrete'] = number_format($total_frete / $orcamento_itens_quant, 2, '.', '');
                    $nf_item['vSeg'] = number_format($total_seguro / $orcamento_itens_quant, 2, '.', '');
                    $nf_item['vOutro'] = number_format($total_outros / $orcamento_itens_quant, 2, '.', '');

                    $nf_item['indTot'] = 1;
                    $nf_item['orig'] = $perfil->st_origem_id;
                    $nf_item['cst_id'] = $perfil->st_icms_id;
                    $nf_item['csosn_id'] = $perfil->csosn_id;
                    $nf_item['pCredSN'] = empty($perfil->aliq_cred_sn) ? 0 : $perfil->aliq_cred_sn;
                    $nf_item['modBCST'] = empty($perfil->modbcst_id) ? 0 : $perfil->modbcst_id;
                    $nf_item['pMVAST'] = empty($perfil->aliq_mvast) ? 0 : $perfil->aliq_mvast;
                    $nf_item['pRedBCST'] = empty($perfil->aliq_red_bc_st) ? 0 : $perfil->aliq_red_bc_st;
                    $nf_item['pICMSST'] = empty($perfil->aliq_icms_st) ? 0 : $perfil->aliq_icms_st;
                    $nf_item['modBC'] = empty($perfil->modbc_id) ? 0 : $perfil->modbc_id;
                    $nf_item['pRedBC'] = empty($perfil->aliq_red_bc) ? 0 : $perfil->aliq_red_bc;
                    $nf_item['pICMS'] = empty($perfil->aliq_icms) ? 0 : $perfil->aliq_icms;

                    if (!empty($ibptax)) {
                        $nf_item['pTribEst'] = $ibptax->aliq_estadual;
                        $nf_item['pTribFed'] = $ibptax->aliq_nacional;
                    } else {
                        $nf_item['pTribEst'] = 0;
                        $nf_item['pTribFed'] = 0;
                    }

                    $nf_item['cst_pis'] = $perfil->st_pis_id;
                    $nf_item['cst_cofins'] = $perfil->st_cofins_id;
                    $nf_item['pPIS'] = $perfil->aliq_pis;
                    $nf_item['pCOFINS'] = $perfil->aliq_cofins;

                    $nf_item['comb_cod_anp'] = $produto->comb_cod_anp;
                    $nf_item['comb_desc_anp'] = $produto->comb_desc_anp;
                    $nf_item['comb_uf_anp'] = $produto->comb_uf_anp;
                    $nf_item['comb_perc_glp'] = $produto->comb_perc_glp;
                    $nf_item['comb_perc_gnn'] = $produto->comb_perc_gnn;

                    try {

                        $item_created = NFeNFCeItens::create($nf_item);

                        if (empty($item_created)) {
                            DB::rollBack();
                            break;
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        break;
                    }
                }

                DB::commit();

                //--------------------------------
                // ENVIA PARA A API GERAR O XML
                //--------------------------------
                $param_fiscal = ParametroFiscal::where('empresa_id', $empresa_id)->first();
                $nf_itens = NFeNFCeItens::where('nf_id', $nf_created->id)->get();
                $orc_parcelas = OrcamentoParcela::where('orcamento_id', $orcamento_id)->get();
                if (!empty($param_fiscal)) {

                    $content = [
                        'hashCert'     => $param_fiscal->cert_hash,
                        'passCert'     => $param_fiscal->cert_pass,
                        'idCsc'        => $param_fiscal->IdCSC,
                        'csc'          => $param_fiscal->CSC,
                        'nf'           => $nf_created,
                        'info_frete'   => $info_frete,
                        'nf_itens'     => $nf_itens,
                        'orc_parcelas' => $orc_parcelas,
                        'tef_rede'     => $request->input('tef_rede', ''),
                    ];

                    $curl = ApiFiscal::getCurl(
                        '/fiscal/gerar_nota',
                        'POST',
                        $content
                    );

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $response = json_decode($response, true);

                    if ($response['status'] == 'OK') {

                        // ATUALIZA TRANSMITIDO
                        $row = [];
                        $row['chave'] = $response['chave'];
                        $row['cStat'] = $response['cStat'];
                        $row['nRec'] = (trim($response['nRec']) != '') ? $response['nRec'] : null;

                        $row['prot_cStat'] = $response['prot_cStat'];
                        $row['prot_nProt'] = $response['prot_nProt'];
                        $row['prot_xMotivo'] = $response['prot_xMotivo'];

                        $row['is_transmitido'] = intval($response['bEnviado']);

                        if (intval($row['cStat']) == 100 || $row['prot_cStat'] == 100)
                            $row['is_autorizado'] = 1;

                        $updated = $nf_created->update($row);

                        if ($updated) {

                            $atualiza_valor = OrcamentoFinalizacao::where('orcamento_id', $orcamento_id)->first();

                            if ($atualiza_valor->forma_pagamento == 'SEM') {

                                $atualizar_final_campo = Orcamento::where('id', $orcamento_id)->first();

                                $atualizar_final_campo->update([
                                    'total_liquido' => 0
                                ]);
                            }

                            return response()->json([
                                'status' => 'OK',
                                'nf_id' => $nf_created->id
                            ]);
                        } else {
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Houve algum problema ao atualizar com o retorno da nota fiscal.'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => 'NOK',
                            'data' => $response['message']
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 'NOK',
                        'data' => 'Acesse parametros->fiscal'
                    ]);
                }

                return response()->json([
                    'status' => 'OK',
                    'data' => 'XML Gerado com sucesso.'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'XML da nota fiscal não foi gerado'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'NOK',
                'data' => $e->getMessage()
            ]);
        }
    }

    public function isAVista(int $orcamento_id)
    {
        $parcela = OrcamentoFinalizacao::where('orcamento_id', $orcamento_id)
            ->first();

        if (!empty($parcela)) {

            $condicao = explode('/', $parcela->condicao_pagamento);

            if (count($condicao) == 1) {
                if ($condicao[0] == '0') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return null;
        }
    }


    public function store(Request $request)
    {
        $fields = $request->all();

        $validator = Validator::make($fields, [
            'condicao_pagamento' => 'required',
            'forma_pagamento' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'NOK',
                'message' => $validator->errors()
            ]);
        }

        try {

            DB::beginTransaction();

            $orcamento_id = intval($fields['orcamento_id']);
            $condicao_pagamento = trim($fields['condicao_pagamento']);
            $forma_pagamento = trim($fields['forma_pagamento']);
            $parcelamento_cliente = trim($fields['parcelamento_cliente']);
            $desconto_valor = str_replace(['.', ','], ['', '.'], $fields['desconto_valor']);
            $desconto_porc = str_replace(['.', ','], ['', '.'], $fields['desconto_porc']);
            $acrescimo_valor = str_replace(['.', ','], ['', '.'], $fields['acrescimo_valor']);
            $acrescimo_porc = str_replace(['.', ','], ['', '.'], $fields['acrescimo_porc']);
            $pre_forma = str_replace(['.', ','], ['', '.'], $fields['pre_forma']);
            //$valor_entrada = str_replace(['.', ','], ['', '.'], $fields['valor_entrada']);
            $centavo_ultima_parcela = intval($fields['centavo_ultima_parcela']);

            $condicao_pagamento = $this->corrigirEscritaCondPgto($condicao_pagamento);
            $forma_pagamento = $this->corrigirEscritaFormaPgto($forma_pagamento);
            $parcelamento_cliente = $this->corrigirEscritaParcelas($parcelamento_cliente);

            // VALIDA QUANTIDADES ENTRE CONDICOES E FORMAS
            $totCondicao = count(explode('/', $condicao_pagamento));
            $totFormas = count(explode('/', $forma_pagamento));
            if ($totCondicao !== $totFormas) {
                return response()->json([
                    'status' => 'NOK',
                    'message' => '<p class="bold m-0 font-size-16 color-red">TOTAL DE FORMAS DE PAGAMENTO DIFERENTE DE TOTAL DE CONDIÇÃO DE PGTO</p>' .
                        '<p class="font-size-12 m-0">Verifique novamente e faça a correção</p>'
                ]);
            }

            /* // VALIDA SE CONDICOES ESTA COM NUMEROS CRESCENTES
            $arrCondicoes = explode('/', $condicao_pagamento);
            $error = false;
            if (count($arrCondicoes) > 1) {
                $itemAnt = $arrCondicoes[0];
                for ($i = 1; $i <= count($arrCondicoes) - 1; $i++) {
                    if ($arrCondicoes[$i] < $itemAnt) {
                        $error = true;
                        break;
                    } else {
                        $itemAnt = $arrCondicoes[$i];
                    }
                }
            }
            if ($error) {
                return response()->json([
                    'status' => 'NOK',
                    'message' => '<p class="bold m-0 font-size-16 color-red">CONDIÇÕES DE PAGAMENTO INCORRETO</p>' .
                                 '<p class="font-size-12 m-0">Existe sequência de dias menor que a anterior</p>'
                ]);
            }*/

            // VALIDA SE FORMAS DE PGTO DIGITADAS ESTAO CORRETAS, CORRESPONDEM COM AS CADASTRADAS
            $arrFormas = explode('/', $forma_pagamento);
            $error = false;
            for ($i = 0; $i <= count($arrFormas) - 1; $i++) {
                $forma = FormaPagamento::where('id', $arrFormas[$i])->first();
                if (empty($forma)) {
                    $error = true;
                    break;
                }
            }
            if ($error) {
                return response()->json([
                    'status' => 'NOK',
                    'message' => '<p class="bold m-0 font-size-16 color-red">ALGUMA FORMA DE PAGAMENTO ESTÁ INCORRETO</p>' .
                        '<p class="font-size-12 m-0">Verifique novamente</p>'
                ]);
            }

            // ATUALIZA ORCAMENTO
            $orcamento = Orcamento::find($orcamento_id);

            // valida se entrada é maior que total liquido
            //            $valor_entrada = empty($valor_entrada) ? 0 : $valor_entrada;
            //            if (doubleval($valor_entrada) >= doubleval($orcamento->total_liquido)){
            //                return response()->json([
            //                    'status' => 'NOK',
            //                    'message' => '<p class="bold m-0 font-size-16 color-red">VALOR DE ENTRADA INVÁLIDO</p>' .
            //                        '<p class="font-size-12 m-0">Verifique se o valor de entrada é maior que o total.</p>'
            //                ]);
            //            }

            if (!empty($orcamento)) {

                $desconto_valor = empty($desconto_valor) ? 0 : $desconto_valor;
                $desconto_porc = empty($desconto_porc) ? 0 : $desconto_porc;

                $orcamento->desconto_valor = $desconto_valor;
                $orcamento->desconto_porc = $desconto_porc;
                $orcamento->total_bruto = $orcamento->total_bruto + $acrescimo_valor;
                $orcamento->total_liquido = $orcamento->total_bruto - $desconto_valor;
                $orcamento->update();

                // VALIDA TOTAL DE PARCELAS
                /* $totalParcelas = 0;
                foreach($fields["orcamento_parcelas"] as $parcela) {
                    $totalParcelas+=$parcela['valor'];
                }
                if (number_format($orcamento->total_liquido,2,'.','')
                    != number_format($totalParcelas,2,'.','')) {
                    return Helper::msg_nok(
                        'TOTAL DAS PARCELAS DIFERENTE DO TOTAL LIQUIDO',
                        'Verifique os parcelamentos do total de R$ ' . $totalParcelas
                    );
                }*/


                // RATEIA DESCONTO NOS ITENS DO ORCAMENTO
                $orcamento_itens = OrcamentoItens::where('orcamento_id', $orcamento->id)
                    ->get();

                $i = 1;
                $len = count($orcamento_itens);

                $vDesc = number_format($desconto_valor / $len, 2, '.', '');
                $vDescExcept = 0;
                if (($orcamento->total_liquido + ($len * $vDesc)) > $orcamento->total_bruto)
                    $vDescLast = $vDesc - (($orcamento->total_liquido + ($len * $vDesc)) - $orcamento->total_bruto);
                else if (($orcamento->total_liquido + ($len * $vDesc)) < $orcamento->total_bruto)
                    $vDescLast = $vDesc + ($orcamento->total_bruto - ($orcamento->total_liquido + ($len * $vDesc)));
                else
                    $vDescLast = $vDesc;

                foreach ($orcamento_itens as $item) {
                    if ($i == $len) {
                        // last
                        if ($vDescLast < $item->subtotal) {
                            $item->desconto = $vDescLast;
                            $item->subtotal = $item->subtotal - $vDescLast;
                        } else {
                            $vDescExcept += $vDescLast;
                        }
                    } else {
                        if ($vDesc < $item->subtotal) {
                            $item->desconto = $vDesc;
                            $item->subtotal = $item->subtotal - $vDesc;
                        } else {
                            $vDescExcept += $vDesc;
                        }
                    }
                    $item->save();
                    $i++;
                }

                if ($vDescExcept > 0) {
                    // verificar qual item tem o preco maior
                    $subTotalBig = 0;
                    $idBig = 0;
                    foreach ($orcamento_itens as $item) {
                        if ($item->subtotal > $subTotalBig) {
                            $subTotalBig = $item->subtotal;
                            $idBig = $item->id;
                        }
                    }

                    foreach ($orcamento_itens as $item) {
                        if ($item->id == $idBig) {
                            $item->desconto += $vDescExcept;
                            $item->subtotal -= $vDescExcept;
                            $item->save();
                        }
                    }
                }
            } else {
                return response()->json([
                    'status' => 'NOK',
                    'message' => 'Orçamento não encontrado.'
                ]);
            }

            // ===================================
            //  VALIDA SE TEM PARCELAS NEGATIVAS
            // ===================================
            /* $existNegativo = false;
            foreach($fields['orcamento_parcelas'] as $parcela) {
                if (doubleval($parcela["valor"]) <= 0){
                    $existNegativo = true;
                    break;
                }
            }
            if ($existNegativo){
                return Helper::msg_nok(
                    'Existe valor de parcela negativo ou menos que zero.',
                    'Verifique as parcelas'
                );
            }*/

            // GRAVA DADOS DE FINALIZACAO
            $finalizacao = OrcamentoFinalizacao::where('orcamento_id', $orcamento->id)->first();
            if (empty($finalizacao)) {

                //                $created = OrcamentoFinalizacao::create([
                //                    'orcamento_id' => $orcamento_id,
                //                    'condicao_pagamento' => $condicao_pagamento,
                //                    'forma_pagamento' => $forma_pagamento,
                //                    'centavo_ultima_parcela' => $centavo_ultima_parcela,
                //                    'valor_entrada' => $valor_entrada
                //                ]);
                $created = OrcamentoFinalizacao::create([
                    'orcamento_id' => $orcamento_id,
                    'condicao_pagamento' => $condicao_pagamento,
                    'forma_pagamento' => $forma_pagamento,
                    'centavo_ultima_parcela' => $centavo_ultima_parcela
                ]);

                if (!empty($created)) {

                    // GERAR AS PARCELAS
                    try {
                        $this->store_parcelas($orcamento->id, $fields['orcamento_parcelas']);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'NOK',
                            'message' => $e->getMessage()
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'status' => 'OK',
                        'message' => 'Orçamento ' . $orcamento->id . ' concluído com sucesso.'
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'NOK',
                        'message' => 'Houve algum problema ao tentar concluir, finalização não cadastrada.'
                    ]);
                }
            } else {

                $finalizacao->condicao_pagamento = $condicao_pagamento;
                $finalizacao->forma_pagamento = $forma_pagamento;
                $finalizacao->centavo_ultima_parcela = $centavo_ultima_parcela;
                //$finalizacao->valor_entrada = $valor_entrada;
                $updated = $finalizacao->update();

                if ($updated) {

                    // GERAR AS PARCELAS
                    try {
                        $this->store_parcelas($orcamento->id, $fields['orcamento_parcelas']);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'NOK',
                            'message' => $e->getMessage()
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'status' => 'OK',
                        'message' => 'Orçamento ' . $orcamento->id . ' concluído com sucesso.'
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'NOK',
                        'message' => 'Houve algum problema ao tentar concluir.'
                    ]);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'EXCEPTION',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param $orcamento_id
     * @param $parcelas
     */

    public function store_faturar(Request $request)
    {

        $empresa_id = intval(\Illuminate\Support\Facades\Request::session()->get('empresa')['id']);
        $user_id = intval(auth('web')->user()->id);

        $fields = $request->all();
        $orcamento_id = intval($fields['id_orcamento']);
        $tipo_movimento_id = intval($fields['tipo_movimento_id']);

        $tipo_movimento = DB::connection('db_client')
            ->table('tipos_movimentos')
            ->where('id', $tipo_movimento_id)
            ->first();

        if (empty($tipo_movimento))
            return response()->json([
                'status' => 'NOK',
                'data' => 'Tipo de movimento não encontrado'
            ]);

        $orcamento = Orcamento::where('empresa_id', $empresa_id)
            ->where('id', $orcamento_id)
            ->first();

        $caixa_abertura_id = CaixaAbertura::caixaAbertoId();

        $mov_estoque = false;
        $mov_financeiro = false;
        if (intval($tipo_movimento->movimenta_estoque) == 1)
            $mov_estoque = true;
        if (intval($tipo_movimento->movimenta_financeiro) == 1)
            $mov_financeiro = true;

        DB::beginTransaction();
        try {

            // GERAR FINANCEIRO
            if ($mov_financeiro) {

                $parcelas = OrcamentoParcela::where('orcamento_id', $orcamento->id)
                    ->get();

                if (count($parcelas) <= 0) {
                    return response()->json([
                        'status' => 'NOK',
                        'data' => 'Não foram definidas parcelas para este orçamento'
                    ]);
                }

                // FAZ OS LANCAMENTOS NO RECEITA
                $id_primeiro_lcto = 0;
                $next = 0;
                foreach ($parcelas as $parcela) {

                    $lcto = [];
                    $lcto['id_primeiro_lcto'] = 0;
                    $lcto['empresa_id'] = $empresa_id;
                    $lcto['pessoa_id'] = $orcamento->cliente_id;
                    $lcto['tipo'] = 'RECEITA';
                    $lcto['categoria'] = 'NORMAL';
                    $lcto['valor'] = number_format($parcela->valor, 2, '.', '');
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

                    if (!empty($receita)) {

                        if ($next == 0) {
                            $id_primeiro_lcto = $receita->id;
                            $next++;
                        }

                        $updated = $receita->update([
                            'id_primeiro_lcto' => $id_primeiro_lcto
                        ]);

                        if (!$updated) {
                            DB::rollBack();
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Houve um problema ao tentar atualizar o ID de contra partida'
                            ]);
                            break;
                        }
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'NOK',
                            'data' => 'Financeiro não foi gerado.'
                        ]);
                        break;
                    }

                    // SE TIVER ENTRADA OU FOR AVISTA, LIQUIDAR E LANCAR NO CAIXA
                    if (intval($parcela->avista) == 1) {

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
                                return response()->json([
                                    'status' => 'NOK',
                                    'data' => 'Houve um problema ao tentar atualziar o ID de contra partida da liquidação'
                                ]);
                                break;
                            }
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Problema ao tentar gerar o lançamento de liquidação'
                            ]);
                            break;
                        }

                        // ATUALIZA O LANCAMENTO ORIGINAL COMO LIQUIDADO
                        $updated = $receita->update([
                            'saldo' => 0,
                            'user_idRecPag' => $user_id,
                            'dh_RecPag' => date('Y-m-d H:i:s')
                        ]);
                        if (!$updated) {
                            DB::rollBack();
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Problema ao tentar atualizar o lançamento origem'
                            ]);
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
                        if (empty($movimento_created)) {
                            DB::rollBack();
                            return response()->json([
                                'status' => 'NOK',
                                'data' => 'Problema ao tentar lançar no caixa'
                            ]);
                            break;
                        }
                    }
                }
            }

            // BAIXA ESTOQUE
            if ($mov_estoque) {
                $itens = OrcamentoItens::where('orcamento_id', $orcamento->id)->get();
                if (count($itens) > 0) {
                    foreach ($itens as $item) {
                        $produtoEstoque = ProdutoEstoque::where('empresa_id', $empresa_id)
                            ->where('produto_id', $item->produto_id)
                            ->first();
                        if (!empty($produtoEstoque)) {
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
                return response()->json([
                    'status' => 'NOK',
                    'data' => 'Houve algum problem ao tentar atualizar os valores do orçamento'
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'OK',
                'data' => 'Sua venda foi faturada com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::msg_exception($e->getMessage());
        }
    }

    private function store_parcelas($orcamento_id, $parcelas)
    {
        OrcamentoParcela::where('orcamento_id', $orcamento_id)
            ->delete();

        $parcelas = $this->normalizeParcelasValores($parcelas);

        foreach ($parcelas as $parcela) {
            OrcamentoParcela::create([
                'orcamento_id'      => $orcamento_id,
                'sequencial'        => $parcela['seq'],
                'valor'             => number_format($parcela['valor'], 2, '.', ''),
                'vencimento'        => $parcela['vcto'],
                'forma_pagamento_id'=> preg_replace('/[0-9]/', '', $parcela['forma']),
                'avista'            => (strtotime(date('Y-m-d')) == strtotime($parcela['vcto'])) ? 1 : 0,
                'cAut'              => $parcela['div']
            ]);
        }
    }

    private function normalizeParcelasValores(array $parcelas): array
    {
        $groups = [];
        foreach ($parcelas as $p) {
            $valor = str_replace(['.', ','], ['', '.'], $p['valor']);
            $valor = round((float) $valor, 2);
            $p['valor'] = $valor;
            $key = $p['forma'] . '#' . ($p['div'] ?? '');
            $groups[$key][] = $p;
        }

        $result = [];
        foreach ($groups as $group) {
            $totalCents = array_reduce($group, fn($s, $g) => $s + (int) round($g['valor'] * 100), 0);
            $count = count($group);
            $baseCents = intdiv($totalCents, $count);
            $somaCents = 0;

            foreach ($group as $idx => $g) {
                if ($idx === $count - 1) {
                    $valorCents = $totalCents - $somaCents;
                } else {
                    $valorCents = $baseCents;
                    $somaCents += $baseCents;
                }
                $g['valor'] = $valorCents / 100;
                $result[] = $g;
            }
        }

        usort($result, fn($a, $b) => ($a['seq'] ?? 0) <=> ($b['seq'] ?? 0));
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Venda\OrcamentoFinalizacao $orcamentoFinalizacao
     * @return \Illuminate\Http\Response
     */
    public function show(OrcamentoFinalizacao $orcamentoFinalizacao)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Venda\OrcamentoFinalizacao $orcamentoFinalizacao
     * @return \Illuminate\Http\Response
     */
    public function edit(OrcamentoFinalizacao $orcamentoFinalizacao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Venda\OrcamentoFinalizacao $orcamentoFinalizacao
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrcamentoFinalizacao $orcamentoFinalizacao)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Venda\OrcamentoFinalizacao $orcamentoFinalizacao
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrcamentoFinalizacao $orcamentoFinalizacao)
    {
        //
    }

    /**
     * @param $condicao_pagamento
     * @return bool|mixed|string
     */
    private function corrigirEscritaCondPgto($condicao_pagamento)
    {
        $condicao_pagamento = str_replace('//', '/', $condicao_pagamento);
        $first = substr($condicao_pagamento, 0, 1);
        $last = substr($condicao_pagamento, strlen($condicao_pagamento) - 1, 1);
        if (!is_numeric($first))
            $condicao_pagamento = substr($condicao_pagamento, 1, strlen($condicao_pagamento));
        if (!is_numeric($last))
            $condicao_pagamento = substr($condicao_pagamento, 0, strlen($condicao_pagamento) - 1);
        return $condicao_pagamento;
    }

    /**
     * @param $forma_pagamento
     * @return string
     */
    private function corrigirEscritaFormaPgto($forma_pagamento)
    {
        $forma_pagamento = str_replace('//', '/', $forma_pagamento);
        $first = substr($forma_pagamento, 0, 1);
        $last = substr($forma_pagamento, strlen($forma_pagamento) - 1, 1);
        if ($first === '/')
            $forma_pagamento = substr($forma_pagamento, 1, strlen($forma_pagamento));
        if ($last === '/')
            $forma_pagamento = substr($forma_pagamento, 0, strlen($forma_pagamento) - 1);
        return strtoupper($forma_pagamento);
    }

    private function corrigirEscritaParcelas($parcelamento_cliente)
    {
        $parcelamento_cliente = str_replace('//', '/', $parcelamento_cliente);
        $first = substr($parcelamento_cliente, 0, 1);
        $last = substr($parcelamento_cliente, strlen($parcelamento_cliente) - 1, 1);
        if (!is_numeric($first))
            $parcelamento_cliente = substr($parcelamento_cliente, 1, strlen($parcelamento_cliente));
        if (!is_numeric($last))
            $parcelamento_cliente = substr($parcelamento_cliente, 0, strlen($parcelamento_cliente) - 1);
        return $parcelamento_cliente;
    }
}
