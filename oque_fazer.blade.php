<div class="ui form" id="content_oque_fazer" data-backdrop="static">
 
 <div class="ui segments m-t-0 m-b-0" style="margin-top: 8px;">

        <div class="ui segment">
            <h3 class="color-green">
           <center> VENDA FINALIZADA COM SUCESSO!</center>
                <span class="font-size-14 color-gray bold-none"></span>
            </h3>
        </div>
    </div>

    <div class="ui segments m-t-0 m-b-0" style="margin-top: 8px;">

        <div class="ui segment">
            <h3 class="color-black-light">
                <center>&nbsp;O QUE DESEJA FAZER?</center>
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
            <a href="" class="ui link fluid card" id="btnFaturarOrcamento">
                <div class="content">
                    <i class="right floated fal fa fa-check color-green font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [1]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                        FATURAR
                    </div>
                    <div class="description">
                    </div>
                </div>
                <div class="extra content">
                </div>
            </a>
    </div>

    <div class="column">
            <a href="" target="_blank" class="ui link fluid card" id="btnImprimirOrcamento">
                <div class="content">
                    <i class="right floated fal fa fa-print color-black font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [2]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                        IMPRIMIR ORÇAMENTO
                    </div>
                    <div class="description">
                    </div>
                </div>
                <div class="extra content">
                </div>
            </a>
    </div>

    @if($aparece > 0 && $tem_conta > 0)
    <div class="column">
            <a href="{{ route('vendas.index') }}" class="ui link fluid card" id="btnDadosTransferencias">
                <div class="content">
                    <i class="right floated fal fa fa-university color-orange font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [3]</span>&nbsp;
                    </div>
                    <div class="meta color-black-light">
                        DADOS TRANSFERÊNCIA
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

    @else

    <div class="column">
            <a href="{{ route('vendas.index') }}" class="ui link fluid card" id="btnSairFaturamento">
                <div class="content">
                    <i class="right floated fal fa fa-times color-red font-size-30"></i>

                    <div class="header color-black-light font-size-25">
                        <span class="color-black-light font-size-16">AT [3]</span>&nbsp;
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
    @endif

    @if($aparece > 0 && $tem_conta > 0)
    <div class="column">
           
    </div>

    <div class="column">
           
    </div>
    @endif
        
    <div class="content">
        <div class="ui segments m-t-0 m-b-0" style="margin-top: 8px;">
            <div class="ui segment">
                <h3 class="color-black">
            <center> &nbsp;TROCO R$ {{number_format($orcamento->valor_troco,2,',','.') }} </center>
                    <span class="font-size-14 color-gray bold-none"></span>
                </h3>
            </div>
        </div>
    </div>


    <br></br>

    <input type="hidden" id="id_orcamento" name="id_orcamento" value="{{ $orcamento->id }}">
    <input type="hidden" id="tipo_movimento_id" name="tipo_movimento_id" value="1">

</div>

<script type="text/javascript">

var ctxParametroContent = "#content_oque_fazer";    

var ChangeMenu = function(el,route){
            $(ctxParametroContent).addClass("loading");
            //$(".item", ctxParametroMenu).removeClass("active");
            //$(el).addClass("active");
            $.getJSON(route ,null, function(response){
                $(ctxParametroContent).empty().html(response.data).removeClass("loading");
            });
}


    @if($aparece === 1 && $tem_conta > 0)
    document.onkeydown=function(e){
        if (e.which == 97) {
            $("#btnFaturarOrcamento").click();
        } else if (e.which == 98){
            $("#btnImprimirOrcamento").click();
        } else if (e.which == 99){
            $("#btnDadosTransferencias").click();
        } else if (e.which == 100){
            btnDadosTransferencias
            $("#btnSairFaturamento").click();
        }
    }
    @else
        document.onkeydown=function(e){
        if (e.which == 97) {
            $("#btnFaturarOrcamento").click();
        } else if (e.which == 98){
            $("#btnImprimirOrcamento").click();
        } else if (e.which == 99){
            $("#btnSairFaturamento").click();
        }
    }
    @endif



    $("#btnFaturarOrcamento").click(function (e) {
            e.preventDefault();
            faturar_orcamento({
            "context": "#content_oque_fazer",
            "route": "{{ route('vendas.orcamento.finalizar.store_faturar') }}",
            "method": "post",
            "data": {
                id_orcamento: $("#id_orcamento", "#content_oque_fazer").val(),
                tipo_movimento_id: $("#tipo_movimento_id", "#content_oque_fazer").val()
            } 
    })}); 
    
    $("#btnImprimirOrcamento").click(function (e) {
            e.preventDefault();
            @if($orcamento->modo_pdv === 1)
            newWindow = window.open('{{ route('vendas.report.cupom',['orcamento_id'=>$orcamento->id]) }}', '_blank');
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
            newWindow = window.open('{{ route('vendas.report.orcamento', ['orcamento_id'=>$orcamento->id]) }}', '_blank');
            @endif
    }); 

    $("#btnSairFaturamento").click(function (e) {
            e.preventDefault();
            window.location.href = "{{ route('vendas.index') }}";
    });
    
    $("#btnDadosTransferencias").click(function (e) {
            e.preventDefault();

           @if($orcamento->modo_pdv === 1)

                        //imprime e fecha a nova janela quando estiver carregada 
                       newWindow = window.open('{{ route('vendas.report.imprimir_transferencia',['orcamento_id'=>$orcamento->id]) }}');

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
                        newWindow = window.open('{{ route('vendas.report.imprimir_transferencia',['orcamento_id'=>$orcamento->id]) }}');
                        window.location.href = "{{ route('vendas.index') }}";
                        @endif
    });

 function faturar_orcamento(obj){
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
        ChangeMenu(this,  "{{ route('vendas.orcamento.finalizar.clicou_faturou', ['orcamento'=>$orcamento->id]) }}");
        }
        if (response.status === "NOK") {
            removeWait(obj.context);
            setToast(response.data, "red");
        (this, "{{ route('vendas.orcamento.finalizar.oque_fazer') }}");
        } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.data);
        }
    }).fail(function(jqXHR,errorStatus, thrownError){
        removeWait(obj.context);
    });
}
    
</script>
