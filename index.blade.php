@extends('layouts.app')

@section('content')

<div class="ui grid">

    <div class="sixteen wide column">

        <div class="ui segments" style="margin-top: 8px;">

            <div class="ui segment">
                <h3 class="color-red">
                    <i class="far fa-cash-register"></i>&nbsp;VENDAS
                    <span class="font-size-14 color-gray bold-none"></span>
                </h3>
            </div>

            <div class="ui secondary segment p-b-0 p-t-5" style="background-color: #E9EBEE; min-height: 1000px;">

                <div class="ui un-shadow p-4 m-b-0 segment">
                    <button type="button" id="btnCreate_VendaOrcamento" class="ui-button ui-widget ui-corner-all v-align">
                        <i class="far fa-plus"></i>&nbsp;Novo Orçamento [F2]
                    </button>
                </div>

                <div class="ui grid segment grey un-shadow p-0 m-t-8 m-b-0">

                    <div class="row p-t-8 p-b-4">

                        <div class="one wide column" style="padding-left: 3px !important;">
                            <div class="field">
                                <label class="m-b-0 color-black-light" for="inputSearchOrc_Id"><i class="fal fa-filter"></i>&nbsp;Id</label>
                                <div class="ui small input">
                                    <input type="text" id="inputSearchOrc_Id" onkeyup="only_number(this)" autocomplete="off" class="w100-per">
                                </div>
                            </div>
                        </div>

                        <div class="three wide column p-l-0">
                            <div class="field">
                                <label class="m-b-0 color-black-light" for="inputSearchOrc_NomePessoa"><i class="fal fa-filter"></i>&nbsp;Pessoa</label>
                                <div class="ui small input w100-per">
                                    <input type="text" id="inputSearchOrc_NomePessoa" autocomplete="off" placeholder="nome, cpf, cnpj...">
                                </div>
                            </div>
                        </div>

                        <div class="two wide column p-l-0">
                            <div class="field">
                                <label class="m-b-0 color-black-light m-t-1" for="inputSearchOrc_TipoDate"><i class="fal fa-filter"></i>&nbsp;Por data</label>
                                <div class="ui small input w100-per m-t-2">
                                    <select id="inputSearchOrc_TipoDate" class="w100-per">
                                        <option value="CADASTRO">Cadastro</option>
                                        <option value="FATURADO">Faturado</option>
                                        <option value="ESTORNADO">Estornado</option>
                                        <option value="CANCELADO">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="two wide column p-l-0">
                            <div class="field">
                                <label class="m-b-0 color-black-light m-t-1" for="inputSearchOrc_Date1"><i class="fal fa-filter"></i>&nbsp;De</label>
                                <div class="ui left icon input small">
                                    <i class="calendar alternate outline icon"></i>
                                    <input type="text" id="inputSearchOrc_Date1" autocomplete="off" style="width: 100%;">
                                </div>
                            </div>
                        </div>

                        <div class="two wide column p-l-0">
                            <div class="field">
                                <label class="m-b-0 color-black-light m-t-1" for="inputSearchOrc_Date2"><i class="fal fa-filter"></i>&nbsp;até</label>
                                <div class="ui left icon input small">
                                    <i class="calendar alternate outline icon"></i>
                                    <input type="text" id="inputSearchOrc_Date2" autocomplete="off" style="width: 100%;">
                                </div>
                            </div>
                        </div>

                        <div class="two wide column p-l-0">
                            <div class="field">
                                <label class="m-b-0 color-black-light m-t-1" for="inputSearchOrc_VendedorId"><i class="fal fa-filter"></i>&nbsp;Vendedor</label>
                                <div class="ui small input w100-per m-t-2">
                                    <select id="inputSearchOrc_VendedorId" class="w100-per"></select>
                                </div>
                            </div>
                        </div>

                        <div class="two wide column p-l-0">
                            <div class="field">
                                <label class="m-b-0 color-black-light m-t-1" for="inputSearchOrc_Status"><i class="fal fa-filter"></i>&nbsp;Status</label>
                                <div class="ui small input w100-per m-t-2">
                                    <select id="inputSearchOrc_Status" class="w100-per">
                                        <option value="">Todos</option>
                                        <option value="ABERTO">Aberto</option>
                                        <option value="FATURADO">Faturado</option>
                                        <option value="ESTORNADO">Estornado</option>
                                        <option value="CANCELADO">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="two wide column p-l-0" style="width: 116px !important;">
                            <button type="button" id="btnSearch_VendaOrcamento" data-typeAction="refresh" class="ui-button ui-widget ui-corner-all m-t-22">
                                <i class="search icon"></i>
                            </button>
                            <button type="button" id="btnEraser_VendaOrcamento" class="ui-button ui-widget ui-corner-all m-t-22" data-tooltip="Limpar Filtros" data-position="top center">
                                <i class="fas fa-eraser"></i>
                            </button>
                        </div>

                    </div>

                </div>

                <div class="table-container m-t-8" id="table_VendaOrcamento" style="min-height: 300px;" data-route="{{ route('vendas.orcamento.table') }}"></div>

            </div>

        </div>
    </div>
</div>
@stop

@section('script')

<script type="text/javascript">

    var $filter_orcid = $("#inputSearchOrc_Id");
    var $filter_nomerazao = $("#inputSearchOrc_NomePessoa");
    var $filter_tipodate = $("#inputSearchOrc_TipoDate");
    var $filter_date1 = $("#inputSearchOrc_Date1");
    var $filter_date2 = $("#inputSearchOrc_Date2");
    var $filter_vendedorid = $("#inputSearchOrc_VendedorId");
    var $filter_status = $("#inputSearchOrc_Status");

    $(document).ready(function() {

        
        $("#btnCreate_Inutilizacao").attr("disabled","disabled").addClass("backcolor-disabled");

    });

    $("#inputSearchOrc_TipoDate, #inputSearchOrc_Status").select2({
        placeholder: "",
        allowClear: true
    });

    document.onkeydown=function(e){
    if (e.which == 113)
    $("#btnCreate_VendaOrcamento").trigger("click");
    }

    $filter_tipodate.val(null).trigger("change");

    $("#inputSearchOrc_Date1,#inputSearchOrc_Date2").datepicker(datepickerPtBr()).mask("00/00/0000");

    $("#inputSearchOrc_VendedorId").select2(onSelect2_query({
        url: "{{ route('select2_vendedores') }}",
        iconSelect2: '<i class="far fa-person-carry color-black-light"></i>',
        allowClear: true
    }));

    var orc_page = 1;

    function eraserFilters() {
        $filter_orcid.val("");
        $filter_nomerazao.val("");
        $filter_tipodate.val(null).trigger("change");
        $filter_date1.val("");
        $filter_date2.val("");
        $filter_vendedorid.val(null).trigger("change");
        $filter_status.val(null).trigger("change");
    }

    $("#btnEraser_VendaOrcamento").click(function() {
        eraserFilters();
        orc_page = 1;
        onVendaOrcamentoGetTable(
            orc_page,
            $filter_orcid.val(),
            $filter_nomerazao.val(),
            $filter_tipodate.val(),
            $filter_date1.val().split("/").reverse().join("-"),
            $filter_date2.val().split("/").reverse().join("-"),
            $filter_vendedorid.val(),
            $filter_status.val()
        );
    });
    $(window).scroll(function() {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            orc_page++;
            onVendaOrcamentoGetTable(
                orc_page,
                $filter_orcid.val(),
                $filter_nomerazao.val(),
                $filter_tipodate.val(),
                $filter_date1.val().split("/").reverse().join("-"),
                $filter_date2.val().split("/").reverse().join("-"),
                $filter_vendedorid.val(),
                $filter_status.val()
            );
        }
    });
    $("#inputSearchOrc_Id,#inputSearchOrc_NomePessoa,#inputSearchOrc_Date1,#inputSearchOrc_Date2").keyup(function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            $("#btnSearch_VendaOrcamento").trigger("click");
        }
    });
    $("#btnSearch_VendaOrcamento").click(function() {

        if (!empty($filter_orcid.val()) || !empty($filter_nomerazao.val()) ||
            (!empty($filter_tipodate.val()) && !empty($filter_date1.val()) && !empty($filter_date2.val())) ||
            !empty($filter_vendedorid.val()) || !empty($filter_status.val())) {
            orc_page = null;
        } else {
            orc_page = 1;
        }
        onVendaOrcamentoGetTable(
            null,
            $filter_orcid.val(),
            $filter_nomerazao.val(),
            $filter_tipodate.val(),
            $filter_date1.val().split("/").reverse().join("-"),
            $filter_date2.val().split("/").reverse().join("-"),
            $filter_vendedorid.val(),
            $filter_status.val()
        );
    });

    onVendaOrcamentoGetTable(orc_page);

    $("#btnSearch_VendaOrcamento").trigger("click");

    $("#btnCreate_VendaOrcamento").click(function() {
        onVendaOrcamentoCreate({
            "route_openFormOrcamento": "{{ route('vendas.orcamento.create') }}",
            "route_postFormOrcamento": "{{ route('vendas.orcamento.store') }}",
            "route_openFormFinalizar": "{{ route('vendas.orcamento.finalizar.create') }}",
            "route_postFormFinalizar": "{{ route('vendas.orcamento.finalizar.store') }}",
            "route_openFormDecidir": "{{ route('vendas.orcamento.finalizar.oque_fazer') }}",
            "route_openFormExcluir": "{{ route('vendas.orcamento.excluir_item') }}"
        });
    });





</script>

@stop
