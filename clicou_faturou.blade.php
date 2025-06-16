<div class="ui form" id="content_clicou_faturou">
 
 <div class="ui segments m-t-0 m-b-0" style="margin-top: 8px;">

        <div class="ui segment">
            <h3 class="color-green">
           <center> VENDA FATURADA!</center>
                <span class="font-size-14 color-gray bold-none"></span>
            </h3>
        </div>
    </div>

    <div class="ui segments m-t-0 m-b-0" style="margin-top: 8px;">

        <div class="ui segment">
            <h3 class="color-black-light">
                <center>&nbsp;REALIZAR OUTRA OPERAÇÃO?</center>
                <span class="font-size-14 color-gray bold-none"></span>
            </h3>
        </div>
    </div>

    @if(\App\Helpers\Helper::isMobile())
        <div class="ui three stackable column grid m-t-2">
    @else
        <div class="ui three column grid m-t-2">
    @endif

    <div class="column">
            <a href="" class="ui link fluid card" id="btnEmitirNFE">
                <div class="content">
                    <i class="right floated fal fa fa-file-code-o color-green font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [1]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                        EMITIR NF-e
                    </div>
                    <div class="description">
                    </div>
                </div>
                <div class="extra content">
                </div>
            </a>
    </div>

    <div class="column">
            <a href="" class="ui link fluid card" id="btnEmitirNFCE">
                <div class="content">
                    <i class="right floated fal fa fa-file-code-o color-orange font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [2]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                     EMITIR NFC-e
                    </div>
                    <div class="description">
                    </div>
                </div>
                <div class="extra content">
                </div>
            </a>
    </div>

    <div class="column">
            <a href="{{ route('vendas.report.cupom',['orcamento_id'=>$orcamento->id]) }}" target="_blank" class="ui link fluid card" id="btnEmitirRomaneio">
                <div class="content">
                    <i class="right floated fal fa fa-print color-blue font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [3]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                        ROMANEIO
                    </div>
                    <div class="description">
                    </div>
                </div>
                <div class="extra content">
                </div>
            </a>
    </div>

    
    <div class="column">
            <a href="{{ route('vendas.index') }}" class="ui link fluid card" id="btnSairFaturamento">
                <div class="content">
                    <i class="right floated fal fa fa-times color-red font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [4]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                        SAIR
                    </div>
                    <div class="description">
                    </div>
                </div>
                <div class="extra content">
                </div>
            </a>
    </div>

        <div class="four wide column p-r-0 p-t-7">

                                @if(!empty($orcamento->cliente->cidade))
                                    @if($orcamento->empresa->cidade->estado_id ==
                                        $orcamento->cliente->cidade->estado_id)
                                        <p class="m-0 p-0 p-t-6 font-size-8">&nbsp;</p>
                                    @endif
                                @else
                                    <p class="m-0 p-0 p-t-6 font-size-8">&nbsp;</p>
                                @endif
                                <div class="ui slider checkbox m-t-9" style="display: none">
                                    <input type="checkbox" name="consumidor_final" id="consumidor_final"
                                           {{ (!empty($nota) && ($nota->ide_indFinal == 0)) ? '' : 'checked' }}>
                                    @if(\App\Helpers\Helper::isMobile())
                                        <label for="consumidor_final" title="Consumidor Final" style="cursor: pointer">cons.&nbsp;fin</label>
                                    @else
                                        <label for="consumidor_final" title="Consumidor Final" style="cursor: pointer">consumidor&nbsp;final</label>
                                    @endif
                                </div>

                                @if(!empty($orcamento->cliente->cidade))
                                    @if($orcamento->empresa->cidade->estado_id !=
                                        $orcamento->cliente->cidade->estado_id)
                                        <div class="ui slider checkbox m-t-9" style="display: none">
                                            <input type="checkbox" name="ide_idDest" id="ide_idDest"
                                                {{ (!empty($nota) && ($nota->ide_idDest == 2)) ? 'checked' : 'checked' }}>
                                            <label for="ide_idDest" style="cursor: pointer">nota&nbsp;interestadual</label>
                                        </div>
                                    @endif
                                @endif

        </div>

    <tr>
                    <td>
                        
                        <div class="ui small input" style="width: 250px;">
                       
                            <input type="hidden" onkeyup="capitalize(this)" placeholder="Nome"
                                   id="consumidor_nome" value="{{ !empty($nota) ? $nota->consumidor_nome : '' }}">
                        </div>

                        
                        <div class="ui small input">
                      
                            <input type="hidden" placeholder="CPF" id="consumidor_cpf"
                                   value="{{ !empty($nota) ? $nota->consumidor_cpf : '' }}">
                        </div>
                    </td>
    </tr>

        <input type="hidden" id="orcamento_id" value="{{ $orcamento->id }}">
        <input type="hidden" id="ide_nNF" value="{{ !empty($nota) ? $nota->ide_nNF : '' }}">
        <input type="hidden" id="ide_mod" value="{{ !empty($nota) ? $nota->ide_mod : '' }}">
        <input type="hidden" id="total_frete" value="{{ !empty($nota) ? number_format($nota->total_frete,2,',','.') : '0,00' }}">
        <input type="hidden" id="total_seguro" value="{{ !empty($nota) ? number_format($nota->total_seguro,2,',','.') : '0,00' }}">
        <input type="hidden" id="total_outros" value="{{ !empty($nota) ? number_format($nota->total_outro,2,',','.') : '0,00' }}">

</div>

<script type="text/javascript">

var ctxParametroContent = "#content_clicou_faturou";


var ChangeMenu = function(el,route){
            $(ctxParametroContent).addClass("loading");
            //$(".item", ctxParametroMenu).removeClass("active");
            //$(el).addClass("active");
            $.getJSON(route ,null, function(response){
                $(ctxParametroContent).empty().html(response.data).removeClass("loading");
            });
} 

document.onkeydown=function(e){
    if (e.which == 97) {
        $("#btnEmitirNFE").click();
    } else if (e.which == 98){
        $("#btnEmitirNFCE").click();
    } else if (e.which == 99){
        $("#btnEmitirRomaneio").click();
    } else if (e.which == 100){
        $("#btnSairFaturamento").click();
    }
}

$("#btnEmitirNFE").click(function (e) {
            e.preventDefault();
            nfe_orcamento({
            "context": "#content_clicou_faturou",
            "route": "{{ route('vendas.orcamento.finalizar.store_emitir_nota') }}",
            "method": "post",
            "data": {
                orcamento_id: $("#orcamento_id", "#content_clicou_faturou").val(),
                ide_nNF: $("#ide_nNF", "#content_clicou_faturou").val(),
                ide_mod: $("#ide_mod", "#content_clicou_faturou").val(),
                consumidor_cpf: $("#consumidor_cpf", "#content_clicou_faturou").val(),
                consumidor_nome: $("#consumidor_nome", "#content_clicou_faturou").val(),
                ide_idDest: $("#ide_idDest", "#content_clicou_faturou").val(),
                consumidor_final: $("#consumidor_final", "#content_clicou_faturou").val(),
                total_frete: $("#total_frete", "#content_clicou_faturou").val(),
                total_seguro: $("#total_seguro", "#content_clicou_faturou").val(),
                total_outros: $("#total_outros", "#content_clicou_faturou").val()
            } 
})});  

$("#btnEmitirNFCE").click(function (e) {
            e.preventDefault();
            nfce_orcamento({
            "context": "#content_clicou_faturou",
            "route": "{{ route('vendas.orcamento.finalizar.store_emitir_notanfce') }}",
            "method": "post",
            "data": {
                orcamento_id: $("#orcamento_id", "#content_clicou_faturou").val(),
                ide_nNF: $("#ide_nNF", "#content_clicou_faturou").val(),
                ide_mod: $("#ide_mod", "#content_clicou_faturou").val(),
                consumidor_cpf: $("#consumidor_cpf", "#content_clicou_faturou").val(),
                consumidor_nome: $("#consumidor_nome", "#content_clicou_faturou").val(),
                ide_idDest: $("#ide_idDest", "#content_clicou_faturou").val(),
                consumidor_final: $("#consumidor_final", "#content_clicou_faturou").val(),
                total_frete: $("#total_frete", "#content_clicou_faturou").val(),
                total_seguro: $("#total_seguro", "#content_clicou_faturou").val(),
                total_outros: $("#total_outros", "#content_clicou_faturou").val()
            } 
})});

$("#btnSairFaturamento").click(function (e) {
            e.preventDefault();
            window.location.href = "{{ route('vendas.index') }}";
});

$("#btnEmitirRomaneio").click(function (e) { 
    e.preventDefault();

                       @if($orcamento->modo_pdv === 1)

                        //imprime e fecha a nova janela quando estiver carregada 
                       newWindow = window.open('{{ route('vendas.report.cupom',['orcamento_id'=>$orcamento->id]) }}');

                       $(newWindow).ready(function() {
                        @if(\App\Helpers\Helper::isMobile())
                        setTimeout(function() {
                        window.open("/vendas/orcamento" + '?orcamento_id=' + $orcamento->id + "/imprimir_cupom", "_blank");
                        }, 1000);
                        @endif
                        setTimeout(function() {
                        newWindow.print();
                        }, 1000);
                        setTimeout(function() {
                        newWindow.close();
                        window.location.href = "{{ route('vendas.index') }}";
                        }, 8000);
                        });
                        @else
                        newWindow = window.open('{{ route('vendas.report.cupom',['orcamento_id'=>$orcamento->id]) }}');
                        window.location.href = "{{ route('vendas.index') }}";
                        @endif

});

function nfe_orcamento(obj){
    $.ajax({
        url: obj.route,
        method: obj.method,
        dataType: "json",
        data: obj.data,
        beforeSend: function(){
            wait_save(obj.context);
        }
    }).done(function(response){
        if (response.status === "OK") {
        removeWait(obj.context);
        setToast(response.data, "green");
        $("#btnSearch_VendaOrcamento").trigger("click");
        @if($orcamento->modo_pdv === 1)
        newWindow = window.open("/fiscal/danfe" + '?nf_id=' + response.nf_id, "_blank");

        $(newWindow).ready(function() {
                        setTimeout(function() {
                        newWindow.print();
                        }, 1000);
                        setTimeout(function() {
                        newWindow.close();
                        window.location.href = "{{ route('vendas.index') }}";
                        }, 8000);
                        });
        @else
        window.location.href = "{{ route('vendas.index') }}";                
        window.open("/fiscal/danfe" + '?nf_id=' + response.nf_id, "_blank");
        @endif
        $("#dialogOrcamentoFinalizarPosVenda").remove();
        //ChangeMenu(this,  "{{ route('vendas.orcamento.finalizar.store_faturar') }}");
        }
        if (response.status === "NOK") {
            removeWait(obj.context);
            setToast(response.data, "red");
        //ChangeMenu(this,  "{{ route('vendas.orcamento.finalizar.oque_fazer') }}");
        } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.data);
        }
    }).fail(function(jqXHR,errorStatus, thrownError){
        removeWait(obj.context);
    });
}

function nfce_orcamento(obj){
    $.ajax({
        url: obj.route,
        method: obj.method,
        dataType: "json",
        data: obj.data,
        beforeSend: function(){
            wait_save(obj.context);
        }
    }).done(function(response){
        if (response.status === "OK") {
        removeWait(obj.context);
        setToast(response.data, "green");
        $("#btnSearch_VendaOrcamento").trigger("click");
        @if($orcamento->modo_pdv === 1)
        newWindow = window.open("/fiscal/danfe" + '?nf_id=' + response.nf_id, "_blank");

        $(newWindow).ready(function() {
                        setTimeout(function() {
                        newWindow.print();
                        }, 1000);
                        setTimeout(function() {
                        newWindow.close();
                        window.location.href = "{{ route('vendas.index') }}";
                        }, 8000);
                        });
        @else
        window.location.href = "{{ route('vendas.index') }}";
        window.open("/fiscal/danfe" + '?nf_id=' + response.nf_id, "_blank");
        @endif

        $("#dialogOrcamentoFinalizarPosVenda").remove();
        //ChangeMenu(this,  "{{ route('vendas.orcamento.finalizar.store_faturar') }}");
        }
        if (response.status === "NOK") {
            removeWait(obj.context);
            setToast(response.data, "red");
        //ChangeMenu(this,  "{{ route('vendas.orcamento.finalizar.oque_fazer') }}");
        } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.data);
        }
    }).fail(function(jqXHR,errorStatus, thrownError){
        removeWait(obj.context);
    });
}

</script>
