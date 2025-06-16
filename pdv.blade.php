@extends('layouts.app')
@section('title', 'PDV Aprimorado')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
  crossorigin="anonymous">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"
  crossorigin="anonymous">

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
  body.pdv-page,
  .pdv-page-container {
    background: #f8f9fa !important;
    color: #333;
    font-size: .9rem;
  }

  .card-header-red {
    background: #e65562;
    color: #fff;
    font-weight: bold;
    padding: .75rem 1.25rem;
  }

  .card-header-red-darker {
    background: #c82333;
    color: #fff;
    font-weight: bold;
    padding: .75rem 1.25rem;
  }

  .total-display-area {
    background: #f0f0f0;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
  }

  .total-row {
    display: flex;
    justify-content: space-between;
    padding: .25rem 0;
    font-size: .9em;
  }

  .faltam-pagar span:last-child {
    color: #dc3545;
    font-size: 1.4em;
  }

  .total-liquido span:last-child {
    color: #28a745;
    font-size: 1.6em;
  }

  .section-divider {
    border-top: 1px dashed #ccc;
    margin: 1rem 0;
  }

  #toastContainer {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1100;
  }

  #botoesCondicaoPagamento button:focus {
    outline: none;
  }

  .dropdown-menu {
    max-height: 220px;
    overflow-y: auto;
    width: 170%;
  }

  .dropdown-item.active,
  .dropdown-item:active {
    background-color: #007bff;
    color: white;
  }

  .col-left-flex {
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  .col-left-flex>.card:not(:last-child) {
    margin-bottom: 1rem;
  }

  .flex-grow-itens {
    flex-grow: 1;
    overflow-y: auto;
    min-height: 0;
  }

  .col-left-flex>.card:last-child {
    margin-top: auto;
  }

  .itens-venda-body {
    height: 500px;
    overflow-y: auto;
  }

  .parcelas-geradas-body {
    max-height: 150px;
    overflow-y: auto;
  }

  #listaItensVenda tr:nth-child(odd) {
    background-color: #f9f9f9;
  }

  #listaItensVenda tr:nth-child(even) {
    background-color: #ffffff;
  }

  #listaItensVenda tr:hover {
    background-color: #e2e6ea;
  }

  #nenhumItemMsgRow {
    background-color: #f9f9f9;
    height: 40px;
  }

  .input-group-text {
    cursor: pointer;
    background-color: #fff;
    border-left: 0;
  }

  .input-group .form-control {
    border-right: 0;
  }

  #modalBuscaProdutoTable thead th {
    white-space: nowrap;
  }

  #modalBuscaProdutoTable {
    position: relative;
  }

  #modalBuscaProdutoTable tbody tr {
    cursor: pointer;
  }

  #modalBuscaProdutoTable tbody td.select-column,
  #modalBuscaProdutoTable thead th.select-column {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 2;
    width: 130px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    box-shadow: -2px 0 5px -2px rgba(0, 0, 0, 0.1);
  }

  #modalBuscaProdutoTable tbody tr.table-success {
    background-color: #d1e7dd !important;
  }

  #modalBuscaProdutoTable tbody td:nth-child(2) {
    max-width: 400px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  #modalBuscaProdutoTable thead th.price-column {
    white-space: normal;
    line-height: 1.2;
    min-width: 110px;
  }

  .quantidade-control {
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }

  .quantidade-control input.input-quantidade {
    width: 50px;
    text-align: center;
    border-radius: 4px;
    height: 30px;
  }

  .modal-dialog.modal-lg {
    max-width: 1000px;
    width: 90%;
    margin: 1.75rem auto;
  }

  #multiplasFormasListModal .input-group {
    margin-bottom: 8px;
  }

  #multiplasFormasListModal .input-group .form-select {
    max-width: 65%;
  }

  #multiplasFormasListModal .input-group .valor-parcela-input {
    max-width: 35%;
  }

  #valorFaltanteContainer {
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 15px;
  }

  #modalBuscaProduto .modal-content {
    max-width: 95vw;
    width: 100%;
    overflow-x: auto;
  }

  #modalBuscaProdutoTable {
    width: 100%;
    table-layout: auto;
    white-space: nowrap;
  }

  #modalTableParcelasGeradas th.acao-col,
  #modalTableParcelasGeradas td.acao-col {
    display: table-cell;
  }

  #modalTableParcelasGeradas.hide-acao-col th.acao-col,
  #modalTableParcelasGeradas.hide-acao-col td.acao-col {
    display: none;
  }

  .pdv-wrapper #produtoSearchResults.show {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .valor-faltante-zero {
    color: #28a745 !important;
    font-weight: bold;
    text-shadow: 0 0 4px rgba(40, 167, 69, 0.6);
  }

  .modal-backdrop.first-backdrop {
    z-index: 1030 !important;
  }

  #modalMultiplasFormas {
    z-index: 1040 !important;
  }

  .modal-backdrop.second-backdrop {
    z-index: 1050 !important;
  }

  #modalConfirmarNFe {
    z-index: 1060 !important;
  }
</style>

<div class="container-fluid pdv-page-container mt-3 pdv-wrapper" style="min-height: 85vh;">
  <div class="row mb-3">

    <div class="col-lg-7 col-left-flex">

      <div class="card shadow-sm mb-3">
        <div class="card-header card-header-red">
          <i class="fas fa-plus-circle"></i> Lançar Produtos
        </div>
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-7">
              <label class="form-label"><b>Produto</b> <small>[Ctrl+0]</small></label>
              <div class="input-group">
                <input id="produtoSearchInput" type="text" class="form-control dropdown-toggle"
                  placeholder="Digite nome, código ou ref." autocomplete="off" data-bs-toggle="dropdown"
                  aria-expanded="false" aria-autocomplete="list" aria-haspopup="true" role="combobox"
                  aria-owns="produtoSearchResults" aria-activedescendant="" autofocus>
                <button class="input-group-text" id="btnModalBuscaProduto" tabindex="-1" title="Buscar produto">
                  <i class="fa fa-search"></i>
                </button>
                <ul class="dropdown-menu w-100" id="produtoSearchResults" role="listbox"
                  aria-label="Resultados da busca"></ul>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label"><b>Qtde</b> <small>[Ctrl+1]</small></label>
              <input id="quantidadeInput" type="text" class="form-control text-center" value="1" inputmode="numeric"
                pattern="[0-9]*" aria-label="Quantidade">
            </div>
            <div class="col-md-2"></div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm flex-grow-itens">
        <div class="card-header card-header-red">
          <i class="fas fa-list-ul"></i> Itens da Venda
        </div>
        <div class="card-body p-0 itens-venda-body">
          <div class="table-responsive" style="height: 100%;">
            <table class="table table-striped mb-0" aria-label="Tabela de itens da venda">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Produto</th>
                  <th scope="col" class="text-center">Qtd</th>
                  <th scope="col" class="text-end">Unit.</th>
                  <th scope="col" class="text-end">Subtotal</th>
                  <th scope="col" class="text-center">Ação</th>
                </tr>
              </thead>
              <tbody id="listaItensVenda">
                <tr id="nenhumItemMsgRow">
                  <td colspan="6" class="text-center text-muted">Nenhum item.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm h-100 d-flex flex-column">
        <div class="card-header card-header-red-darker">
          <i class="fas fa-file-invoice-dollar"></i> Fechamento da Venda
        </div>
        <div class="card-body flex-grow-1 d-flex flex-column">

          <div class="mb-3">
            <a href="#" id="toggleDescontosFechamento" class="text-primary small" style="text-decoration:none;">
              <i class="fas fa-chevron-down"></i> Desconto / Acréscimo / Preço Livre
            </a>
            <div id="areaDescontosFechamento" class="mt-2 d-none">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Desconto R$</label>
                  <input id="descontoReais" type="text" class="form-control form-control-sm text-end" placeholder="0,00"
                    inputmode="decimal" pattern="[0-9.,]*" aria-label="Desconto em reais">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Desconto %</label>
                  <input id="descontoPercent" type="text" class="form-control form-control-sm text-end"
                    placeholder="0,00" inputmode="decimal" pattern="[0-9.,]*" aria-label="Desconto em porcentagem">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Acréscimo R$</label>
                  <input id="acrescimoReais" type="text" class="form-control form-control-sm text-end"
                    placeholder="0,00" inputmode="decimal" pattern="[0-9.,]*" aria-label="Acréscimo em reais">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Preço Livre R$</label>
                  <input id="precoLivreInput" type="text" class="form-control form-control-sm text-end"
                    placeholder="0,00" inputmode="decimal" pattern="[0-9.,]*" aria-label="Preço livre">
                </div>
              </div>
            </div>
          </div>

          <div class="total-display-area mb-3" aria-live="polite" aria-atomic="true">
            <div class="total-row"><span>Subtotal:</span> <span id="subtotalItensValor">R$ 0,00</span></div>
            <div class="total-row"><span>Descontos:</span> <span id="descontosValor">− R$ 0,00</span></div>
            <div class="total-row"><span>Acréscimos:</span> <span id="acrescimosValor">+ R$ 0,00</span></div>
            <hr class="my-1">
            <div class="total-row total-liquido"><span>Total Líquido</span> <span id="totalLiquidoValor">R$ 0,00</span>
            </div>
            <hr class="my-1">
            <div class="total-row faltam-pagar"><span>Faltam Pagar:</span> <span id="faltamPagarValor">R$ 0,00</span>
            </div>
          </div>

          <div class="d-grid gap-2 mt-auto">
            <button id="btnReiniciarVenda" class="btn btn-outline-secondary" aria-label="Reiniciar venda">
              <i class="fas fa-history"></i> Reiniciar [F4]
            </button>
            <button id="btnCancelarOperacao" class="btn btn-danger" aria-label="Cancelar operação">
              <i class="fas fa-times-circle"></i> Cancelar [ESC]
            </button>
            <button id="btnFinalizarVenda" class="btn btn-success" aria-label="Finalizar venda">
              <i class="fas fa-check-circle"></i> Finalizar Venda
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>

  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header card-header-red">
          <i class="fas fa-cogs"></i> Configurações da Venda
        </div>
        <div class="card-body">

          <div class="row mb-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label"><b>Cliente</b></label>
              <select id="clienteInput2" name="cliente_id" class="form-select">
                @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ $c->id == 1 ? 'selected' : '' }}>
                  {{ $c->fantasia ?? $c->nome_razao }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label"><b>Vendedor</b></label>
              <select id="vendedorInput2" class="form-select" aria-label="Vendedor">
                @foreach($vendedores as $v)
                <option value="{{ $v->id }}">
                  {{ optional($v->pessoa)->nome_completo ?? optional($v->pessoa)->fantasia }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label"><b>Tipo de Venda</b></label>
              <select id="tipoVendaInput2" class="form-select" tabindex="2" aria-label="Tipo de Venda">
                <option value="VISTA" data-movimento-id="1" selected>À Vista</option>
                <option value="PRAZO" data-movimento-id="2">A Prazo</option>
              </select>
            </div>
          </div>

          <div class="row mb-2 d-none">
            <div class="col-md-7">
              <label class="form-label"><b>Comprador</b></label>
              <input id="compradorInput2" type="text" class="form-control" placeholder="(opcional)"
                aria-label="Comprador (opcional)">
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalMultiplasFormas" tabindex="-1" aria-labelledby="modalMultiplasFormasLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalMultiplasFormasLabel">
            Finalizar Venda - Múltiplas Formas de Pagamento
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <form id="formMultiplasFormasModal">
            <div id="valorFaltanteContainer" class="mb-3" aria-live="polite" aria-atomic="true">
              Valor faltante: <span id="valorFaltanteDisplay" style="color: red;">R$ 0,00</span>
            </div>
            <div id="multiplasFormasContainerModal">
              <div id="multiplasFormasListModal" class="mb-3"></div>
              <button type="button" id="btnAdicionarFormaPagamentoModal" class="btn btn-info mb-3"
                aria-label="Adicionar forma de pagamento">
                <i class="fas fa-plus-circle"></i> Adicionar Forma de Pagamento
              </button>
              <div>
                <label class="form-label"><b>Parcelas Geradas</b></label>
                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                  <table class="table table-sm table-bordered mb-0" id="modalTableParcelasGeradas"
                    aria-label="Parcelas geradas">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th class="text-end">Valor</th>
                        <th class="text-center">Parc.</th>
                        <th>Forma</th>
                        <th class="text-center">Vencimento</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr id="modalNenhumaParcelaMsgRow">
                        <td colspan="6" class="text-center text-muted">Nenhuma parcela.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            aria-label="Cancelar">Cancelar</button>
          <button type="button" class="btn btn-success" id="modalBtnConfirmarFinalizacao" disabled
            aria-label="Confirmar finalização">Confirmar Finalização</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBuscaProduto" tabindex="-1" aria-labelledby="modalBuscaProdutoLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalBuscaProdutoLabel">Buscar Produto</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <input type="text" id="modalBuscaProdutoInput" class="form-control mb-3" placeholder="Digite para filtrar..."
            aria-label="Filtro de produtos">
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered table-hover mb-0" id="modalBuscaProdutoTable"
              aria-label="Tabela de busca de produtos">
              <thead>
                <tr>
                  <th>Cód/Ref</th>
                  <th>Produto</th>
                  <th class="text-end price-column">Preço à vista</th>
                  <th class="text-end price-column">Preço a prazo</th>
                  <th class="text-center">Estoque</th>
                  <th class="text-center select-column">Selecionar / Qtde</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="text-center text-muted">Digite para buscar...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            aria-label="Cancelar">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnConfirmarProduto"
            aria-label="Confirmar produtos selecionados">Confirmar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalConfirmarNFe" tabindex="-1" aria-labelledby="modalConfirmarNFeLabel" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document" tabindex="-1">
      <div class="modal-content" tabindex="-1">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmarNFeLabel">Emitir Nota Fiscal Eletrônica?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          Deseja gerar a Nota Fiscal Eletrônica para essa venda?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="btnNaoEmitirNFe" data-bs-dismiss="modal">Não</button>
          <button type="button" class="btn btn-primary" id="btnConfirmarEmitirNFe">Sim, emitir NF-e</button>
        </div>
      </div>
    </div>
  </div>

  <div id="toastContainer" aria-live="polite" aria-atomic="true"></div>
  @endsection


@section('script')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script>
    window.paymentConditions = @json($paymentConditions, JSON_UNESCAPED_UNICODE);
  </script>
  <script src="{{ asset('js/pdv.js') }}"></script>
@endsection
