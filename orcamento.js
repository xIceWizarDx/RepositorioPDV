/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./resources/js/venda/orcamento.js ***!
  \*****************************************/
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var orcamento_itens = [];

function onVendaOrcamento(e) {
  var $this = $(e);
  createForm({
    formId: "formCadPessoa",
    formTitle: "Pessoas",
    formWidth: "1000px",
    route: $this.data("route")
  });
}

function onVendaOrcamentoCreate(obj) {
  $(".form-security").addClass('hide');
  var _width = 'auto';
  if (isMobile()) _width = '98%';else _width = "950px";
  var html = '<div id="dialogOrcamentoCreate" title="Novo Orçamento"></div>';
  $(html).dialog({
    resizable: false,
    width: _width,
    minHeight: "400",
    modal: true,
    closeOnEscape: false,
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    create: function create(e, ui) {
      $("#btnFinalizarOrcamento").button("disable");
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: obj.route_openFormOrcamento,
        dataType: "json",
        statusCode: {
          401: function _() {
            window.location.href = base_url("/login");
          }
        },
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoCreate");
        }
      }).done(function (response) {
        removeWait("#dialogOrcamentoCreate");
        $("#btnFinalizarOrcamento").button("enable");

        if (response.status === "OK") {
          $("#dialogOrcamentoCreate").empty().html(response.form).transition('scale in');
          $("#btnAction1").button("enable");
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoCreate").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoCreate").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoCreate").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        createFormModalBug("Ops! " + ajaxOptions);
      });
    },
    buttons: [{
      id: 'btnFinalizarOrcamento',
      text: "F4 Finalizar",
      click: function click() {
        var produto_id = $("#produto_id", "#formOrcamentoCreate").val();

        if (!empty(produto_id)) {
          createFormModalError('Existe um produto selecionado, clique em adicionar ou no (x) para limpar o campo');
          return false;
        }

        $.ajax({
          url: obj.route_postFormOrcamento,
          method: "post",
          dataType: "json",
          data: {
            orcamento_id: $("#orcamento_id", "#formOrcamentoCreate").val(),
            cliente_id: $("#cliente_idOrcamento", "#formOrcamentoCreate").val(),
            comprador: $("#comprador", "#formOrcamentoCreate").val(),
            vendedor_id: $("#vendedor_id", "#formOrcamentoCreate").val(),
            preco_item: $("#preco_item", "#formOrcamentoCreate").val(),
            orcamento_itens: orcamento_itens
          },
          beforeSend: function beforeSend() {
            $("#btnFinalizarOrcamento").button("disable");
            wait_naja("#formOrcamentoCreate");
          }
        }).done(function (response) {
          removeWait("#formOrcamentoCreate");
          $("#btnFinalizarOrcamento").button("enable");
          removeValidationAlert("#formOrcamentoCreate");

          if (response.status === "OK") {
            var insert_id = parseInt(response.insert_id);
            $("#orcamento_id", "#formOrcamentoCreate").val(insert_id); //-----------------------
            //  TELA DE FINALIZACAO
            //-----------------------

            obj.insert_id = insert_id;
            onVendaOrcamentoFinalizar(obj);
          } else if (response.status === "NOK") {
            addValidationAlert(response.message, "#formOrcamentoCreate");
          } else if (response.status === "NOK_2") {
            createFormModalError(response.message);
          } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.message);
          } else {
            createFormModalError("Error desconhecido!");
          }
        }).fail(function (jqXHR, ajaxOptions, thrownError) {
          removeWait("#formOrcamentoCreate");
          createFormModalBug("Ops! " + thrownError);
        });
      }
    }, {
      text: "Cancelar",
      click: function click() {
        $("#dialogOrcamentoCreate").dialog("close");
      }
    }],
    close: function close(e, ui) {
      orcamento_itens = [];
      $("#dialogOrcamentoCreate").remove();
    }
  });
}

function onVendaOrcamentoFinalizar(obj) {
  $(".form-security").addClass('hide');
  var _width = 'auto';
  if (isMobile()) _width = '98%';else _width = "950px";
  var htmlDialog = '<div id="dialogOrcamentoFinalizar" title="Finalizar Orçamento | Nro Orc ' + obj.insert_id + '"></div>';
  $(htmlDialog).dialog({
    resizable: false,
    width: _width,
    modal: true,
    minHeight: "400",
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    create: function create(e, ui) {
      $("#btnConcluirOrcamento").button("disable");
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: obj.route_openFormFinalizar,
        dataType: "json",
        statusCode: {
          401: function _() {
            window.location.href = base_url("/login");
          }
        },
        data: {
          orcamento_id: obj.insert_id
        },
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoFinalizar");
        }
      }).done(function (response) {
        $("#btnConcluirOrcamento").button("enable");
        removeWait("#dialogOrcamentoFinalizar");

        if (response.status === "OK") {
          $("#dialogOrcamentoFinalizar").html(response.form).transition('scale in');
          $("#btnAction1").button("enable");
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoFinalizar").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoFinalizar").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoFinalizar").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        $("#dialogOrcamentoFinalizar").dialog("close");
        createFormModalBug(thrownError);
      });
    },
    buttons: [{
      id: 'btnConcluirOrcamento',
      text: "Concluir Orçamento",
      click: function click() {
        var orcamento_id = obj.insert_id;
        var cliente_id = $("#cliente_id", "#orcamentoFinalizar").val();
        var condicao_pagamento = $("#condicao_pagamento", "#orcamentoFinalizar").val();
        var forma_pagamento = $("#forma_pagamento", "#orcamentoFinalizar").val();
        var valor_entrada = $("#entrada", "#orcamentoFinalizar").val();
        var centavo_ultima_parcela = $("#centavo_ultima_parcela", "#orcamentoFinalizar").is(":checked");
        var desconto_valor = $("#desconto_valor", "#orcamentoFinalizar").val();
        var desconto_porc = $("#desconto_porc", "#orcamentoFinalizar").val();
        var preco_item = $("#preco_item", "#formOrcamentoCreate").val();
        var preco_item2 = $("#preco_item", "#formOrcamentoEdit").val();
        if (centavo_ultima_parcela) centavo_ultima_parcela = 1;else centavo_ultima_parcela = 0;

        if (preco_item === 'VISTA' && condicao_pagamento.length > 1) {
          createFormModalError("Seu orçamento foi feito no tipo Á VISTA, e você não pode inserir parcelamentos no pagamento.");
          return false;
        }

        if (preco_item === 'PRAZO' && condicao_pagamento.length === 1) {
          createFormModalError("Seu orçamento foi feito no tipo A PRAZO, você não pode inserir pagamento á vista na conclusão.");
          return false;
        }

        if (preco_item2 === 'VISTA' && condicao_pagamento.length > 1) {
          createFormModalError("Seu orçamento foi feito no tipo Á VISTA, e você não pode inserir parcelamentos no pagamento.");
          return false;
        }

        if (preco_item2 === 'PRAZO' && condicao_pagamento.length === 1) {
          createFormModalError("Seu orçamento foi feito no tipo A PRAZO, você não pode inserir pagamento á vista na conclusão.");
          return false;
        }

        $.ajax({
          url: obj.route_postFormFinalizar,
          method: "post",
          dataType: "json",
          data: {
            orcamento_id: orcamento_id,

            /*cliente_id: cliente_id,*/
            condicao_pagamento: condicao_pagamento,
            forma_pagamento: forma_pagamento,

            /*valor_entrada: valor_entrada,*/
            centavo_ultima_parcela: centavo_ultima_parcela,
            desconto_valor: desconto_valor,
            desconto_porc: desconto_porc,
            orcamento_parcelas: orcamento_parcelas
          },
          beforeSend: function beforeSend() {
            $("#btnConcluirOrcamento").button("disable");
            wait_naja("#orcamentoFinalizar");
          }
        }).done(function (response) {
          removeWait("#orcamentoFinalizar");
          removeValidationAlert("#orcamentoFinalizar");
          $("#btnConcluirOrcamento").button("enable");

          if (response.status === "OK") {
            setToast(response.message, "green");
            $("#dialogOrcamentoFinalizar").dialog("close");
            $("#dialogOrcamentoCreate").dialog("close");
            $("#dialogOrcamentoEdit").dialog("close");
            $("#btnSearch_VendaOrcamento").trigger("click");
          } else if (response.status === "NOK") {
            if (_typeof(response.message) === "object") addValidationAlert(response.message, "#orcamentoFinalizar");else createFormModalError(response.message);
          } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.message);
          } else {
            createFormModalBug("Error desconhecido!");
          }
        }).fail(function (jqXHR, textStatus, errorThrown) {
          removeWait("#dialogOrcamentoFinalizar");
          createFormModalBug(textStatus);
          $("#btnConcluirOrcamento").button("enable");
        });
      }
    }, {
      text: "Cancelar",
      click: function click() {
        $("#dialogOrcamentoFinalizar").dialog("close");
      }
    }],
    close: function close(e, ui) {
      $("#dialogOrcamentoFinalizar").remove();
    }
  });
}

function onVendaOrcamentoEdit(e) {
  $(".form-security").addClass('hide');
  e.preventDefault();
  e.stopImmediatePropagation();
  var $this = $(this);
  var _width = null;
  if (isMobile()) _width = '98%';else _width = "950px";
  var orcamento_id = $this.data('id');
  var html = '<div id="dialogOrcamentoEdit" title="Editar Orçamento | Nro Orc ' + orcamento_id + '"></div>';
  $(html).dialog({
    resizable: false,
    width: _width,
    minHeight: "400",
    modal: true,
    closeOnEscape: false,
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    create: function create(e, ui) {
      $("#btnFinalizarOrcamento").button("disable");
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: $this.attr("href"),
        dataType: "json",
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoEdit");
        }
      }).done(function (response) {
        removeWait("#dialogOrcamentoEdit");
        $("#btnFinalizarOrcamento").button("enable");

        if (response.status === "OK") {
          $("#dialogOrcamentoEdit").empty().html(response.form).transition('scale in');

          if (response.itens.length > 0) {
            orcamento_itens = [];
            response.itens.forEach(function (v, i) {
              var itens = {
                _id: "",
                "produto_id": v.produto_id,
                "produto_text": v.produto_text,
                "quantidade": v.quantidade.toString(),
                "preco_vista": v.preco_vista,
                "preco_prazo": v.preco_prazo
              };
              orcamento_itens.push(itens);
            }); // recalcula os ids

            var id = 1;
            orcamento_itens.forEach(function (v, i) {
              v._id = id;
              id++;
            });
            onVendaOrcamentoMontaTableItens();
          } else {
            createFormModalError('Nenhum item encontrado para este orçamento');
          }

          $("#btnAction1").button("enable");
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoEdit").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoEdit").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoEdit").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        $("#btnFinalizarOrcamento").button("enable");
        createFormModalBug("Ops! " + thrownError);
      });
    },
    buttons: [{
      id: 'btnFinalizarOrcamento',
      text: "F4 Atualizar e Finalizar",
      click: function click() {
        var produto_id = $("#produto_id", "#formOrcamentoEdit").val();

        if (!empty(produto_id)) {
          createFormModalError('Existe um produto selecionado, clique em adicionar ou no (x) para limpar o campo.');
          return false;
        }

        $.ajax({
          url: $this.data("route_put"),
          method: "put",
          dataType: "json",
          data: {
            orcamento_id: $("#orcamento_id", "#formOrcamentoEdit").val(),
            cliente_id: $("#cliente_idOrcamento", "#formOrcamentoEdit").val(),
            comprador: $("#comprador", "#formOrcamentoEdit").val(),
            vendedor_id: $("#vendedor_id", "#formOrcamentoEdit").val(),
            preco_item: $("#preco_item", "#formOrcamentoEdit").val(),
            orcamento_itens: orcamento_itens
          },
          beforeSend: function beforeSend() {
            $("#btnFinalizarOrcamento").button("disable");
            wait_naja("#formOrcamentoEdit");
          }
        }).done(function (response) {
          removeWait("#formOrcamentoEdit");
          $("#btnFinalizarOrcamento").button("enable");
          removeValidationAlert("#formOrcamentoEdit");

          if (response.status === "OK") {
            onVendaOrcamentoFinalizar({
              "insert_id": parseInt(response.insert_id),
              "route_openFormFinalizar": $this.data("open_form_finalizar"),
              "route_postFormFinalizar": $this.data("post_form_finalizar")
            });
          } else if (response.status === "NOK") {
            addValidationAlert(response.message, "#formOrcamentoEdit");
          } else if (response.status === "NOK_2") {
            createFormModalError(response.message);
          } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.message);
          } else {
            createFormModalError("Error desconhecido!");
          }
        }).fail(function (jqXHR, ajaxOptions, thrownError) {
          removeWait("#formOrcamentoEdit");
          createFormModalBug("Ops! " + thrownError);
        });
      }
    }, {
      text: "Cancelar",
      click: function click() {
        $("#dialogOrcamentoEdit").dialog("close");
      }
    }],
    close: function close(e, ui) {
      orcamento_itens = [];
      $("#dialogOrcamentoEdit").remove();
    }
  });
}

function onVendaOrcamentoGetTable(page, orcamento_id, nome_razao, tipo_date, date1, date2, vendedor_id, status) {
  var $ctx_tableVendaOrc = $("#table_VendaOrcamento");
  if (empty(page)) page = 1;
  if (empty(orcamento_id)) orcamento_id = '';
  if (empty(nome_razao)) nome_razao = '';
  if (empty(tipo_date)) tipo_date = '';
  if (empty(date1)) date1 = '';
  if (empty(date2)) date2 = '';
  if (empty(vendedor_id)) vendedor_id = '';
  if (empty(status)) status = '';
  $.ajax({
    url: $ctx_tableVendaOrc.data("route") + "?page=" + page + "&orcamento_id=" + orcamento_id + "&nome_razao=" + nome_razao + "&tipo_date=" + tipo_date + "&date1=" + date1 + "&date2=" + date2 + "&vendedor_id=" + vendedor_id + "&status=" + status,
    type: "get",
    dataType: "html",
    statusCode: {
      401: function _() {
        window.location.href = base_url("/login");
      }
    },
    beforeSend: function beforeSend() {
      /*if ($("#table_VendaOrcamento>table").length <= 0)
          wait_naja("#table_VendaOrcamento");
      else
          wait_naja("#table_VendaOrcamento>table");*/
      $("#btnSearch_VendaOrcamento").html('<i class="spinner loading icon"></i>');
      $ctx_tableVendaOrc.append('<div class="ui active loader"></div>');
    }
  }).done(function (response) {
    $("#btnSearch_VendaOrcamento").html('<i class="search icon"></i>');
    $ctx_tableVendaOrc.find('.ui.active.loader').remove();
    /*removeWait("#table_VendaOrcamento");*/

    if (empty(response)) {
      $ctx_tableVendaOrc.find("table>tfoot").html('<td colspan="8" class="td-center">Não existe mais registros para serem mostrados...</td>');
      return;
    }

    if (page > 1) {
      $ctx_tableVendaOrc.find("table").find("tbody").append(response);
    } else {
      $ctx_tableVendaOrc.empty().html(response);
    }

    $('.title').popup();
    $(".btnEdit", $ctx_tableVendaOrc).click(onVendaOrcamentoEdit);
    $(".btnFaturarOrc", $ctx_tableVendaOrc).click(onVendaOrcamentoFaturar);
    $(".btnCancelarOrc", $ctx_tableVendaOrc).click(onVendaOrcamentoCancelar);
    $(".btnEstornarOrc", $ctx_tableVendaOrc).click(onVendaOrcamentoEstornar);
    $(".btnShowFinalizacaoOrc", $ctx_tableVendaOrc).click(onVendaShowFinalizacao);
    $(".btnFiscalGerarXML", $ctx_tableVendaOrc).click(onFiscalGerarXML);
    $(".btnFiscalCancelarNF", $ctx_tableVendaOrc).click(onFiscalCancelarNF);
    $(".dropdown").dropdown();
  }).fail(function (jqXHR, ajaxOptions, thrownError) {
    createFormModalBug("Ops! " + thrownError);
  });
}

function onVendaOrcamentoExcluirItemTable() {
  var id = parseInt($(this).data("id"));
  orcamento_itens.forEach(function (v, i) {
    if (v._id === id) orcamento_itens.splice(i, 1);
  });
  onVendaOrcamentoMontaTableItens();
  event.stopImmediatePropagation();
}

function onVendaOrcamentoMontaTableItens() {
  var opcao = document.getElementById("preco_item").value;

  if (opcao === 'PRAZO') {
    document.getElementById("tableOrcamentoItens").style.display = "block";
    document.getElementById("tableOrcamentoItensvista").style.display = "none";
  } else {
    document.getElementById("tableOrcamentoItens").style.display = "none";
    document.getElementById("tableOrcamentoItensvista").style.display = "block";
  }

  $("#tableOrcamentoItens").html("");

  if (orcamento_itens.length > 0) {
    var totalGeral = 0;
    var htmlTable = '';
    htmlTable += '<table class="ui compact unstackable celled striped selectable table">';
    htmlTable += '    <thead>';
    htmlTable += '        <tr>';
    htmlTable += '            <th>&nbsp;</th>';
    htmlTable += '            <th>Produto</th>';
    htmlTable += '            <th style="width: 80px;">Qtde</th>';
    htmlTable += '            <th style="width: 150px;">Preço - a prazo</th>';
    htmlTable += '            <th style="width: 180px;">SubTotal</th>';
    htmlTable += '        </tr>';
    htmlTable += '    </thead>';
    htmlTable += '    <tbody>';
    orcamento_itens.forEach(function (v, i) {
      var subTotal = toFloat(v.quantidade) * v.preco_prazo;
      htmlTable += '<tr class="">';
      htmlTable += '    <td style="width: 50px !important;" class="td-v-m">';
      htmlTable += '        <div class="ui icon basic mini buttons">';
      htmlTable += '            <div class="ui icon bottom left pointing dropdown button" tabindex="-1" data-tooltip="Remover" data-position="right center">';
      htmlTable += '                <i class="far fa-trash-alt font-size-14 color-red"></i>';
      htmlTable += '                <div class="menu transition hidden" tabindex="-1">';
      htmlTable += '                    <div class="item">';
      htmlTable += '                        <p class="bold color-black-light">Confirma a remoção ?</p>';
      htmlTable += '                        <a class="ui left attached button">Não</a>';
      htmlTable += '                        <a class="ui red right attached button btnRemoverItemOrc" data-id="' + v._id + '">';
      htmlTable += '                            <span class="color-red bold">Sim</span>';
      htmlTable += '                        </a>';
      htmlTable += '                    </div>';
      htmlTable += '                </div>';
      htmlTable += '            </div>';
      htmlTable += '        </div>';
      htmlTable += '    </td>';
      htmlTable += '    <td class="td-v-m">';
      htmlTable += '        <div class="bold color-black-light v-align">';
      htmlTable += '            <i class="fad fa-box-open font-size-20 color-beige"></i>&nbsp;&nbsp;' + v.produto_text;
      htmlTable += '        </div>';
      htmlTable += '    </td>';
      htmlTable += '    <td class="td-v-m">' + v.quantidade + '</td>';
      htmlTable += '    <td class="td-v-m td-right backcolor-yellow bold">';
      htmlTable += '        <i class="fal fa-money-bill-alt font-size-19" style="float: left; color: #CE9B00;"></i>';
      htmlTable += '        ' + format_money(v.preco_prazo);
      htmlTable += '    </td>';
      htmlTable += '    <td class="td-v-m td-right backcolor-gray bold">';
      htmlTable += '        <i class="fal fa-money-bill-alt font-size-19 color-gray" style="float: left;"></i>';
      htmlTable += '        ' + format_money(subTotal);
      htmlTable += '    </td>';
      htmlTable += '</tr>';
      totalGeral += subTotal;
    });
    htmlTable += '    </tbody>';
    htmlTable += '    <tfoot>';
    htmlTable += '        <tr style="height: 50px;">';
    htmlTable += '            <td colspan="4">&nbsp;</td>';
    htmlTable += '            <td class="td-v-m td-right backcolor-gray bold">';
    htmlTable += '                <i class="fal fa-money-bill-alt font-size-19 color-gray" style="float: left;"></i>';
    htmlTable += '                <span class="small color-gray">Total R$</span>&nbsp;&nbsp;';
    htmlTable += '                <span class="color-green bold font-size-20">' + format_money(totalGeral) + '</span>';
    htmlTable += '            </td>';
    htmlTable += '        </tr>';
    htmlTable += '    </tfoot>';
    htmlTable += '</table>';
    $("#tableOrcamentoItens").html(htmlTable);
    $(".btnRemoverItemOrc").click(onVendaOrcamentoExcluirItemTable);
  } else {
    var html = '';
    html += '<div class="ui segment" style="background-color: #F6F6F6; border: 1px solid #C5C5C5; box-shadow: unset; margin-top: 10px;">';
    html += '    <h3 class="color-black-light" style="text-align: center;">Nenhum item adicionado&nbsp;<i class="far fa-frown"></i></h3>';
    html += '</div>';
    $("#tableOrcamentoItens").html(html);
  }

  $('.ui.dropdown').dropdown();
  $("#tableOrcamentoItensvista").html("");

  if (orcamento_itens.length > 0) {
    var _totalGeral = 0;
    var _htmlTable = '';
    _htmlTable += '<table class="ui compact unstackable celled striped selectable table">';
    _htmlTable += '    <thead>';
    _htmlTable += '        <tr>';
    _htmlTable += '            <th>&nbsp;</th>';
    _htmlTable += '            <th>Produto</th>';
    _htmlTable += '            <th style="width: 80px;">Qtde</th>';
    _htmlTable += '            <th style="width: 150px;">Preço - á vista</th>';
    _htmlTable += '            <th style="width: 180px;">SubTotal</th>';
    _htmlTable += '        </tr>';
    _htmlTable += '    </thead>';
    _htmlTable += '    <tbody>';
    orcamento_itens.forEach(function (v, i) {
      var subTotal = toFloat(v.quantidade) * v.preco_vista;
      _htmlTable += '<tr class="">';
      _htmlTable += '    <td style="width: 50px !important;" class="td-v-m">';
      _htmlTable += '        <div class="ui icon basic mini buttons">';
      _htmlTable += '            <div class="ui icon bottom left pointing dropdown button" tabindex="-1" data-tooltip="Remover" data-position="right center">';
      _htmlTable += '                <i class="far fa-trash-alt font-size-14 color-red"></i>';
      _htmlTable += '                <div class="menu transition hidden" tabindex="-1">';
      _htmlTable += '                    <div class="item">';
      _htmlTable += '                        <p class="bold color-black-light">Confirma a remoção ?</p>';
      _htmlTable += '                        <a class="ui left attached button">Não</a>';
      _htmlTable += '                        <a class="ui red right attached button btnRemoverItemOrc" data-id="' + v._id + '">';
      _htmlTable += '                            <span class="color-red bold">Sim</span>';
      _htmlTable += '                        </a>';
      _htmlTable += '                    </div>';
      _htmlTable += '                </div>';
      _htmlTable += '            </div>';
      _htmlTable += '        </div>';
      _htmlTable += '    </td>';
      _htmlTable += '    <td class="td-v-m">';
      _htmlTable += '        <div class="bold color-black-light v-align">';
      _htmlTable += '            <i class="fad fa-box-open font-size-20 color-beige"></i>&nbsp;&nbsp;' + v.produto_text;
      _htmlTable += '        </div>';
      _htmlTable += '    </td>';
      _htmlTable += '    <td class="td-v-m">' + v.quantidade + '</td>';
      _htmlTable += '    <td class="td-v-m td-right backcolor-roxo bold">';
      _htmlTable += '        <i class="fal fa-money-bill-alt font-size-19" style="float: left; color: #4f0e5c;"></i>';
      _htmlTable += '        ' + format_money(v.preco_vista);
      _htmlTable += '    </td>';
      _htmlTable += '    <td class="td-v-m td-right backcolor-gray bold">';
      _htmlTable += '        <i class="fal fa-money-bill-alt font-size-19 color-gray" style="float: left;"></i>';
      _htmlTable += '        ' + format_money(subTotal);
      _htmlTable += '    </td>';
      _htmlTable += '</tr>';
      _totalGeral += subTotal;
    });
    _htmlTable += '    </tbody>';
    _htmlTable += '    <tfoot>';
    _htmlTable += '        <tr style="height: 50px;">';
    _htmlTable += '            <td colspan="4">&nbsp;</td>';
    _htmlTable += '            <td class="td-v-m td-right backcolor-gray bold">';
    _htmlTable += '                <i class="fal fa-money-bill-alt font-size-19 color-gray" style="float: left;"></i>';
    _htmlTable += '                <span class="small color-gray">Total R$</span>&nbsp;&nbsp;';
    _htmlTable += '                <span class="color-green bold font-size-20">' + format_money(_totalGeral) + '</span>';
    _htmlTable += '            </td>';
    _htmlTable += '        </tr>';
    _htmlTable += '    </tfoot>';
    _htmlTable += '</table>';
    $("#tableOrcamentoItensvista").html(_htmlTable);
    $(".btnRemoverItemOrc").click(onVendaOrcamentoExcluirItemTable);
  } else {
    var _html = '';
    _html += '<div class="ui segment" style="background-color: #F6F6F6; border: 1px solid #C5C5C5; box-shadow: unset; margin-top: 10px;">';
    _html += '    <h3 class="color-black-light" style="text-align: center;">Nenhum item adicionado&nbsp;<i class="far fa-frown"></i></h3>';
    _html += '</div>';
    $("#tableOrcamentoItensvista").html(_html);
  }

  $('.ui.dropdown').dropdown();
}

function onVendaOrcamentoConfigForm(obj) {
  var $cliente_idOrcamento = $("#cliente_idOrcamento", obj.context);
  var $comprador = $("#comprador", obj.context);
  var $produto_id = $("#produto_id", obj.context);
  var $quantidade = $("#quantidade", obj.context);
  var $cEAN = $("#cEAN", obj.context);
  var $btnAdd = $("#btnAdd", obj.context);
  var $preco_item = $("#preco_item", obj.context);

  function mountItem(response) {
    var _item = {
      _id: "",
      "produto_id": response.id,
      "produto_text": response.descricao,
      "preco_vista": response.preco_vista,
      "preco_prazo": response.preco_prazo,
      "quantidade": $quantidade.val(),
      "preco_item": $preco_item.val()
    };
    orcamento_itens.push(_item); // recalcula os ids

    var _id = 1;
    orcamento_itens.forEach(function (v, i) {
      v._id = _id;
      _id++;
    });
  }

  function onVendaOrcamentoAddItem() {
    var $this = $(this);

    if (empty($produto_id.val())) {
      createFormModalError("Selecione um produto para adicionar.");
      return;
    }

    if (empty($quantidade.val()) || toFloat($quantidade.val()) <= 0) {
      createFormModalError("Quantidade é obrigatório.");
      return;
    }

    $.ajax({
      url: obj.route_getProdutoOfId + $produto_id.val(),
      type: "get",
      dataType: "json",
      beforeSend: function beforeSend() {
        $this.attr("disabled", "disabled").html('<i class="fas fa-cog fa-spin"></i>');
        wait_naja(obj.context);
      }
    }).done(function (response) {
      removeWait(obj.context);
      $this.removeAttr("disabled", "disabled").html('<i class="far fa-cart-plus"></i>&nbsp;<i class="far fa-long-arrow-down"></i>');

      if (parseFloat(response.preco_prazo) <= 0) {
        createFormModalError("Produto com preço a prazo zerado, não é possível adicionar.");
        return false;
      }

      if (parseFloat(response.preco_vista) <= 0) {
        createFormModalError("Produto com preço á vista zerado, não é possível adicionar.");
        return false;
      }

      mountItem(response);
      /*reset components*/

      $quantidade.val("1");
      $produto_id.val(null).trigger("change").select2("open");
      /*remonta a table*/

      onVendaOrcamentoMontaTableItens();
    }).fail(function (jqXHR, ajaxOptions, thrownError) {
      $this.removeAttr("disabled", "disabled").html('<i class="far fa-cart-plus"></i>&nbsp;<i class="far fa-long-arrow-down"></i>');
      removeWait(obj.context);
    });
  }

  function onVendaOrcamentoAddItemOfcEAN(cEAN) {
    $.ajax({
      url: obj.route_getProdutoOfcEAN + cEAN,
      type: "get",
      dataType: "json",
      beforeSend: function beforeSend() {
        wait_naja(obj.context);
      }
    }).done(function (response) {
      removeWait(obj.context);

      if (!$.isEmptyObject(response)) {
        if (parseFloat(response.preco_prazo) <= 0) {
          createFormModalError("Preço do produto esta zerado.");
          return false;
        }

        mountItem(response); //reset components

        $quantidade.val("1");
        $produto_id.val(null).trigger("change").focus();
        $cEAN.val("").focus();
        onVendaOrcamentoMontaTableItens();
      } else {
        //$cEAN.select();
        createFormModalError('Produto não encontrado');
        $cEAN.val("");
      }
    }).fail(function (jqXHR, ajaxOptions, thrownError) {
      removeWait(obj.context);
    });
  }

  $cliente_idOrcamento.select2(onSelect2_pessoas({
    url: obj.route_select2Pessoas,
    allowClear: true
  })).change(function () {
    var id = $(this).val();

    if (id == 1) {
      $comprador.removeAttr("disabled").removeClass("backcolor-gray");
    } else {
      $comprador.attr("disabled", "disabled").addClass("backcolor-gray");
    }
  });
  $("#vendedor_id", obj.context).select2(onSelect2_query({
    url: obj.route_select2Vendedores,
    iconSelect2: '<i class="far fa-person-carry color-black-light"></i>'
  }));
  $produto_id.select2(onSelect2_produtos({
    url: obj.route_select2Produtos,
    iconSelect2: '<i class="fad fa-boxes"></i>',
    allowClear: true
  }));
  $('.select2-selection', obj.context).on('keyup', function (event) {
    if (event.keyCode == 13 && !empty($produto_id.val())) {
      $btnAdd.trigger("click");
    }
  });
  $quantidade.keyup(function (event) {
    if (event.keyCode == 13) {
      $btnAdd.focus();
    }
  });
  $(obj.context).keyup(function (event) {
    if (event.keyCode == 113) {
      $produto_id.select2("open");
    } else if (event.keyCode == 106) {
      $quantidade.focus().select();
    } else if (event.keyCode == 115) {
      $("#btnFinalizarOrcamento").trigger("click");
    }
  });
  $("#btnAdd", obj.context).click(onVendaOrcamentoAddItem);
  $cEAN.keyup(function (event) {
    var $this = $(this);

    if (event.keyCode == 13 && !empty($this.val())) {
      onVendaOrcamentoAddItemOfcEAN($this.val());
    }
  });
  onVendaOrcamentoMontaTableItens();
  $produto_id.select2("focus");
}

function onVendaOrcamentoFaturar(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  $(".form-security").addClass('hide');
  var $this = $(this);
  var _width = 'auto';
  if (isMobile()) _width = '98%';else _width = "600px";
  var html = '<div id="dialogOrcamentoFaturar" title="Faturar Orcamento"></div>';
  $(html).dialog({
    resizable: false,
    width: _width,
    modal: true,
    minHeight: "250",
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    create: function create(e, ui) {
      $("#btnFaturarOrcamento").button("disable");
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: $this.data("route_create"),
        dataType: "json",
        statusCode: {
          401: function _() {
            window.location.href = base_url("/login");
          }
        },
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoFaturar");
        }
      }).done(function (response) {
        removeWait("#dialogOrcamentoFaturar");

        if (response.status === "OK") {
          $("#dialogOrcamentoFaturar").empty().html(response.form).transition('scale in');
          $("#btnFaturarOrcamento").button("enable");
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoFaturar").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoFaturar").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoFaturar").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        $("#dialogOrcamentoFaturar").dialog("close");
        createFormModalBug("Ops! " + thrownError);
      });
    },
    buttons: [{
      id: 'btnFaturarOrcamento',
      text: "Faturar",
      click: function click() {
        $.ajax({
          url: $this.data("route_store"),
          method: "post",
          dataType: "json",
          data: {
            orcamento_id: $("#orcamento_id", "#formFaturarOrcamento").val(),
            tipo_movimento_id: $("#tipo_movimento_id", "#formFaturarOrcamento").val()
          },
          beforeSend: function beforeSend() {
            $("#btnFaturarOrcamento").button("disable");
            wait_naja("#formFaturarOrcamento");
          }
        }).done(function (response) {
          removeWait("#formFaturarOrcamento");
          $("#btnFaturarOrcamento").button("enable");

          if (response.status === "OK") {
            setToast(response.message, "green");
            $("#dialogOrcamentoFaturar").dialog("close");
            $("#btnSearch_VendaOrcamento").trigger("click");
          } else if (response.status === "NOK") {
            createFormModalError(response.message);
          } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.message);
          } else {
            createFormModalError("Error desconhecido!");
          }
        }).fail(function (jqXHR, ajaxOptions, thrownError) {
          removeWait("#formFaturarOrcamento");
          $("#btnFaturarOrcamento").button("enable");
          createFormModalBug("Ops! " + thrownError);
        });
      }
    }, {
      text: "Voltar",
      click: function click() {
        $("#dialogOrcamentoFaturar").dialog("close");
      }
    }],
    close: function close(e, ui) {
      $("#dialogOrcamentoFaturar").remove();
    }
  });
}

function onVendaOrcamentoCancelar(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  $(".form-security").addClass('hide');
  var $this = $(this);
  var html = '<div id="dialogOrcamentoCancelar" title="Cancelar Orcamento"></div>';
  $(html).dialog({
    resizable: false,
    width: "600px",
    modal: true,
    minHeight: "250",
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    create: function create(e, ui) {
      $("#btnCancelarOrcamento").button("disable");
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: $this.data("route_create"),
        dataType: "json",
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoCancelar");
        }
      }).done(function (response) {
        removeWait("#dialogOrcamentoCancelar");

        if (response.status === "OK") {
          $("#dialogOrcamentoCancelar").empty().html(response.view).transition('scale in');
          $("#btnCancelarOrcamento").button("enable");
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoCancelar").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoCancelar").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoCancelar").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        $("#dialogOrcamentoCancelar").dialog("close");
        createFormModalBug("Ops! " + thrownError);
      });
    },
    buttons: [{
      id: 'btnCancelarOrcamento',
      text: "Executar Cancelamento",
      click: function click() {
        $.ajax({
          url: $this.data("route_store"),
          method: "post",
          dataType: "json",
          data: {
            orcamento_id: $("#orcamento_id", "#formCancelarOrcamento").val()
          },
          beforeSend: function beforeSend() {
            $("#btnCancelarOrcamento").button("disable");
            wait_naja("#formCancelarOrcamento");
          }
        }).done(function (response) {
          removeWait("#formCancelarOrcamento");
          $("#btnCancelarOrcamento").button("enable");

          if (response.status === "OK") {
            setToast(response.message, "green");
            $("#dialogOrcamentoCancelar").dialog("close");
            $("#btnSearch_VendaOrcamento").trigger("click");
          } else if (response.status === "NOK") {
            createFormModalError(response.message);
          } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.message);
          } else {
            createFormModalError("Error desconhecido!");
          }
        }).fail(function (jqXHR, ajaxOptions, thrownError) {
          removeWait("#formCancelarOrcamento");
          $("#btnCancelarOrcamento").button("enable");
          createFormModalBug("Ops! " + thrownError);
        });
      }
    }, {
      text: "Voltar",
      click: function click() {
        $("#dialogOrcamentoCancelar").dialog("close");
      }
    }],
    close: function close(e, ui) {
      $("#dialogOrcamentoCancelar").remove();
    }
  });
}

function onVendaOrcamentoEstornar(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  $(".form-security").addClass('hide');
  var $this = $(this);
  var html = '<div id="dialogOrcamentoEstornar" title="Estornar Orcamento"></div>';
  $(html).dialog({
    resizable: false,
    width: "600px",
    modal: true,
    minHeight: "250",
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    create: function create(e, ui) {
      $("#btnEstornarOrcamento").button("disable");
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: $this.data("route_create"),
        dataType: "json",
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoEstornar");
        }
      }).done(function (response) {
        removeWait("#dialogOrcamentoEstornar");

        if (response.status === "OK") {
          $("#dialogOrcamentoEstornar").empty().html(response.view).transition('scale in');
          $("#btnEstornarOrcamento").button("enable");
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoEstornar").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoEstornar").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoEstornar").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        $("#dialogOrcamentoEstornar").dialog("close");
        createFormModalBug("Ops! " + thrownError);
      });
    },
    buttons: [{
      id: 'btnEstornarOrcamento',
      text: "Estornar",
      click: function click() {
        $.ajax({
          url: $this.data("route_store"),
          method: "post",
          dataType: "json",
          data: {
            orcamento_id: $("#orcamento_id", "#formEstornarOrcamento").val()
          },
          beforeSend: function beforeSend() {
            $("#btnEstornarOrcamento").button("disable");
            wait_naja("#formEstornarOrcamento");
          }
        }).done(function (response) {
          removeWait("#formEstornarOrcamento");
          $("#btnEstornarOrcamento").button("enable");

          if (response.status === "OK") {
            setToast(response.message, "green");
            $("#dialogOrcamentoEstornar").dialog("close");
            $("#btnSearch_VendaOrcamento").trigger("click");
          } else if (response.status === "NOK") {
            createFormModalError(response.message);
          } else if (response.status === "EXCEPTION") {
            createFormModalBug(response.message);
          } else {
            createFormModalError("Error desconhecido!");
          }
        }).fail(function (jqXHR, ajaxOptions, thrownError) {
          removeWait("#formEstornarOrcamento");
          $("#btnEstornarOrcamento").button("enable");
          createFormModalBug("Ops! " + thrownError);
        });
      }
    }, {
      text: "Voltar",
      click: function click() {
        $("#dialogOrcamentoEstornar").dialog("close");
      }
    }],
    close: function close(e, ui) {
      $("#dialogOrcamentoEstornar").remove();
    }
  });
}

function onVendaOrcamentoGetTotalVendaHoje(obj) {
  $.get(obj.route, null, function (response) {
    $("#homeTotalVendasHoje").html(number_format(response.message, 2, ",", "."));
  });
}

function onVendaOrcamentoGetVendasMesGrafico(obj) {
  $.ajax({
    url: obj.route,
    method: "get",
    dataType: "json",
    beforeSend: function beforeSend() {
      wait_naja("#cxtChart");
    }
  }).done(function (response) {
    removeWait("#cxtChart");

    if (response.status === "OK") {
      var ctxMyChart = document.getElementById('myChart').getContext('2d');
      var myChart = new Chart(ctxMyChart, {
        type: 'line',
        data: {
          labels: response.meses,
          datasets: [{
            label: 'Total',
            data: response.totais,
            backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'],
            borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    } else {
      setToast("Error ao tentar montar o grafico");
    }
  }).fail(function (jqXHR, ajaxOptions, thrownError) {
    removeWait("#myChart");
    setToast(thrownError);
  });
}

function onVendaShowFinalizacao(e) {
  e.preventDefault();
  e.stopImmediatePropagation();
  $(".form-security").addClass('hide');
  var $this = $(this);
  var _width = 'auto';
  if (isMobile()) _width = '98%';else _width = "600px";
  var html = '<div id="dialogOrcamentoShowFinalizacao" title="Detalhes da finalização"></div>';
  $(html).dialog({
    resizable: false,
    width: _width,
    modal: true,
    minHeight: "250",
    position: {
      my: "center top",
      at: "top+60",
      of: window
    },
    open: function open(e, ui) {
      $.ajax({
        type: "get",
        url: $this.data("route_create"),
        dataType: "json",
        statusCode: {
          401: function _() {
            window.location.href = base_url("/login");
          }
        },
        beforeSend: function beforeSend() {
          wait_naja("#dialogOrcamentoShowFinalizacao");
        }
      }).done(function (response) {
        removeWait("#dialogOrcamentoShowFinalizacao");

        if (response.status === "OK") {
          $("#dialogOrcamentoShowFinalizacao").empty().html(response.form).transition('scale in');
        } else if (response.status === "NOK") {
          $("#dialogOrcamentoShowFinalizacao").dialog("close");
          createFormModalError(response.message);
        } else if (response.status === "EXCEPTION") {
          $("#dialogOrcamentoShowFinalizacao").dialog("close");
          createFormModalBug(response.message);
        } else {
          $("#dialogOrcamentoShowFinalizacao").dialog("close");
          createFormModalBug("Error desconhecido!");
        }
      }).fail(function (jqXHR, ajaxOptions, thrownError) {
        $("#dialogOrcamentoShowFinalizacao").dialog("close");
        createFormModalBug("Ops! " + thrownError);
      });
    },
    buttons: [{
      text: "OK",
      click: function click() {
        $("#dialogOrcamentoShowFinalizacao").dialog("close");
      }
    }],
    close: function close(e, ui) {
      $("#dialogOrcamentoShowFinalizacao").remove();
    }
  });
}
/******/ })()
;