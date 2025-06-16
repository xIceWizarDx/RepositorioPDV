<?php

namespace App\Http\Controllers\Cadastro\TabelaAuxiliar;

use App\Models\Cadastro\Pessoa;
use App\Models\Cadastro\TabelaAuxiliar\Banco;
use App\Models\Cadastro\TabelaAuxiliar\ModoPagar;
use App\Models\Cadastro\TabelaAuxiliar\FormaPagamento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TiposPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
           'status' => 'OK',
           'data' => view('cadastro.tabela_auxiliar.tipos_pagamento.index')->render()
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function table()
    {
        try {

            $pagamentos = DB::connection('db_client')->table('formas_pagamentos')
                ->orderBy('sequencial','asc')
                ->orderBy('is_active', 'desc')
                ->get();

            return view('cadastro.tabela_auxiliar.tipos_pagamento.table')
                ->with('pagamentos', $pagamentos);

        } catch (\Exception $e) {
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
        return response()->json([
            'status' => 'OK',
            'data' => view('cadastro.tabela_auxiliar.tipos_pagamento.create')->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function show(FormaPagamento $pagamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cadastro\TabelaAuxiliar\Banco  $banco
     * @return \Illuminate\Http\Response
     */
    public function edit(FormaPagamento $pagamento)
    {
        try {

            $mod_pag = ModoPagar::where('descricao_id', $pagamento->id)->first();

            $mod_pag_info = ModoPagar::where('descricao_id', $pagamento->id)->first();

            $pagamento = FormaPagamento::find($pagamento->id);

            if (!empty($pagamento)) {
                if(empty($mod_pag)){
                $mod_pag = 0;
                return response()->json([
                    'status' => 'OK',
                    'data' => view('cadastro.tabela_auxiliar.tipos_pagamento.edit')
                        ->with('pagamento', $pagamento)
                        ->with('mod_pag', $mod_pag)
                        ->render()
                ]);
               }else if(!empty($mod_pag)){
                $mod_pag = $mod_pag->count();
                return response()->json([
                    'status' => 'OK',
                    'data' => view('cadastro.tabela_auxiliar.tipos_pagamento.edit')
                        ->with('pagamento', $pagamento)
                        ->with('mod_pag', $mod_pag)
                        ->with('mod_pag_info', $mod_pag_info)
                        ->render()
                ]);
               }
            } else {
                return response()->json([
                    'status' => 'EXCEPTION',
                    'data' => 'Pagamento não encontrado!'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'EXCEPTION',
                'data' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cadastro\TabelaAuxiliar\Banco  $banco
     * @return \Illuminate\Http\Response
     */

    public function excluir(Request $request, FormaPagamento $pagamento)
    {
        $fields = $request->all();

        $pagamento = FormaPagamento::find($pagamento->id);

        try {

            if(!empty($pagamento)){

                $apagar = FormaPagamento::where('id', $pagamento->id)->first();

                $apagar->delete();
                

                if ($apagar) {
                    return response()->json([
                        'status' => 'OK',
                        'data' => 'Forma de pagamento excluida com sucesso.'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'NOK',
                        'data' => 'Não conseguimos apagar sua forma de pagamento.'
                    ]);
                }

            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'EXCEPTION',
                'data' => 'EXCLUA TODAS AS CONDIÇÕES DESTA FORMA DE PAGAMENTO, PARA EXECUTAR ESTA TAREFA.'
            ]);
        }
    }

    public function update(Request $request, FormaPagamento $pagamento)
    {
        $fields = $request->all();

        if (isset($fields['is_active'])) {
            $fields['is_active'] = intval(boolval($fields['is_active']));
        } else {
            $fields['is_active'] = 0;
        }

        if (isset($fields['livre_pag'])) {
            $fields['livre_pag'] = intval(boolval($fields['livre_pag']));
        } else {
            $fields['livre_pag'] = 0;
        }

        if (isset($_POST['mod_vai_pagar'])){
            $_POST['mod_vai_pagar'] = $_POST['mod_vai_pagar'];
        } else {
            $_POST['mod_vai_pagar'] = '';
        } 


        $pagamento = FormaPagamento::find($pagamento->id);

        try {

                $inputeste = $_POST['mod_vai_pagar'];

                if (!empty($inputeste)) { 
                    $inputeste = $_POST['mod_vai_pagar'];               
                    $qtd = count($inputeste);
                    $verificacao_qtd = ModoPagar::where('descricao_id',  $pagamento->id)->first();

                    if (empty($verificacao_qtd)) {
                        $qtd_ve = 0;
                    } else {
                        $qtd_ve = $verificacao_qtd->count();

                    } 

                    //Testa para saber se a quantidade gravada no banco de dados é igual a quantidade de input que foram enviadas
                    if($qtd != $qtd_ve){

                        $verificacao_qtd = ModoPagar::where('descricao_id', '=', $pagamento->id)->firstWhere('sequencia', '>=', $qtd);
                    for ($i = 0; $i < $qtd; $i++) {

                        $verificacao_qtd = ModoPagar::where('descricao_id', '=', $pagamento->id)->delete();

                    }
                    }
                    
                    for ($i = 0; $i < $qtd; $i++) {

                      if ($inputeste[$i]!=""){

                        if(!is_numeric($inputeste[$i])){

                            return response()->json([
                                'status' => 'EXCEPTION',
                                'data' => 'Alguma forma de pagamento esta sem os dias, ou com  caracteres diferentes de números.'
                            ]);
    
                          } 

                        $verificacao = ModoPagar::where('descricao_id', $pagamento->id)->where('sequencia', $i)->first();

                        if(empty($verificacao)){  
                        
                        $updated = ModoPagar::create([
                              'descricao_id' => $fields['id'],
                              'pag_mod' => $inputeste[$i],
                              'sequencia' => $i
                        ]);

                        $updated2 = $pagamento->update([
                            'id' => $fields['id'],
                            'descricao' => $fields['descricao'],
                            'is_active' => $fields['is_active'],
                            'livre_pag' => $fields['livre_pag']
                        ]);

                        }else if(!empty($verificacao)){

                        $updated = $verificacao->update([
                                'descricao_id' => $fields['id'],
                                'pag_mod' => $inputeste[$i],
                                'sequencia' => $i
                        ]);  

                        $updated2 = $pagamento->update([
                            'id' => $fields['id'],
                            'descricao' => $fields['descricao'],
                            'is_active' => $fields['is_active'],
                            'livre_pag' => $fields['livre_pag']
                        ]);
                    
                        }
                    
                    }
                        }

                    if ($updated && $updated2) {
                        return response()->json([
                            'status' => 'OK',
                            'data' => 'Forma de pagamento atualizado com sucesso.'
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'EXCEPTION',
                            'data' => 'Ocorreu algum erro.'
                        ]);
                    }

                }

                if (empty($inputeste)) {

                    $verificacao_qtd = ModoPagar::where('descricao_id', '=', $pagamento->id)->delete();
                    
                    $updated = $pagamento->update([
                        'id' => $fields['id'],
                        'descricao' => $fields['descricao'],
                        'is_active' => $fields['is_active'],
                        'livre_pag' => $fields['livre_pag']
                    ]);
                        
                    if ($updated) {
                        return response()->json([
                            'status' => 'OK',
                            'data' => 'Forma de pagamento atualizado com sucesso.'
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'EXCEPTION',
                            'data' => 'Ocorreu algum error.'
                        ]);
                    }


                }
 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'EXCEPTION',
                'data' => 'VOCÊ DEIXOU ALGUM CAMPO OBRIGATÓRIO EM BRANCO'
            ]);
        }
        
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cadastro\TabelaAuxiliar\Banco  $banco
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormaPagamento $pagamento)
    {
        //
    }

    public function criar_form(Request $request)
    {

        $fields = $request->all();

        if (isset($fields['is_active'])) {
            $fields['is_active'] = intval(boolval($fields['is_active']));
        } else {
            $fields['is_active'] = 0;
        }

        if (isset($fields['livre_pag'])) {
            $fields['livre_pag'] = intval(boolval($fields['livre_pag']));
        } else {
            $fields['livre_pag'] = 0;
        }

        $validator = Validator::make($fields, [
            'forma_pagamento_id' => 'required',
            'descricao' => 'required|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'NOK',
                'data' => $validator->errors()
            ]);
        }

        // VALIDA SE NÃO TEM NENHUMA OUTRA FORMA DE PAGAMENTO COM O NOME IGUAL
        $arrFormas = $fields['descricao'];
        $error = false;
        $forma_teste = FormaPagamento::where('descricao', $arrFormas)->get();
        $forma_final = count($forma_teste);
        for ($i = 0; $i <= $forma_final - 1; $i++) {
            $forma = FormaPagamento::where('descricao', $arrFormas[$i])->first();
            if (empty($forma)) {
                return response()->json([
                    'status' => 'EXCEPTION',
                    'data' => 'Você possui uma forma de pagamento com o mesmo nome, por favor tente outro!'
                ]);
            }
        }

        $forma_pag_qual = FormaPagamento::find($fields['forma_pagamento_id']);

        $todos = FormaPagamento::get();

        //$nome = FormaPagamento::where('id', $forma_pag_qual->id)->get();

        $nome = DB::connection('db_client')
            ->table('formas_pagamentos')
            ->select([
                '*'  
            ])
            ->whereRaw('id LIKE ?', ["$forma_pag_qual->id%"])
            ->get();

        $nome_conta = count($nome);

        $nome_novo = intval($nome_conta + 1);

        $counta = count($todos);

        try {
            $ret = FormaPagamento::create([
                'id' => $forma_pag_qual->id.' '.$nome_novo,   
                'descricao' => $fields['descricao'],
                'sequencial' => intval($counta + 1) ,
                'idNFCe' => $forma_pag_qual->idNFCe,
                'is_active' => $fields['is_active'],
                'livre_pag' => $fields['livre_pag']
            ]);

            if (!empty($ret)) {
                return response()->json([
                    'status' => 'OK',
                    'data' => 'Cadastro realizado com sucesso.'
                ]);
            } else {
                return response()->json([
                    'status' => 'EXCEPTION',
                    'data' => 'Ocorreu algum error.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'EXCEPTION',
                'data' => $e->getMessage()
            ]);
        }

    }

    private function updatepag(Request $request)
    {
       

    }

    
}
