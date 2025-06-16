<div class="ui segment m-b-2" style="box-shadow: unset" id="orcamentoFinalizarteste">
    <h3 class="color-red">
         <i class="far fa-cash-register"></i>&nbsp;FINALIZAR ORÇAMENTO
         <span id="teste"></span>
    </h3>
</div>

<style type="text/css">

</style>

<div id="orcamentoFinalizar" class="ui segment m-t-0 p-b-0 m-b-0" data-tab="first" style="background-color: #F6F6F6; border: 1px solid #C5C5C5; box-shadow: unset;">

    <div class="ui grid  p-b-0 m-b-0">
    @if(\App\Helpers\Helper::isMobile())
        <div>
        <form class="ui form" id="formOrcamentoFinalizarCreate">

        @csrf
    @endif
    <div class="ten wide column p-b-0">
        
        <div class="six wide column p-3" style="border-left: 1px solid #d6d7d7;">
        
            <form class="ui form" id="formOrcamentoFinalizarCreate">

                @csrf
    
                <div class="fields">
                
                    <div class="sixteen wide field required forma_pagamento_cod">
                        <label class="label-customized mb-1" for="forma_pagamentoFinalizar">Forma de Pagamento</label>
                        <div class="ui input action small">
                            <input type="text"
                                   value=""
                                   class="color-black bold input-valid"
                                   name="forma_pagamento_cod"
                                   id="forma_pagamento_cod"
                                   autocomplete="off"
                                   style="text-transform: uppercase;" style="height: 39px;" placeholder="Código + <enter>" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                            <button type="button" id="btnLimparFormaPgt" class="ui button mini basic tooltip_custom"
                                    data-content="Limpar campo"
                                    data-variation="inverted mini"
                                    data-position="top left">
                                <i class="fas fa-eraser"></i>
                            </button>
                    </div>  
                    <br></br>

                @if(!\App\Helpers\Helper::isMobile())
                <div id="tableTiposPagamentos" style="height: 570px; overflow-y: scroll;">
                @else
                <div id="tableTiposPagamentos" @if(count($formas_pagamentos) <= 6) style="height: auto; overflow-y: scroll;" @else  style="height: 410px; overflow-y: scroll;" @endif>
                @endif
                
                @if(\App\Helpers\Helper::isMobile())
                <table class="ui compact celled table" style="table-layout: auto;">
                    <tbody>

                        @if(count($formas_pagamentos) > 0)
                            @foreach($formas_pagamentos as $formas)
                            
                                <tr> 
                                    <td class="td-v-m td-center color-black-light" style="height: 40px;">
                                        <div class="ui basic button fpgto tooltip_custom m-b-0"
                                        data-content="{{ $formas->descricao }}"
                                        data-value="{{ $formas->id }}"
                                        data-variation="inverted mini"
                                        data-position="bottom left" id="{{ $formas->id }}">
                                        {!! $formas->font_icon !!} {{ $formas->descricao }}
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                        @endif

                    </tbody>

                </table>
                
                @else
                <table class="ui compact celled table">
                    <thead>
                        <tr>
                            <th>COD</th>
                            <th>TIPO</th>
                            <th>ID</th>
                            <th>C.P</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if(count($formas_pagamentos) > 0)
                            @foreach($formas_pagamentos as $formas)
                            
                                <tr>
                                    <td>{{ $formas->sequencial }}</td>
                                    <td>
                                    {{ $formas->descricao }}   
                                    </td>
                                    <td>
                                        <div class="ui basic button fpgto tooltip_custom m-b-7"
                                        data-content="{{ $formas->descricao }}"
                                        data-variation="inverted mini"
                                        data-position="bottom left" id="{{ $formas->id }}">
                                        {!! $formas->font_icon !!} {{ $formas->id }}
                                        </div>
                                    </td>
                                    @if($formas->livre_pag > 0)
                                    <td>✓</td>
                                    @else
                                    <td>X</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif

                    </tbody>

                </table>
                @endif
            </div>

                @if(\App\Helpers\Helper::isMobile())
               
                    <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                             <label class="label-customized mb-1" for="help"><b>Reiniciar a venda?</b></label>

                             <button type="button" id="btnLimparFormaPagamento" class="ui button  tooltip_custom"
                                    data-content="Reiniciar venda"
                                    data-variation="inverted mini"
                                    data-position="top center">
                                <i class="fas fa fa-history"></i>&nbsp;Reiniciar venda [F4]
                             </button>
                    </div>
                    </div>
                    </div>
                    </div>

                @endif

        </div>
        
        @if(\App\Helpers\Helper::isMobile())
        <div id="cond_mobile">
       @endif
       <div class="six wide column p-3" style="border-left: 1px solid #d6d7d7;" id="cond_mobile">
            <div class="fields"> 
                  
                    <div class="sixteen wide field required condicao_pagamento_cod">
                        <label class="label-customized mb-1" for="condicao_pagamentoFinalizar">Condição de Pagamento</label>
                        <div id="cp_nao_mod">
                        <div class="ui small action input">

                            <input type="text" class="color-black bold input-valid"
                                value=""
                                name="condicao_pagamento_cod"
                                id="condicao_pagamento_cod"
                                autocomplete="off" style="height: 39px;" placeholder="Ex: 1x = 1, 2x = 2, 3x = 3 ..." onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">

                            <button type="button" id="btnLimparCondicaoPagamentoCOD" class="ui button mini basic tooltip_custom"
                                data-content="Limpar campo"
                                data-variation="inverted mini"
                                data-position="top left">
                                <i class="fas fa-eraser"></i>
                            </button>
                        </div>
                        </div>

                        <p class="m-0 font-size-11 color-green" id="totParcelas"> 0 <span class="color-gray"> Meios de pagamento</span></p>                  
                        <div class="ui celled horizontal list color-teal" id="condicao_nao_modificada">

                        @if(!\App\Helpers\Helper::isMobile())
                            <div class="ui basic button condicao tooltip_custom m-b-10"
                                 data-content="Entrada / A Vista" data-variation="inverted mini"
                                 data-position="bottom left"
                                 data-value="0" id="pag0">
                                0
                            </div>

                           
                            <div class="ui button condicao tooltip_custom" data-value="1" id="pag1">1 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="2" id="pag2">2 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="3" id="pag3">3 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="4" id="pag4">4 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="5" id="pag5">5 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="6" id="pag6">6 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="7" id="pag7">7 x</div>
                            <div class="ui button condicao tooltip_custom m-b-10" data-value="8" id="pag8">8 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="9" id="pag9">9 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="10" id="pag10">10 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="11" id="pag11">11 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="12" id="pag12">12 x</div>

                        @else
                        <div class="ui basic button condicao tooltip_custom m-b-10"
                                 data-content="Entrada / A Vista" data-variation="inverted mini"
                                 data-position="bottom left"
                                 data-value="0" id="pag0">
                                0
                            </div>

                           
                            <div class="ui button condicao tooltip_custom" data-value="1" id="pag1">1 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="2" id="pag2">2 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="3" id="pag3">3 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="4" id="pag4">4 x</div>
                            <div class="ui button condicao tooltip_custom m-b-10" data-value="5" id="pag5">5 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="6" id="pag6">6 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="7" id="pag7">7 x</div>
                            <div class="ui button condicao tooltip_custom m-b-10" data-value="8" id="pag8">8 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="9" id="pag9">9 x</div>
                            <div class="ui button condicao tooltip_custom m-b-10" data-value="10" id="pag10">10 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="11" id="pag11">11 x</div>
                            <div class="ui button condicao tooltip_custom" data-value="12" id="pag12">12 x</div>
                        @endif
                    </div>

                        <br></br>
        
                @if(!\App\Helpers\Helper::isMobile())        
                <label class="label-customized mb-1" for="condicao_descontos_promocoes">Descontos/Promoções</label>
                <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                        @if(($param_finan) == 1)
                            <div class="five wide field disabled">
                                <label class="label-customized mb-0" for="">Desc&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" readonly id="desconto_valor" name="desconto_valor"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_valor, 2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div>
                            <div class="five wide field disabled">
                                <label class="label-customized mb-0" for="">Desc&nbsp;%</label>
                                <div class="ui input small">
                                    <input type="text" class="money" readonly id="desconto_porc" name="desconto_porc"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_porc,2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div> 
                            @else
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Desc&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="desconto_valor" name="desconto_valor"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_valor, 2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div>
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Desc&nbsp;%</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="desconto_porc" name="desconto_porc"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_porc,2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div> 
                        @endif
                    </div>
                </div>
                <label class="label-customized mb-1" for="condicao_acrescimos_taxas">Acréscimos/Taxas</label>
                <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Valor&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="acrescimo_valor" name="acrescimo_valor"
                                        value="0,00" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div>
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Valor&nbsp;%</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="acrescimo_porc" name="acrescimo_porc"
                                        value="0,00" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div>     
                             
                            <button type="button" class="ui basic button tooltip_custom"
                                    data-content="Lembre sempre de avisar o consumidor a respeito das taxas e acréscimos quando incluído na venda!"
                                    data-variation="inverted mini"
                                    data-position="top left">
                                <i class="fa fa-exclamation-triangle" style="color: #d14836;"></i>
                            </button>  
                    </div>
                </div>
                <label class="label-customized mb-1" for="preco_livre">Preço livre</label>
                <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Valor&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="preco_livre" name="preco_livre"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->total_bruto, 2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div>
                            <button type="button" class="ui basic button tooltip_custom"
                                    data-content="Só altere o valor da compra caso o produto seja de preço não definido, e sempre avise o valor ao consumidor!"
                                    data-variation="inverted mini"
                                    data-position="top left">
                                <i class="fa fa-exclamation-triangle" style="color: #d17936;"></i>
                            </button>
                    </div>
                </div>
                @endif

                    @if(!\App\Helpers\Helper::isMobile())
                    <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                             <label class="label-customized mb-1" for="help"><b>Reiniciar a venda?</b></label>

                             <button type="button" id="btnLimparFormaPagamento" class="ui button  tooltip_custom"
                                    data-content="Reiniciar venda"
                                    data-variation="inverted mini"
                                    data-position="top center">
                                <i class="fas fa fa-history"></i>&nbsp;Reiniciar venda [F4]
                             </button>
                    </div>
                    </div>
                    </div>
                    </div>
                    @endif

            </div>
            
        </div>
    </div>
    
            </form>
        </div>

        <div class="six wide column p-3" style="border-left: 1px solid #d6d7d7;">

        @if(\App\Helpers\Helper::isMobile())
        <label class="label-customized mb-1" for="condicao_descontos_promocoes">Descontos/Promoções</label>
                <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                        @if(($param_finan) == 1)
                            <div class="five wide field disabled">
                                <label class="label-customized mb-0" for="">Desc&nbsp;R$ {!!str_repeat("&nbsp;", 30)!!}Desc&nbsp;%</label>
                                <div class="ui input small">
                                    <input type="text" class="money" readonly id="desconto_valor" name="desconto_valor"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_valor, 2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                        <input type="text" class="money" readonly id="desconto_porc" name="desconto_porc"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_porc,2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                </div>
                            </div> 
                            @else
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Desc&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="desconto_valor" name="desconto_valor"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_valor, 2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">       
                                    <button type="button" id="btnAddDescValor" class="ui button mini  tooltip_custom"
                                            data-content="Adicionar"
                                            data-variation="inverted mini"
                                            data-position="top left">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    </div>
                            </div>
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Desc&nbsp;%</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="desconto_porc" name="desconto_porc"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->desconto_porc,2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">      
                                    <button type="button" id="btnAddDescPorc" class="ui button mini  tooltip_custom"
                                            data-content="Adicionar"
                                            data-variation="inverted mini"
                                            data-position="top left">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                 </div>
                            </div> 
                        @endif
                    </div>
                </div>
                <label class="label-customized mb-1" for="condicao_acrescimos_taxas">Acréscimos/Taxas</label>
                <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Valor&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="acrescimo_valor" name="acrescimo_valor"
                                        value="0,00" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                        <button type="button" id="btnAddPocValor" class="ui button mini  tooltip_custom"
                                            data-content="Adicionar"
                                            data-variation="inverted mini"
                                            data-position="top left">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Valor&nbsp;%</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="acrescimo_porc" name="acrescimo_porc"
                                        value="0,00" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                        <button type="button" id="btnAddPoc" class="ui button mini  tooltip_custom"
                                            data-content="Adicionar"
                                            data-variation="inverted mini"
                                            data-position="top left">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>       
                    </div>
                </div>
                <label class="label-customized mb-1" for="preco_livre">Preço livre</label>
                <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                            <div class="five wide field">
                                <label class="label-customized mb-0" for="">Valor&nbsp;R$</label>
                                <div class="ui input small">
                                    <input type="text" class="money" id="preco_livre" name="preco_livre"
                                        value="{{ (!empty($orcamento)) ? number_format($orcamento->total_bruto, 2,',','.') : '0,00' }}" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''">
                                       
                                        <button type="button" id="btnAddValorLivre" class="ui button mini  tooltip_custom"
                                            data-content="Adicionar"
                                            data-variation="inverted mini"
                                            data-position="top left">
                                        <i class="fa fa-plus"></i>
                                        </button>
                                
                                    </div>
                            </div>
                    </div>
                </div>
                @endif


            <div class="ui segment form m-l-0 m-t-2 m-r-3 m-b-2 p-b-0" style="box-shadow: unset;">
                    <div class="fields mb-0">
                        <div class="five wide field">
                            <label class="label-customized mb-0" for="">Valor&nbsp;R$</label>
                            <div class="ui input small">
                                <input type="text" class="money" id="valor_abater" name="valor_abater"
                                    value="" autocomplete="off" onFocus="this.style.backgroundColor='#FFFBDD'" onblur="this.style.backgroundColor=''" placeholder="R$">
                            @if(\App\Helpers\Helper::isMobile())        
                            <button type="button" id="btnAddValor" class="ui button mini  tooltip_custom"
                                    data-content="Adicionar"
                                    data-variation="inverted mini"
                                    data-position="top left">
                                <i class="fa fa-plus"></i>
                            </button>
                            @endif

                            </div>
                        </div>
                        <div class="five wide field">
                            <label class="label-customized mb-0" for="">Faltam&nbsp;R$</label>
                            <div class="ui input small">
                            @if(empty($parcelas->valor))   
                            <span class="color-red font-size-25 bold" style="float: right;" id="labelFaltamPagar">
                                {{  number_format($orcamento->total_liquido,2,',','.') }}
                                </span> 
                            @else
                            <span class="color-red font-size-25 bold" style="float: right;" id="labelFaltamPagar">
                                  0,00
                                </span>
                            @endif    
                            </div>
                        </div>
                        <div class="six wide field p-0">
                            <div class="m-t-10 p-0 font-size-9">
                                <span class="color-black-light">Tot&nbsp;Bruto</span>
                                <span class="color-black font-size-20 bold" style="float: right;" id="labelTotBruto">
                                    {{  number_format($orcamento->total_bruto,2,',','.') }}
                                </span>
                            </div>
                            <div class="m-t-10 p-0 font-size-9">
                                <span class="color-black-light">Tot&nbsp;Liq</span>
                                <span class="color-green font-size-20 bold" style="float:  right;" id="labelTotLiq">
                                    {{  number_format($orcamento->total_liquido,2,',','.') }}
                                </span>
                            </div>
                        </div>
                    </div>
            </div> 

            @if(!\App\Helpers\Helper::isMobile())
            <div id="tableParcelasGeradas" style="height: 570px; overflow-y: scroll;">
            @endif
            @if(\App\Helpers\Helper::isMobile())
            <div id="tableParcelasGeradas" style="height: auto; overflow-x: scroll;" class="table-responsive">
            @endif

                
                <table class="ui compact celled table">
                    <thead>
                        <tr>
                            <th>Seq</th>
                            <th>Valor&nbsp;R$</th>
                            <th>Vcto</th>
                            <th>Forma</th>
                            <th>C.P</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if(count($parcelas) > 0)
                            @foreach($parcelas as $parcela)
                                <tr>
                                    <td class="td-v-m td-center color-black-light">{{ $parcela->sequencial }}</td>
                                    <td>
                                        <div class="ui mini input" style="width: 75px;">
                                            @if($loop->last)
                                                <input type="text" class="inputValorParcela"
                                                    readonly
                                                    tabindex="-1"
                                                    onchange="onInputValorParcelaChange(this)"
                                                    value="{{ number_format($parcela->valor, 2, ',', '.') }}">
                                            @else
                                                <input type="text" class="inputValorParcela"
                                                    onchange="onInputValorParcelaChange(this)"
                                                    value="{{ number_format($parcela->valor, 2, ',', '.') }}">
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ui mini input" style="width: 85px;">
                                            <input type="text" class="inputVctoParcela"
                                                onchange="onInputVctoParcelaChange(this)"
                                                value="{{ \App\Helpers\Helper::date2Br($parcela->vencimento) }}">
                                        </div>
                                    </td>
                                    <td>{{ $parcela->forma_pagamento_id }}</td>
                                    <td>{{ $parcela->cAut }}</td>
                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                </table>
            
            </div>

            <div class="fields">
                    <div class="sixteen wide field required forma_pagamento">
                        <div class="ui input action small">
                            <input type="hidden"
                                   @if (!empty($finalizacao))
                                   value="{{ $finalizacao->forma_pagamento }}"
                                   @endif
                                   class="color-black bold input-valid"
                                   name="forma_pagamento"
                                   id="forma_pagamento"
                                   autocomplete="off"
                                   onkeyup="upper(this)"
                                   style="text-transform: uppercase;">
                </div> 

                <div class="sixteen wide field required condicao_pagamento">
                        <div class="ui small action input">

                            <input type="hidden" class="color-black bold input-valid"
                                onkeyup="format_codicaoPagamento(this)"
                                @if (!empty($finalizacao))
                                value="{{ $finalizacao->condicao_pagamento }}"
                                @endif
                                name="condicao_pagamento"
                                id="condicao_pagamento"
                                autocomplete="off">

                </div>

                <input type="hidden" id="pre_forma" name="pre_forma" value="">
                <input type="hidden" id="pre_condicao" name="pre_condicao" value="">
                <input type="hidden" id="valor_parcela_nova" name="valor_parcela_nova" value="">
                <input type="hidden" id="parcelamento_cliente" name="parcelamento_cliente" value="" onkeyup="format_codicaoPagamento(this)">
                <input type="hidden" id="verificacao_valor" name="verificacao_valor" value="">
                <input type="hidden" id="condi_temp" name="condi_temp" value="">
                <input type="hidden" id="confe_forma" name="confe_forma" value="">
                <input type="hidden" id="valor_conf" name="valor_conf" value="">


        </div>
    </div>

</div>

<script type="text/javascript"> 
 

    @if(\App\Helpers\Helper::isMobile())
    document.getElementById("cond_mobile").style.display = "none";
    @endif

onVendaOrcamentoConfigFormFinalizar({
        "context" : "#orcamentoFinalizar",
        "route_AdicionarForma": "{{ route('vendas.orcamento.pesquisa_forma_pagamento') }}",
        "data":{
                forma_pag: $("#forma_pagamento_cod", "#orcamentoFinalizar").val()
            } 
});

$(document).ready(function() {

    var $como_inicia = {{$tem_preco_livre}};

    if($como_inicia > 0){
        $("#preco_livre", "#orcamentoFinalizar").focus().select();
    }else{
        $("#preco_livre", "#orcamentoFinalizar").attr("disabled","disabled").addClass("backcolor-disabled");
        $("#btnAddValorLivre", "#orcamentoFinalizar").attr("disabled","disabled").addClass("backcolor-disabled");
        $("#forma_pagamento_cod", "#orcamentoFinalizar").focus().select();
    }



});

//document.getElementById("condicao_modificada").style.display = "none";
//document.getElementById("cp_mod").style.display = "none";

$("#formas_pagamentos_orcamento", "#orcamentoFinalizar").select2(formas_pagamentos_orcamento({
        url: "{{ route('select2_formas_pagamento_orcamento') }}",
        iconSelect2: '<i class="far fa fa-credit-card color-black-light"></i>'
}));

$("#cartoes_cliente", "#orcamentoFinalizar").select2(parametros_cartoes({
        url: "{{ route('select2_parametro_cartao') }}",
        iconSelect2: '<i class="far fa fa-credit-card color-black-light"></i>'
}));

$("#bancos_cliente", "#orcamentoFinalizar").select2(bancos_clientes({
        url: "{{ route('select2_bancos') }}",
        iconSelect2: '<i class="far fa fa-university color-black-light"></i>'
}));

document.onkeyup=function(e){
    if(e.which == 17)
    pressedCtrl =false;
} 

document.onkeydown=function(e){
    if (e.which == 115) {
        $btnReiniciarCompra.trigger("click");
    } 
}

document.onkeyup=function(e){
    if (e.which == 109) {
        @if(($param_finan) == 1)
        setToast('Descontos desativado pelo financeiro', "red");
        @else
        $desconto_valorFlz.focus().select();
        @endif
    } 
}

/*else if (e.which == 49) {
        $("#pag1").trigger("click");
    } else if (e.which == 50) {
        $("#pag2").trigger("click");
    } else if (e.which == 51) {
        $("#pag3").trigger("click");
    } else if (e.which == 52) {
        $("#pag4").trigger("click");
    } else if (e.which == 53) {
        $("#pag5").trigger("click");
    } else if (e.which == 54) {
        $("#pag6").trigger("click");
    } else if (e.which == 55) {
        $("#pag7").trigger("click");
    } else if (e.which == 56) {
        $("#pag8").trigger("click");
    } else if (e.which == 57) {
        $("#pag9").trigger("click");
    } else if (e.which == 48) {
        $("#pag10").trigger("click");
    } else if (e.which == 189) {
        $("#pag11").trigger("click");
    } else if (e.which == 187) {
        $("#pag12").trigger("click");
    } else if (e.which == 8) {
        //$("#btnLimparCondicaoPagamento").trigger("click");
        //$("#btnLimparFormaPagamento").trigger("click");
    } else if (e.which == 97) {
        $("#DIN").trigger("click");
    } else if (e.which == 98) {
        $("#CHE").trigger("click");
    } else if (e.which == 99) {
        $("#CRE").trigger("click");
    } else if (e.which == 100) {
        $("#DEB").trigger("click");
    } else if (e.which == 101) {
        $("#CLO").trigger("click");
    } else if (e.which == 102) {
        $("#VAL").trigger("click");
    } else if (e.which == 103) {
        $("#VRE").trigger("click");
    } else if (e.which == 104) {
        $("#VPR").trigger("click");
    } else if (e.which == 105) {
        $("#VCO").trigger("click");
    } else if (e.which == 110) {
        $("#centavo_ultima_parcela").trigger("click");
    }
}*/

    $("#formOrcamentoFinalizarCreate").bind('submit', false);
    $(".tooltip_custom").popup();

    $(".datea").datepicker(datepickerPtBr()).mask("00/00/0000");
    $(".inputVctoParcela").datepicker(datepickerPtBr()).mask("00/00/0000");
    $(".money").mask('000.000.000.000.000,00', {reverse: true});
    $(".inputValorParcela").mask('000.000.000.000.000,00', {reverse: true});

    var ctxOrcFnz = "#orcamentoFinalizar";
    var orcamento_parcelas = [];

    var $ctxOrcamentoFinalizar = $("#formOrcamentoFinalizarCreate");
    var $btnLimparFormaPgt = $("#btnLimparFormaPgt", $ctxOrcamentoFinalizar);
    var $btnLimparCondPgt = $("#btnLimparCondicaoPagamentoCOD", $ctxOrcamentoFinalizar);
    var $btnLimparCondPgtNovo = $("#btnLimparCondicaoPagamentoCODNOVO", "#orcamentoFinalizar");
    var $btnReiniciarCompra = $("#btnLimparFormaPagamento", "#orcamentoFinalizar");
    var $pre_forma_pag = $("#pre_forma", "#orcamentoFinalizar");
    var $pre_condicao_pag = $("#pre_condicao", "#orcamentoFinalizar");
    var $verificacao_valor = $("#verificacao_valor", "#orcamentoFinalizar");
    var $escolha_forma_pagamento = $("#forma_pagamento_cod", "#formOrcamentoFinalizarCreate");
    var $escolha_condicao_pagamento = $("#condicao_pagamento_cod", "#formOrcamentoFinalizarCreate");
    var $condicao_pagamento = $("#condicao_pagamento", "#orcamentoFinalizar");
    var $cartoes_clientes = $("#cartoes_cliente", "#orcamentoFinalizar");
    var $bancos_cliente = $("#bancos_cliente", "#orcamentoFinalizar");
    var $parcela_nova = $("#valor_parcela_nova", "#orcamentoFinalizar");
    var $parcelamento_cliente = $("#parcelamento_cliente", "#orcamentoFinalizar");
    var $forma_pagamento = $("#forma_pagamento", "#orcamentoFinalizar");
    var $nova_cp_atu = $("#condicao_modificada", $ctxOrcamentoFinalizar);
    var $nova_cp_txt = $("#condicao_pagamento_cod_nova", "#orcamentoFinalizar");
    var $desconto_valorFlz = $("#desconto_valor", "#orcamentoFinalizar");
    var $desconto_porcFlz = $("#desconto_porc", "#orcamentoFinalizar");
    var $valor_abater = $("#valor_abater", "#orcamentoFinalizar");
    var $entrada = $("#entrada", "#orcamentoFinalizar");
    var $condi_temp = $("#condi_temp", "#orcamentoFinalizar");
    var $confe_forma = $("#confe_forma", "#orcamentoFinalizar");
    var $valor_conf = $("#valor_conf", "#orcamentoFinalizar");
    var $preco_item = $("#preco_item", "#formOrcamentoCreate").val();
    var $preco_item2 = $("#preco_item", "#formOrcamentoEdit").val();
    var $preco_livre = $("#preco_livre", "#orcamentoFinalizar");
    var $acrescimo_valor = $("#acrescimo_valor", "#orcamentoFinalizar");
    var $acrescimo_porc = $("#acrescimo_porc", "#orcamentoFinalizar");

    var parcelas_existentes = false;

    $acrescimo_valor.keyup(function(event){
        if(event.keyCode == 13){
            let v = toFloat($(this).val());
            let totBruto = toFloat($preco_livre.val()) + v;
            let acres_p = v / totBruto * 100;
            let totLiq = totBruto - toFloat($desconto_valorFlz.val());
            let totfinal = totLiq;
            $acrescimo_porc.val(format_money(acres_p));
            $pre_forma_pag.val(totfinal);
            $verificacao_valor.val(totfinal);
            $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
            $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(totBruto,2,',','.'));
            $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
            onVendaOrcamentoMontaTableParcelas();

            if($acrescimo_valor.val() == "0" || $acrescimo_valor.val() == "0,00"){
            $acrescimo_porc.focus().select();
            }else{
            $valor_abater.focus().select();  
            }

        }
    });

    $("#btnAddPocValor", "#orcamentoFinalizar").click(function () {
        let v = toFloat($(this).val());
            let totBruto = toFloat($preco_livre.val()) + v;
            let acres_p = v / totBruto * 100;
            let totLiq = totBruto - toFloat($desconto_valorFlz.val());
            let totfinal = totLiq;
            $acrescimo_porc.val(format_money(acres_p));
            $pre_forma_pag.val(totfinal);
            $verificacao_valor.val(totfinal);
            $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
            $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(totBruto,2,',','.'));
            $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
            onVendaOrcamentoMontaTableParcelas();

            if($acrescimo_valor.val() == "0" || $acrescimo_valor.val() == "0,00"){
            $acrescimo_porc.focus().select();
            }else{
            $valor_abater.focus().select();  
            }   
    });

    $acrescimo_porc.keyup(function(event){
        if(event.keyCode == 13){
            let v = toFloat($(this).val());
            let totBruto = toFloat($preco_livre.val());
            let desc_v = v / 100 * totBruto;
            let bruto_final = totBruto + desc_v;
            let totLiq = totBruto - toFloat($desconto_valorFlz.val());
            let totfinal = totLiq + desc_v;
            $acrescimo_valor.val(format_money(desc_v));
            $pre_forma_pag.val(totfinal);
            $verificacao_valor.val(totfinal);
            $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
            $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(bruto_final,2,',','.'));
            $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
            onVendaOrcamentoMontaTableParcelas();

            $valor_abater.focus().select();  
            

        }
    });

    $("#btnAddPoc", "#orcamentoFinalizar").click(function () {
        let v = toFloat($(this).val());
            let totBruto = toFloat($preco_livre.val());
            let desc_v = v / 100 * totBruto;
            let bruto_final = totBruto + desc_v;
            let totLiq = totBruto - toFloat($desconto_valorFlz.val());
            let totfinal = totLiq + desc_v;
            $acrescimo_valor.val(format_money(desc_v));
            $pre_forma_pag.val(totfinal);
            $verificacao_valor.val(totfinal);
            $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
            $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(bruto_final,2,',','.'));
            $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
            onVendaOrcamentoMontaTableParcelas();

            $valor_abater.focus().select(); 
    });

    $preco_livre.keyup(function(event){
        if(event.keyCode == 13){
            let v = toFloat($(this).val());
        let totBruto = v + toFloat($acrescimo_valor.val());
        let totLiq = totBruto - toFloat($desconto_valorFlz.val());
        let totfinal = totLiq;
        $pre_forma_pag.val(totLiq);
        $verificacao_valor.val(totLiq);
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(totBruto,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        onVendaOrcamentoMontaTableParcelas();

            var $como_inicia = {{$tem_preco_livre}};

            if($como_inicia > 0){
                $("#forma_pagamento_cod", "#orcamentoFinalizar").focus();
            }else{
                $valor_abater.focus().select();
            }
        }
    });

    $("#btnAddValorLivre", "#orcamentoFinalizar").click(function () {
        let v = toFloat($(this).val());
        let totBruto = v + toFloat($acrescimo_valor.val());
        let totLiq = totBruto - toFloat($desconto_valorFlz.val());
        let totfinal = totLiq;
        $pre_forma_pag.val(totLiq);
        $verificacao_valor.val(totLiq);
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(totBruto,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        onVendaOrcamentoMontaTableParcelas();

            var $como_inicia = {{$tem_preco_livre}};

            if($como_inicia > 0){
                $("#forma_pagamento_cod", "#orcamentoFinalizar").focus();
            }else{
                $valor_abater.focus().select();
            }
    });

    @if(count($parcelas) > 0)
        parcelas_existentes = true;
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(0,2,',','.'));
        $verificacao_valor.val(number_format(0,2,',','.'));
        @foreach($parcelas as $parcela)
            orcamento_parcelas.push({
                seq: "{{ $parcela->sequencial }}",
                valor: toFloat("{{ $parcela->valor }}"),
                vcto: "{{ $parcela->vencimento }}",
                forma: "{{ $parcela->forma_pagamento_id }}",
                div: "{{ $parcela->cAut }}"
            });
        @endforeach
    @endif
     

    $btnReiniciarCompra.click(function () {

        var $como_inicia = {{$tem_preco_livre}};

        $condicao_pagamento.val("");
        $escolha_condicao_pagamento.val("");
        $escolha_forma_pagamento.val("");
        $forma_pagamento.val("");
        $desconto_valorFlz.val("0,00");
        $desconto_porcFlz.val("0,00");
        $parcela_nova.val("");
        $parcelamento_cliente.val("");
        $pre_condicao_pag.val("");
        $valor_abater.val("");
        $condi_temp.val("");
        $confe_forma.val("");
        $acrescimo_valor.val("0,00");
        $acrescimo_porc.val("0,00");
        $preco_livre.val(number_format("{{$orcamento->total_bruto}}",2,',','.'));

        if($como_inicia > 0){
        $("#preco_livre", "#orcamentoFinalizar").focus().val('');
        }else{
        $("#forma_pagamento_cod", "#orcamentoFinalizar").focus();
        }
        
        let totBruto = parseFloat("{{$orcamento->total_bruto}}");
        $verificacao_valor.val(number_format(totBruto,2,',','.'));
        $pre_forma_pag.val(totBruto);
        let totLiq = totBruto;
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(totBruto,2,',','.'));
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        setToast('Venda reiniciada com sucesso!', "green");
        parcelas_existentes = false;
        onVendaOrcamentoMontaTableParcelas();
    });

    $btnLimparFormaPgt.click(function () {
        $escolha_forma_pagamento.val("");
        $escolha_forma_pagamento.focus();
    });

    $btnLimparCondPgt.click(function () {
        $escolha_condicao_pagamento.val("");
        $escolha_condicao_pagamento.focus();
    });

    $btnLimparCondPgtNovo.click(function () {
        $nova_cp_txt.val("");
        $nova_cp_txt.focus();
    });

    function calculaQtdeCondPgto(){
        $("#totParcelas", "#orcamentoFinalizar").html(
            $condicao_pagamento.val().split("/").length + " " + '<span class="color-gray">Meios de pagamento</span>');
    }

    $(".condicao", "#orcamentoFinalizar").click(function () {
        let value = parseInt($.trim($(this).data("value")));
        let condicao_list = $.trim($condicao_pagamento.val());
        $condi_temp.val("");

        if (empty(condicao_list)) {
            if (value === 0) {
                $condicao_pagamento.val(value);
                $parcelamento_cliente.val(value + " - " + $confe_forma.val());
                $condi_temp.val(value);
            } else {
                let dias = 30;
                for (let i = 0; i < value; i++) {
                    if (i === 0)
                        $condicao_pagamento.val(dias);
                    else
                        $condicao_pagamento.val($condicao_pagamento.val() + "/" + dias);

                    if (empty($parcelamento_cliente.val()))
                        $parcelamento_cliente.val(dias + " - " + $confe_forma.val());
                    else
                        $parcelamento_cliente.val($parcelamento_cliente.val() + "/" + dias + " - " + $confe_forma.val());

                        if(empty($condi_temp.val()))
                            $condi_temp.val(dias);
                        else
                            $condi_temp.val($condi_temp.val() + "/" + dias);
                        dias += 30;
                    
                }
            }
        } else {
                let dias = 30;
                //$condicao_pagamento.val("");
                $condi_temp.val("");
                if (value === 0) {
                $condicao_pagamento.val($condicao_pagamento.val() + "/" + value);
                $parcelamento_cliente.val($parcelamento_cliente.val() + "/" + value + " - " + $confe_forma.val());
                $condi_temp.val(value);
                }
                for (let i = 0; i < value; i++) {
                        $condicao_pagamento.val($condicao_pagamento.val() + "/" + dias);

                    if (empty($parcelamento_cliente.val()))
                        $parcelamento_cliente.val(dias + " - " + $confe_forma.val());
                    else
                        $parcelamento_cliente.val($parcelamento_cliente.val() + "/" + dias + " - " + $confe_forma.val());

                        if(empty($condi_temp.val()))
                            $condi_temp.val(dias);
                        else
                            $condi_temp.val($condi_temp.val() + "/" + dias);
                        dias += 30;
                }
        }
        parcelas_existentes = false;
        calculaQtdeCondPgto();
        onVendaOrcamentoMontaTableParcelas();

            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif

            @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            }
            @endif
        
    });

   /* $(".condicao", $ctxOrcamentoFinalizar).click(function () {
        let value = parseInt($.trim($(this).data("value")));
        let condicao_list = $.trim($condicao_pagamento.val());

        if (empty(condicao_list)){
            $condicao_pagamento.val(value);
        }else{
            $condicao_pagamento.val($condicao_pagamento.val() + "/" + value);
        }

        if(empty($condi_temp.val())){
            $condi_temp.val(value);
        }else{
            $condi_temp.val($condi_temp.val() + "/" + value);
        }
        
        parcelas_existentes = false;
        calculaQtdeCondPgto();
        onVendaOrcamentoMontaTableParcelas();
        if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
        /*onVndOrcAtivaEntrada();
    });*/

    $("#condicao_modificada", "#orcamentoFinalizar").click(function () {
        let value = parseInt($.trim($("#"+$nova_cp_txt.val()).data("value")));
        let condicao_list = $.trim($condicao_pagamento.val());

        if (empty(condicao_list)){
            $condicao_pagamento.val(value);
        }else{
            $condicao_pagamento.val($condicao_pagamento.val() + "/" + value);
        }
        
        parcelas_existentes = false;
        calculaQtdeCondPgto();
        onVendaOrcamentoMontaTableParcelas();

        if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
        }
    });

    
    $("#condicao_modificada", "#orcamentoFinalizar").click(function () {
        let forma_sel = parseInt($.trim($("#"+$nova_cp_txt.val()).data("value")));
        if (empty($parcelamento_cliente.val())){
            $parcelamento_cliente.val(forma_sel);
        }else{
            $parcelamento_cliente.val($parcelamento_cliente.val() + "/" + forma_sel);
        }
        parcelas_existentes = false;
    });

    $nova_cp_txt.keyup(function(event){
        if (event.keyCode == 13 && $("#"+$nova_cp_txt.val()).length > 0){
            $("#"+$nova_cp_txt.val()).trigger("click");
            $nova_cp_txt.val("");
            if(empty($parcela_nova.val())){  
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
        }
    });

    $condicao_pagamento.keyup(function () {
        parcelas_existentes = false;
        calculaQtdeCondPgto();
        onVendaOrcamentoMontaTableParcelas();
        /*onVndOrcAtivaEntrada();*/
    });

    function calculaQtdeFormasPgto() {
        if ($forma_pagamento.val().split("/").length === $condicao_pagamento.val().split("/").length) {
            $("#totFormasPagamento", "#formOrcamentoFinalizarCreate")
                .removeClass("color-red")
                .addClass("color-green")
                .html($forma_pagamento.val().split("/").length + " " + '<span class="color-gray">formas pagamento(s)</span>');
        } else {
            $("#totFormasPagamento", "#formOrcamentoFinalizarCreate")
                .removeClass("color-green")
                .addClass("color-red")
                .html($forma_pagamento.val().split("/").length + " " + '<span class="color-gray">formas pagamento(s)</span>');
        }
    }

    /*$(".fpgto", $ctxOrcamentoFinalizar).click(function () {
        let forma_sel = $.trim($(this).text());
        if (empty($forma_pagamento.val())){
            $forma_pagamento.val(forma_sel);
        }else{
            $forma_pagamento.val($forma_pagamento.val() + "/" + forma_sel);
        }
        parcelas_existentes = false;
        calculaQtdeFormasPgto();
        onVendaOrcamentoMontaTableParcelas();
        $escolha_condicao_pagamento.focus();
       /* onVndOrcAtivaEntrada();
    });*/


    /*$(".condicao", $ctxOrcamentoFinalizar).click(function () {
        let forma_sel = $.trim($(this).text());
        if (empty($parcelamento_cliente.val())){
            $parcelamento_cliente.val(forma_sel);
        }else{
            $parcelamento_cliente.val($parcelamento_cliente.val() + "/" + forma_sel);
        }
        parcelas_existentes = false;
        //calculaQtdeFormasPgto();
        //onVendaOrcamentoMontaTableParcelas();
        /*onVndOrcAtivaEntrada();
    });*/
    

    

    
   /* $entrada.keyup(function(){
        let $this = $(this);
        let vliq = parseFloat("") - toFloat($desconto_valorFlz.val());
        let v = toFloat($(this).val());
        if (v >= vliq) {
            $this.parents(".field").addClass("error");
        }else{
            $this.parents(".field").removeClass("error");
        }
        onVendaOrcamentoMontaTableParcelas();
    });*/
    

    function onVendaOrcamentoMontaTableParcelas() {
        $("#tableParcelasGeradas", "#orcamentoFinalizar").html("");
        orcamento_parcelas = [];
        
        let centavo_ultima_parcela = $("#centavo_ultima_parcela").is(":checked");
        let arrCondPgto = $condicao_pagamento.val().split("/");
        let arrFormas = $forma_pagamento.val().split("/");
        let quantasvezes = $parcelamento_cliente.val().split("/");
        let novo_valor_parcela = $parcela_nova.val().split("/");
        let cartoes_dos_clientes = $cartoes_clientes.val();
        let qtdeParcelas = 0;

        if (!empty($condicao_pagamento.val()))
           qtdeParcelas = arrCondPgto.length;

        if (!empty($forma_pagamento.val())){
            if (arrFormas.length == qtdeParcelas) {

                let t_parc = 0;
                let t_liq = (toFloat($preco_livre.val()) - toFloat($desconto_valorFlz.val())) + toFloat($acrescimo_valor.val());
                $pre_forma_pag.val(number_format(t_liq,2,',','.'));
                @foreach($parcelas as $parcela)
                    t_parc+=parseFloat("{{ $parcela->valor }}");
                    $pre_condicao_pag.val(number_format(t_parc,2,',','.'));
                @endforeach

                if(parcelas_existentes ===  false || number_format(t_liq - t_parc,2,',','.') != '0,00') {

                    let total_bruto = toFloat($preco_livre.val());
                    let total_liquido = total_bruto - toFloat($desconto_valorFlz.val());
                    let desconto_valor = toFloat($desconto_valorFlz.val());
                    if (empty(desconto_valor))
                        desconto_valor = 0;

                    
                   /* let valor_entrada = 0;
                    if ($entrada.prop("disabled") === false && toFloat($entrada.val()) !== 0 && toFloat($entrada.val()) < total_liquido){
                        valor_parcela = novo_valor_parcela;
                    }else{
                        valor_parcela = novo_valor_parcela;
                    }*/
                    

                    let valor_parcela = novo_valor_parcela;

                    let valor = 0;
                    if (centavo_ultima_parcela)
                        valor = Math.trunc(valor_parcela);
                    else
                        valor = valor_parcela;

                    let totCentavos = 0;
                    let now = new Date();
                    for (let i = 0; i < qtdeParcelas; i++) {
                        let date = null;
                        if (parseInt(arrCondPgto[i]) === 0) {
                            date = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
                        } else {
                            date = new Date(now.getFullYear(), now.getMonth(), (now.getDate() + parseInt(arrCondPgto[i])), 0, 0, 0, 0);
                        }
                        let m = date.getMonth() + 1;
                        if (m < 10) m =  "0" + m;
                        let s_date = date.getFullYear() + "-" + m + "-" + ((date.getDate() < 10) ? "0" + date.getDate() : date.getDate());
                        totCentavos += valor_parcela - Math.trunc(valor_parcela);

                        
                        /*let valor_entrada = valor;

                        if (parseInt(arrCondPgto[i]) === 0) {
                            if ($entrada.prop("disabled") === false && toFloat($entrada.val()) !== 0 && toFloat($entrada.val()) < total_liquido )
                                valor_new = valor_entrada;
                        }*/

                        let item = {
                            "seq": i + 1,
                            "valor": toFloat(number_format(novo_valor_parcela[i],2,".","")),
                            "vcto": s_date,
                            "forma": arrFormas[i],
                            "div": quantasvezes[i]
                        };
                        orcamento_parcelas.push(item);
                    }

                    if (centavo_ultima_parcela) {
                        orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] =
                            toFloat(number_format(orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] + totCentavos,2,".",""));
                    }

                    let total_parcela = 0;
                    orcamento_parcelas.forEach(function(v,i){
                        total_parcela+= toFloat(format_money(v.valor));
                        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(t_liq - total_parcela,2,',','.'));
                        $verificacao_valor.val(number_format(t_liq - total_parcela,2,',','.'));
                        if(number_format(t_liq - total_parcela,2,',','.') === '0,00'){
                            setToast('Você finalizou sua venda, aperte o botão " + " para finalizar!', "green");
                            $valor_abater.focus();
                            @if(\App\Helpers\Helper::isMobile())
                            document.getElementById("cond_mobile").style.display = "none";
                            @endif
                        }
                        if(number_format(t_liq - total_parcela,2,',','.') != '0,00'){
                            $escolha_forma_pagamento.focus();
                            $valor_abater.val(number_format(t_liq - total_parcela,2,',','.'));
                        }

                    });
   
                } else {

                    @foreach($parcelas as $parcela)
                        orcamento_parcelas.push({
                            "seq": parseInt("{{ $parcela->sequencial }}"),
                            "valor": toFloat("{{ $parcela->valor }}"),
                            "vcto": "{{ $parcela->vencimento }}",
                            "forma": "{{ $parcela->forma_pagamento_id }}",
                            "div": "{{ $parcela->cAut }}"
                        });
                    @endforeach

                    parcelas_exist
                    entes = true;

                }

            }else{
                orcamento_parcelas = [];
            }
        }
        onVendaOrcamentoMontaTableParcelasHtml();
    }


    function onVendaOrcamentoMontaTableParcelasHtml() {
        let html = '';
        html+='<table class="ui compact celled table">';
        html+='    <thead>';
        html+='        <tr class="td-v-m td-center color-black-light">';
        html+='            <th>&nbsp;</th>';
        html+='            <th>Seq</th>';
        html+='            <th>Valor&nbsp;R$</th>';
        html+='            <th>Vcto</th>';
        html+='            <th>Forma</th>';
        html+='            <th>C.P</th>';
        html+='        </tr>';
        html+='    </thead>';
        html+='    <tbody>';
        if (orcamento_parcelas.length > 0) {
            orcamento_parcelas.forEach((v,i) => {

                html+='    <tr class="td-v-m td-center color-black-light">';
                html+='    <td style="width: 50px !important;" class="td-v-m">';
                html+='        <div class="ui icon basic mini buttons">';
                html+='            <div class="ui icon bottom left pointing dropdown button" tabindex="-1" data-tooltip="Remover" data-position="right center">';
                html+='                <i class="far fa-trash-alt font-size-14 color-red"></i>';
                html+='                <div class="menu transition hidden" tabindex="-1">';
                html+='                    <div class="item">';
                html+='                        <p class="bold color-black-light">Confirma a remoção ?</p>';
                html+='                        <a class="ui left attached button">Não</a>';
                html+='                        <a class="ui red right attached button btnRemoverParc" id="btnRemoverParc" data-id="'+ v.seq +'">';
                html+='                            <span class="color-red bold">Sim</span>';
                html+='                        </a>';
                html+='                    </div>';
                html+='                </div>';
                html+='            </div>';
                html+='        </div>';
                html+='    </td>';
                html+='        <td>' + v.seq + '</td>';
                html+='        <td>';
                html+='            <div class="ui mini input" style="width: 75px;">';
                if (i === (orcamento_parcelas.length - 1)) {
                    html += '                <input type="text" class="inputValorParcela" readonly tabindex="-1" onchange="onInputValorParcelaChange(this)" value="' + format_money(v.valor) + '">';
                } else {
                    html += '                <input type="text" class="inputValorParcela"  onchange="onInputValorParcelaChange(this)" value="' + format_money(v.valor) + '">';
                }
                html+='            </div>';
                html+='        </td>';
                html+='        <td>';
                html+='            <div class="ui mini input" style="width: 85px;">';
                html+='                <input type="text" class="inputVctoParcela" readonly onchange="onInputVctoParcelaChange(this)" value="' + v.vcto.split("-").reverse().join("/") +'">';
                html+='            </div>';
                html+='        </td>';
                html+='        <td>' + v.forma + '</td>';
                html+='        <td>' + v.div + '</td>';
                html+='    </tr>';

            });
        } else {
            html+='        <tr>';
            html+='            <td class="td-center color-red" colspan="5">VENDA NÃO DEFINIDA</td>';
            html+='        </tr>';
        }
        html+='    </tbody>';
        html+='</table>';
        $("#tableParcelasGeradas", "#orcamentoFinalizar").html(html);
        $(".inputValorParcela").mask('000.000.000.000.000,00', {reverse: true});
        $(".inputVctoParcela").datepicker(datepickerPtBr()).mask("00/00/0000");
        $(".btnRemoverParc").click(onVendaExcluirParcTable);
    }

    function onVendaExcluirParcTable() {
    let id = parseInt($(this).data("id"));
    orcamento_parcelas.forEach((v,i) =>{
        if (v.seq === id)
        orcamento_parcelas.splice(i, 1);
    });
    onVendaOrcamentoMontaTableParcelas();
    event.stopImmediatePropagation();
    }

    function onInputValorParcelaChange(el) {
        let valorAlterado = toFloat($(el).val());
        let seq = parseInt($(el).parents("tr").find("td:first").text());

        if (valorAlterado != orcamento_parcelas[seq-1]["valor"]) {

            orcamento_parcelas[seq - 1]["valor"] = valorAlterado;

            let totalLiquido = parseFloat("{{ $orcamento->total_bruto }}") - toFloat($desconto_valorFlz.val());
            let totalModificado = 0;
            let qtdeNaoModificado = 1;
            for (let i = 0; i < orcamento_parcelas.length - 1; i++) {
                if (i <= (seq - 1)) {
                    totalModificado += orcamento_parcelas[i]["valor"];
                } else {
                    qtdeNaoModificado++;
                }
            }

            let totalLancar = totalLiquido - totalModificado;
            let totalLancarParc = toFloat(number_format((totalLancar / qtdeNaoModificado), 2, ",", "."));

            let totalParcGeradas = 0;
            for (let j = seq; j < orcamento_parcelas.length; j++) {
                orcamento_parcelas[j]["valor"] = totalLancarParc;
                totalParcGeradas += orcamento_parcelas[j]["valor"];
            }

            if (totalLiquido < (totalModificado + totalParcGeradas)) {
                orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] =
                    orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] - ((totalModificado + totalParcGeradas) - totalLiquido);
            } else if (totalLiquido > (totalModificado + totalParcGeradas)) {
                orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] =
                    orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] + (totalLiquido - (totalModificado + totalParcGeradas));
            }
            orcamento_parcelas[orcamento_parcelas.length - 1]["valor"] = toFloat(number_format(orcamento_parcelas[orcamento_parcelas.length - 1]["valor"], 2, ".", ""));

            onVendaOrcamentoMontaTableParcelasHtml();

        }
    }

    function onInputVctoParcelaChange(el) {
        let vcto = $(el).val().split("/").reverse().join("-");
        let seq = parseInt($(el).parents("tr").find("td:first").text());
        orcamento_parcelas[seq-1]["vcto"] = vcto;
    }

    $desconto_valorFlz.keyup(function(event){ 
        if(event.keyCode == 13){
         let v = toFloat($(this).val());
        let totBruto = toFloat($preco_livre.val());
        let desc_p = v / totBruto * 100;
        let totLiq = totBruto;
        let totfinal = totLiq - v;
        $desconto_porcFlz.val(format_money(desc_p));
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        let forma_pag = $forma_pagamento.val().split("/");
                        let qtdeRef = 0;
                        let condicao_pag = $condicao_pagamento.val().split("/");
                        if (!empty($forma_pagamento.val()))
                        qtdeRef = condicao_pag.length;
                        for (var i = forma_pag.length; i < qtdeRef; i++) {
                            $forma_pagamento.val($forma_pagamento.val() + "/" + $confe_forma.val())
                        }
        onVendaOrcamentoMontaTableParcelas();
        if(v == "0" || v == "0,00"){
        $desconto_porcFlz.focus().select();
        }else{
        $acrescimo_valor.focus().select();  
        }
        }
    });

    $("#btnAddDescValor", "#orcamentoFinalizar").click(function () {
        let v = toFloat($desconto_valorFlz.val());
        let totBruto = parseFloat("{{$orcamento->total_bruto}}");
        let desc_p = v / totBruto * 100;
        let totLiq = totBruto;
        let totfinal = totLiq - v;
        $desconto_porcFlz.val(format_money(desc_p));
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        let forma_pag = $forma_pagamento.val().split("/");
                        let qtdeRef = 0;
                        let condicao_pag = $condicao_pagamento.val().split("/");
                        if (!empty($forma_pagamento.val()))
                        qtdeRef = condicao_pag.length;
                        for (var i = forma_pag.length; i < qtdeRef; i++) {
                            $forma_pagamento.val($forma_pagamento.val() + "/" + $confe_forma.val())
                        }
        onVendaOrcamentoMontaTableParcelas();
        if(v == "0" || v == "0,00"){
        $desconto_porcFlz.focus().select();
        }else{
        $valor_abater.focus().select();  
        }

    });        

    /*$entrada.keyup(function(event){
        let v = toFloat($(this).val());
        let totBruto = parseFloat("{{$orcamento->total_bruto}}");
        let totLiq = totBruto - v;
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totLiq,2,',','.'));
        onVendaOrcamentoMontaTableParcelas();
        if(event.keyCode == 13){
        $desconto_valorFlz.focus().select();
        }
    });*/

    $desconto_porcFlz.keyup(function(){
        if(event.keyCode == 13){
            let v = toFloat($(this).val());
        let totBruto = toFloat($preco_livre.val());
        let desc_v = v / 100 * totBruto;
        let totLiq = totBruto - desc_v;
        let totfinal =  totLiq;
        $desconto_valorFlz.val(format_money(desc_v));
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        onVendaOrcamentoMontaTableParcelas();
            $acrescimo_valor.focus().select();   
        }
    });

    $("#btnAddDescPorc", "#orcamentoFinalizar").click(function () {
        let v = toFloat($desconto_porcFlz.val());
        let totBruto = parseFloat("{{$orcamento->total_bruto}}");
        let desc_v = v / 100 * totBruto;
        let totLiq = totBruto - desc_v;
        let totfinal =  totLiq;
        $desconto_valorFlz.val(format_money(desc_v));
        $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(totfinal,2,',','.'));
        onVendaOrcamentoMontaTableParcelas();
            $valor_abater.focus().select();   
    });

    $("#btnAddValor", "#orcamentoFinalizar").click(function () {
        let v = toFloat($valor_abater.val());
            let totBruto = parseFloat("{{$orcamento->total_bruto}}");
            let totLiq = totBruto - toFloat($desconto_valorFlz.val());
                let faltPagar = totLiq;
                let faltPagar2 = faltPagar - v;
                $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(totBruto,2,',','.'));
                $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(faltPagar,2,',','.'));
                $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(faltPagar2,2,',','.'));
                let condi_count = $condi_temp.val().split("/");
                    let qtdePac = 0;
                    let totCentavos = 0;
                    if (!empty($condi_temp.val()))
                    qtdePac = condi_count.length;
                        for (var i = 0; i < qtdePac; i++) {
                            let valor_parcela = (v/qtdePac);
                            let valor_final = (Math.floor(valor_parcela * 100) / 100);
                            let valor_comp = parseFloat(valor_final * qtdePac);
                            let comp = toFloat(number_format(v - valor_comp,2,'.',''));
                            $valor_conf.val(comp);

                            if (empty($parcela_nova.val())){ 
                                if (comp > 0 && i === 0) {
                                    $parcela_nova.val(toFloat(number_format(valor_final + comp,2,'.','')));
                            
                                    $valor_conf.val('0');
                                }else{    
                                    $parcela_nova.val(toFloat(number_format(valor_final,2,'.',''))); 
                               } 
                            }else{ 
                                if (comp > 0 && i === 0) {
                                    $parcela_nova.val($parcela_nova.val() + "/" + toFloat(number_format(valor_final + comp,2,'.','')));
                            
                                 $valor_conf.val('0');
                                }else{       
                                 $parcela_nova.val($parcela_nova.val() + "/" + toFloat(number_format(valor_final,2,'.','')));
                                }
                            }
                        }
                            
                            
                        

                        let forma_pag = $forma_pagamento.val().split("/");
                        let qtdeRef = 0;
                        let condicao_pag = $condicao_pagamento.val().split("/");
                        if (!empty($forma_pagamento.val()))
                        qtdeRef = condicao_pag.length;
                        for (var i = forma_pag.length; i < qtdeRef; i++) {
                            $forma_pagamento.val($forma_pagamento.val() + "/" + $confe_forma.val())
                        }
                
                    onVendaOrcamentoMontaTableParcelas();
                    $valor_abater.val("");
                    $condi_temp.val("");
                    $confe_forma.val("");
                    $valor_conf.val(""); 


    });

    $valor_abater.keyup(function(event){
        if (event.keyCode == 13) {
            let v = toFloat($(this).val());
            let totBruto = toFloat($preco_livre.val());
            let totLiq = totBruto - toFloat($desconto_valorFlz.val()); 
                let faltPagar = totLiq + toFloat($acrescimo_valor.val());
                let faltPagar2 = faltPagar - v;
                let bruto_c = totBruto  + toFloat($acrescimo_valor.val());
                $("#labelTotBruto", "#orcamentoFinalizar").html(number_format(bruto_c,2,',','.'));
                $("#labelTotLiq", "#orcamentoFinalizar").html(number_format(faltPagar,2,',','.'));
                $("#labelFaltamPagar", "#orcamentoFinalizar").html(number_format(faltPagar2,2,',','.'));
                let condi_count = $condi_temp.val().split("/");
                    let qtdePac = 0;
                    let totCentavos = 0;
                    if (!empty($condi_temp.val()))
                    qtdePac = condi_count.length;
                        for (var i = 0; i < qtdePac; i++) {
                            let valor_parcela = (v/qtdePac);
                            let valor_final = (Math.floor(valor_parcela * 100) / 100);
                            let valor_comp = parseFloat(valor_final * qtdePac);
                            let comp = toFloat(number_format(v - valor_comp,2,'.',''));
                            $valor_conf.val(comp);

                            if (empty($parcela_nova.val())){ 
                                if (comp > 0 && i === 0) {
                                    $parcela_nova.val(toFloat(number_format(valor_final + comp,2,'.','')));
                            
                                    $valor_conf.val('0');
                                }else{    
                                    $parcela_nova.val(toFloat(number_format(valor_final,2,'.',''))); 
                               } 
                            }else{ 
                                if (comp > 0 && i === 0) {
                                    $parcela_nova.val($parcela_nova.val() + "/" + toFloat(number_format(valor_final + comp,2,'.','')));
                            
                                 $valor_conf.val('0');
                                }else{       
                                 $parcela_nova.val($parcela_nova.val() + "/" + toFloat(number_format(valor_final,2,'.','')));
                                }
                            }
                        }
                            
                            
                        

                        let forma_pag = $forma_pagamento.val().split("/");
                        let qtdeRef = 0;
                        let condicao_pag = $condicao_pagamento.val().split("/");
                        if (!empty($forma_pagamento.val()))
                        qtdeRef = condicao_pag.length;
                        for (var i = forma_pag.length; i < qtdeRef; i++) {
                            $forma_pagamento.val($forma_pagamento.val() + "/" + $confe_forma.val())
                        }
                
                    onVendaOrcamentoMontaTableParcelas();
                    $valor_abater.val("");
                    $condi_temp.val("");
                    $confe_forma.val("");
                    $valor_conf.val(""); 
    }});

    $nova_cp_txt.keyup(function(event){
        if (event.keyCode == 13 && $nova_cp_txt.val() === '23'){ 
            $("#23").trigger("click");
            $nova_cp_txt.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
        }
    });

    //quando não possui C.P personalizados
    $escolha_condicao_pagamento.keyup(function(event){
        if (event.keyCode == 13 && $escolha_condicao_pagamento.val() === '0'){ 
            $("#pag0").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        } else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '1') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag1").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        } else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '2') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')) {    
            $("#pag2").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '3') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')) {    
            $("#pag3").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '4') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')) {    
            $("#pag4").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '5') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag5").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '6') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag6").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '7') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag7").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '8') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag8").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '9') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag9").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '10') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag10").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '11') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag11").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }else if ((event.keyCode == 13 && $escolha_condicao_pagamento.val() === '12') && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')){    
            $("#pag12").trigger("click");
            $escolha_condicao_pagamento.val("");
            onVendaOrcamentoMontaTableParcelasHtml();
            @if(\App\Helpers\Helper::isMobile())
            document.getElementById("cond_mobile").style.display = "none";
            @endif
           @if(($param_finan) == 1)
            $valor_abater.focus().val($verificacao_valor.val()).select();    
            @else
            if(empty($parcela_nova.val())){
            $desconto_valorFlz.focus().select();
            }else{
            $valor_abater.focus().select();    
            }
            @endif
        }
        
    });

    $(".checkbok_centavo_ultima", "#orcamentoFinalizar").checkbox({
        onChange: function() {
            onVendaOrcamentoMontaTableParcelas();
        }
    });

function onVendaOrcamentoConfigFormFinalizar(obj){

var $forma_pagamento = $("#forma_pagamento_cod", obj.context);
var $forma_pag = $("#forma_pagamento", obj.context);
var $pre_condicao_pagamento = $("#condicao_pagamento_cod", obj.context);
var $parcela_nova = $("#valor_parcela_nova", obj.context);
var $desconto_valorFlz = $("#desconto_valor", obj.context);
var $valor_abater = $("#valor_abater", obj.context);
var $parcelamento_cliente = $("#parcelamento_cliente", obj.context);
var $condicao_pagamento = $("#condicao_pagamento", obj.context);
var $cp_mod_novo = $("#cp_mod_novo", obj.context);
var $confe_forma = $("#confe_forma", obj.context);
var $fpgto = $(".fpgto", obj.context);
var $nova_cp_txt = $("#condicao_pagamento_cod_nova", obj.context);
var $total = parseFloat("{{$orcamento->total_bruto}}");

function onVendaOrcamentoModPagamento(){

    $.ajax({
        url: obj.route_AdicionarForma,
        type: "get",
        dataType: "json",
        data: {
           forma: $forma_pagamento.val(),
           fpgto: $forma_pagamento.val()

        },
        beforeSend: function(){
            wait_naja(obj.context);
        }
    }).done(function(response){
        
        if (!$.isEmptyObject(response)){

            if (!empty(response.form_pag) && response.passou === 'OK'){
                $forma_pagamento.val("");

                $confe_forma.val(response.mod_pagar);

                    for (var i = 0, l = response.form_pag.length; i < l; i++) {
                        let condicao_list = $.trim($condicao_pagamento.val());
                        let condi_temp = $.trim($condi_temp.val());

                        if (empty(condicao_list)){
                            $condicao_pagamento.val(Object.values(response.form_pag[i]));
                            $forma_pag.val(Object.values(response.modo_forms_id[i]));
                            $parcelamento_cliente.val(Object.values(response.form_pag[i]) +" - "+ Object.values(response.modo_forms_id[i]));
                        }else{
                            $condicao_pagamento.val($condicao_pagamento.val() + "/" + Object.values(response.form_pag[i]));
                            $forma_pag.val($forma_pag.val() + "/" + Object.values(response.modo_forms_id[i]));
                            $parcelamento_cliente.val($parcelamento_cliente.val() + "/" + Object.values(response.form_pag[i]) + " - "+ Object.values(response.modo_forms_id[i]));
                        }

                        if(empty(condi_temp)){
                        $condi_temp.val(Object.values(response.form_pag[i]));
                        }else{
                        $condi_temp.val($condi_temp.val() + "/" + Object.values(response.form_pag[i]));
                        }
                    }  
                    onVendaOrcamentoMontaTableParcelas();
                    $valor_abater.focus().val(($verificacao_valor.val())).select();
                removeWait(obj.context);
            }
            if (response.passou === "NOK" && empty(response.form_pag)) {

                @if(\App\Helpers\Helper::isMobile())
                document.getElementById("cond_mobile").style.display = "block";
                @endif

                $forma_pagamento.val("");
                $pre_condicao_pagamento.focus();
                $confe_forma.val(response.mod_pagar); 
                
                if (empty($forma_pag.val())){
                        $forma_pag.val(response.mod_pagar);
                    }else{
                        $forma_pag.val($forma_pag.val() + "/" + response.mod_pagar);
                }
             
                removeWait(obj.context);
                
            }

            if (response.passou === "NOK2" && empty(response.form_pag)) {

                $forma_pagamento.val("");

                $forma_pagamento.focus();
                setToast(response.data, "red");
                removeWait(obj.context);
                //onVendaOrcamentoMontaTableParcelas();
            }

            if (response.passou === "NOK3" && empty(response.form_pag)) {
                $forma_pagamento.val("");

                $forma_pagamento.focus();
                setToast(response.data, "red");

                removeWait(obj.context);
                //onVendaOrcamentoMontaTableParcelas();

            }
            
            //mountItem(response);
            //reset components

        }else{
            removeWait(obj.context);
            createFormModalError('Ocorreu um erro ao validar sua forma de pagamento, por favor tente outra forma.');
        }
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        removeWait(obj.context);
        $forma_pagamento.val("");
        createFormModalError('Ocorreu um erro ao validar sua forma de pagamento.');
    });

}

    @if(!\App\Helpers\Helper::isMobile())

        $forma_pagamento.keyup(function(event){
            let $this = $(this);
            if (event.keyCode == 13 && !empty($this.val())) {
                onVendaOrcamentoModPagamento($this.val());
            }
        });

        $(".fpgto", "#formOrcamentoFinalizarCreate").click(function () {   
        let $this = $.trim($(this).text());
        $forma_pagamento.val($this);
       });

       $(".fpgto", "#formOrcamentoFinalizarCreate").click(function () {
        let $this = $(this);
        onVendaOrcamentoModPagamento($this.val());
       });

    @endif

    @if(\App\Helpers\Helper::isMobile())

        $forma_pagamento.keyup(function(event){
            let $this = $(this);
            if (event.keyCode == 13 && !empty($this.val())) {
                onVendaOrcamentoModPagamento($this.val());
            }
        });

        $(".fpgto", "#formOrcamentoFinalizarCreate").click(function () {   
        let $this = $(this).data('value');
        $forma_pagamento.val($this);
       });

       $(".fpgto", "#formOrcamentoFinalizarCreate").click(function () {
        let $this = $(this);
        onVendaOrcamentoModPagamento($this.val());
       });
       
    @endif   

}

    //Verificação de tipo de compra
    if($preco_item2 === 'VISTA' || $preco_item === 'VISTA'){
        $("#pag1", "#orcamentoFinalizar").addClass("disabled");
        $("#pag2", "#orcamentoFinalizar").addClass("disabled");
        $("#pag3", "#orcamentoFinalizar").addClass("disabled");
        $("#pag4", "#orcamentoFinalizar").addClass("disabled");
        $("#pag5", "#orcamentoFinalizar").addClass("disabled");
        $("#pag6", "#orcamentoFinalizar").addClass("disabled");
        $("#pag7", "#orcamentoFinalizar").addClass("disabled");
        $("#pag8", "#orcamentoFinalizar").addClass("disabled");
        $("#pag9", "#orcamentoFinalizar").addClass("disabled");
        $("#pag10", "#orcamentoFinalizar").addClass("disabled");
        $("#pag11", "#orcamentoFinalizar").addClass("disabled");
        $("#pag12", "#orcamentoFinalizar").addClass("disabled");
    }
    
    /*function onVndOrcAtivaEntrada(){

        let arrParc = $condicao_pagamento.val().split("/");

        if (($condicao_pagamento.val().substring(0,1).toString() === "0" && arrParc.length > 1) && ($preco_item2 === 'PRAZO' || $preco_item === 'PRAZO')) {
            $entrada.val("").prop("disabled", false);
        }else{
            $entrada.val("").prop("disabled", true);
        }
    
   }*/

    
    /*if (!parcelas_existentes) {
        onVndOrcAtivaEntrada();
    }else if (toFloat($entrada.val()) <= 0){
        if ($forma_pagamento.val().split("/").length > 1 && $condicao_pagamento.val().split("/")[0] != 0)
            $entrada.prop("disabled", true);
    }*/
    

     onVendaOrcamentoMontaTableParcelas(); 

</script>
