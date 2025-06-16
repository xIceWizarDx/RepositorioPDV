(() => {
  const state = {
    itensVenda: [],
    parcelasPagamento: [],
    produtosCache: [],
    paymentConditions: window.paymentConditions || {}
  };
  window.PDVState = state;



    window.addEventListener('pageshow', event => {
      const navEntries = performance.getEntriesByType('navigation');
      const navType = navEntries.length ? navEntries[0].type : '';

      if (event.persisted || navType === 'back_forward') {
        carregarProdutosCache();
      }
    });


    window.showToast = (message, type = 'info') => {
      console.log('showToast:', message, type);
      const container = document.getElementById('toastContainer');
      const toastEl = document.createElement('div');
      toastEl.className = `toast align-items-center text-bg-${type} border-0 mb-2`;
      toastEl.setAttribute('role', 'alert');
      toastEl.setAttribute('data-bs-delay', '3000');
      toastEl.innerHTML = `
      <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;
      container.appendChild(toastEl);
      new bootstrap.Toast(toastEl).show();
    };









    $(function() {


      const prefix = '.pdv-wrapper ';



      function formatDateBRtoISO(br) {
        console.log('DEBUG formatDateBRtoISO receber:', br);
        const [d, m, y] = br.split('/');
        const iso = `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
        console.log('DEBUG formatDateBRtoISO retorna:', iso);
        return iso;
      }



      function montarMapeamentos() {
        const orc_itens = state.itensVenda.map(it => ({
          produto_id: it.produto_id,
          quantidade: it.qtd,
          preco_vista: it.preco,
          preco_prazo: it.preco
        }));

        const condicoes = state.parcelasPagamento.map(p => p.condicao).join('/');

        const formas = state.parcelasPagamento
          .map(p => p.forma.substring(0, 3))
          .map(f => f.match(/^CRE/) ? 'CRE' : f)
          .join('/');

        const orc_parcelas = state.parcelasPagamento.map(p => ({
          seq: p.seq,
          valor: p.valor,
          vcto: formatDateBRtoISO(p.vencimento),
          forma: p.forma.match(/^CRE/) ? 'CRE' : p.forma.substring(0, 3),
          div: p.condicao
        }));

        return {
          orc_itens,
          condicoes,
          formas,
          orc_parcelas
        };
      }



      let pendingOrcamentoId = null;
      let emitirNFeEscolha = null;
      let finalizandoOrcamento = false;


      console.log('DEBUG: valor inicial #clienteInput2 ->', $(prefix + '#clienteInput2').val());

      if (!$(prefix + '#clienteInput2').val()) {
        let primeiroCliente = $(prefix + '#clienteInput2 option:not([value=""])').first();
        if (primeiroCliente.length) $(prefix + '#clienteInput2').val(primeiroCliente.val());
      }
      if (!$(prefix + '#vendedorInput2').val()) {
        let primeiroVendedor = $(prefix + '#vendedorInput2 option:not([value=""])').first();
        if (primeiroVendedor.length) $(prefix + '#vendedorInput2').val(primeiroVendedor.val());
      }

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      console.log('AJAX setup pronto.');

      const formatCurrency = v => v.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      });
      const parseFloatStrict = s => parseFloat(String(s).replace(/\./g, '').replace(',', '.')) || 0;
      const onlyNumberFloat = $el => $el.val($el.val().replace(/[^0-9,]/g, '').replace(/,(?=.*,)/g, ''));
      let itemIdCounter = 0;
      let parcelaIdCounter = 0;
      let isProcessandoFinalizacao = false;
      let isProcessandoEmissaoNFe = false;
      let debounceTimeout = null;
      let produtoIndexAtivo = 0;
      let produtoFoiAdicionado = false;
      let adicionandoProduto = false;

      function carregarProdutosCache() {
        return $.get('{{ route("pdv.search.products") }}', {
            q: ''
          })
          .done(function(data) {
            window.produtosCache = data;
            console.log('Cache de produtos carregado:', data.length);
          })
          .fail(function() {
            console.warn('Falha ao carregar cache de produtos.');
          });
      }

      carregarProdutosCache();

      function buscarProdutosNoCache(query) {
        if (!query) return [];
        const q = query.toLowerCase();

        const todos = window.produtosCache.filter(p =>
          (p.codigo_ref && p.codigo_ref.toLowerCase().includes(q)) ||
          (p.cEAN && p.cEAN.toLowerCase().includes(q)) ||
          (p.text && p.text.toLowerCase().includes(q)) ||
          (p.modelo && p.modelo.toLowerCase().includes(q))
        );

        todos.sort((a, b) => {
          const aMatch = (a.codigo_ref === query || a.cEAN === query) ? 0 : 1;
          const bMatch = (b.codigo_ref === query || b.cEAN === query) ? 0 : 1;
          if (aMatch !== bMatch) return aMatch - bMatch;
          return a.text.localeCompare(b.text);
        });

        return todos;
      }

      function buscarProdutos(term) {
        return $.get('{{ route("pdv.search.products") }}', {
            q: term || ''
          })
          .done(function(data) {
            window.produtosCache = data;
          });
      }

      function preencherResultadosDropdown(data, query = '') {
        if (produtoFoiAdicionado) {
          produtoFoiAdicionado = false;
          let html = '';
          if (!Array.isArray(data) || !data.length) {
            html = '<li><span class="dropdown-item text-muted">Nenhum resultado encontrado</span></li>';
          } else {
            data.forEach(function(item, idx) {
              html += `<li>
      <a href="#" class="dropdown-item${idx === 0 ? ' active' : ''}" 
      data-id="${item.id}" 
      data-preco_vista="${item.preco_vista}" 
      data-preco_prazo="${item.preco_prazo}" 
      data-estoque="${item.estoque}" 
      data-text="${item.text}"
      data-codigo_ref="${item.codigo_ref || ''}"
      data-cEAN="${item.cEAN || ''}">
      <strong>${item.text}</strong>
      <br>
      <small>Cód: ${item.codigo_ref || item.cEAN || '-'} | À vista: ${(item.preco_vista ? parseFloat(item.preco_vista).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '-')} | A prazo: ${(item.preco_prazo ? parseFloat(item.preco_prazo).toLocaleString('pt-BR', { style: 'currency', 'currency': 'BRL' }) : '-')} | Estoque: ${item.estoque ?? '-'}</small>
      </a>
      </li>`;
            });
          }
          $(prefix + '#produtoSearchResults').html(html);
          produtoIndexAtivo = 0;
          $(prefix + '#produtoSearchInput').attr('aria-activedescendant', $(prefix + '#produtoSearchResults .dropdown-item.active').attr('id') || '');
          return;
        }

        if (query && query.length > 0) {
          data.sort((a, b) => {
            const aMatch = (a.codigo_ref === query || a.cEAN === query) ? 0 : 1;
            const bMatch = (b.codigo_ref === query || b.cEAN === query) ? 0 : 1;
            if (aMatch !== bMatch) return aMatch - bMatch;
            return a.text.localeCompare(b.text);
          });
        }

        let html = '';
        if (!Array.isArray(data) || !data.length) {
          html = '<li><span class="dropdown-item text-muted">Nenhum resultado encontrado</span></li>';
        } else {
          data.forEach(function(item, idx) {
            html += `<li>
      <a href="#" class="dropdown-item${idx === 0 ? ' active' : ''}" 
      data-id="${item.id}" 
      data-preco_vista="${item.preco_vista}" 
      data-preco_prazo="${item.preco_prazo}" 
      data-estoque="${item.estoque}" 
      data-text="${item.text}"
      data-codigo_ref="${item.codigo_ref || ''}"
      data-cEAN="${item.cEAN || ''}">
      <strong>${item.text}</strong>
      <br>
      <small>Cód: ${item.codigo_ref || item.cEAN || '-'} | À vista: ${(item.preco_vista ? parseFloat(item.preco_vista).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '-')} | A prazo: ${(item.preco_prazo ? parseFloat(item.preco_prazo).toLocaleString('pt-BR', { style: 'currency', 'currency': 'BRL' }) : '-')} | Estoque: ${item.estoque ?? '-'}</small>
      </a>
      </li>`;
          });
        }
        $(prefix + '#produtoSearchResults').html(html);

        let dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(document.getElementById('produtoSearchInput'));
        dropdownInstance.hide();
        dropdownInstance.show();

        produtoIndexAtivo = 0;
        $(prefix + '#produtoSearchInput').attr('aria-activedescendant', $(prefix + '#produtoSearchResults .dropdown-item.active').attr('id') || '');
      }

      $(prefix + '#produtoSearchInput').on('input', function() {
        const val = $(this).val().trim();
        if (!val) {
          $(prefix + '#produtoSearchResults').empty();
          const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(this);
          dropdownInstance.hide();
          return;
        }
        const resultados = buscarProdutosNoCache(val);
        preencherResultadosDropdown(resultados, val);
        const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(this);
        dropdownInstance.show();
      });

      $(prefix + '#produtoSearchInput').on('click focus', function() {
        const $input = $(this);
        const val = $input.val().trim();
        const dropdownTrigger = this;
        const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(dropdownTrigger);
        if (val.length > 0) {
          dropdownInstance.show();
          setTimeout(() => {
            $input.focus();
          }, 10);
        } else {
          dropdownInstance.hide();
          $(prefix + '#produtoSearchResults').removeClass('show').css('display', 'none');
        }
      });

      $(prefix + '#produtoSearchInput').on('keydown', function(e) {
        if (e.key === "Enter") {
          e.preventDefault();

          const val = $(this).val().trim();
          if (!val) {
            if (state.itensVenda.length > 0) {
              $(prefix + '#btnFinalizarVenda').click();
            }
            return;
          }

          const data = buscarProdutosNoCache(val);
          const codigosProdutos = data.filter(p => {
            const cods = [p.codigo_ref, p.cEAN].map(c => c ? String(c) : '').filter(Boolean);
            return cods.includes(val);
          });

          let prodParaAdicionar = null;
          if (codigosProdutos.length > 0) {
            prodParaAdicionar = codigosProdutos[0];
          } else {
            const nomeExato = data.find(p => p.text.toLowerCase() === val.toLowerCase());
            if (nomeExato) prodParaAdicionar = nomeExato;
          }

          if (prodParaAdicionar) {
            adicionarItemPDV({
              id: prodParaAdicionar.id,
              text: prodParaAdicionar.text,
              preco_vista: prodParaAdicionar.preco_vista,
              preco_prazo: prodParaAdicionar.preco_prazo,
              estoque: prodParaAdicionar.estoque
            });
            $(prefix + '#produtoSearchInput').val('').focus();
            $(prefix + '#produtoSearchResults').empty();
          } else {
            preencherResultadosDropdown(data, val);
            showToast('Produto não encontrado para o valor exato digitado.', 'warning');
          }
        }

        const itens = $(prefix + '#produtoSearchResults .dropdown-item');
        if (!itens.length) return;

        if (e.key === "ArrowDown") {
          e.preventDefault();
          produtoIndexAtivo = (produtoIndexAtivo + 1) % itens.length;
          itens.removeClass('active');
          $(itens[produtoIndexAtivo]).addClass('active');
          $(prefix + '#produtoSearchInput').attr('aria-activedescendant', $(itens[produtoIndexAtivo]).attr('id') || '');
        }
        if (e.key === "ArrowUp") {
          e.preventDefault();
          produtoIndexAtivo = (produtoIndexAtivo - 1 + itens.length) % itens.length;
          itens.removeClass('active');
          $(itens[produtoIndexAtivo]).addClass('active');
          $(prefix + '#produtoSearchInput').attr('aria-activedescendant', $(itens[produtoIndexAtivo]).attr('id') || '');
        }
        if (e.key === "Enter" && produtoIndexAtivo > -1) {
          e.preventDefault();
          $(itens[produtoIndexAtivo]).click();
          produtoIndexAtivo = 0;
        }
      });

      $(prefix + '#produtoSearchResults').on('click', '.dropdown-item', function(e) {
        e.preventDefault();
        const $el = $(this);
        const prod = {
          id: $el.data('id'),
          text: $el.data('text'),
          preco_vista: $el.data('preco_vista'),
          preco_prazo: $el.data('preco_prazo'),
          estoque: $el.data('estoque')
        };
        adicionarItemPDV(prod);
        $(prefix + '#produtoSearchInput').val('');
        $(prefix + '#produtoSearchResults').empty();
        produtoIndexAtivo = 0;
        $(prefix + '#produtoSearchInput').attr('aria-activedescendant', '');
      });

      $(prefix + '#produtoSearchInput').on('hidden.bs.dropdown', function() {
        const $input = $(this);
        setTimeout(() => {
          $input.focus();
        }, 10);
      });

      let produtosSelecionadosModal = new Map();

      function renderProdutosModal(data) {
        if (!data.length) {
          $(prefix + '#modalBuscaProdutoTable tbody').html('<tr><td colspan="6" class="text-center text-muted">Nenhum produto encontrado</td></tr>');
          return;
        }
        let html = '';
        data.forEach(function(prod) {
          const selecionado = produtosSelecionadosModal.has(prod.id);
          const estoqueNum = parseInt(prod.estoque);
          const podeSelecionar = !isNaN(estoqueNum) && estoqueNum > 0;
          let quantidade = 0;
          if (selecionado) {
            const qtdSelecionada = produtosSelecionadosModal.get(prod.id).quantidade;
            quantidade = (qtdSelecionada > estoqueNum) ? estoqueNum : qtdSelecionada;
            produtosSelecionadosModal.get(prod.id).quantidade = quantidade;
          }
          html += `
      <tr data-id="${prod.id}"
      data-text="${prod.text}"
      data-preco_vista="${prod.preco_vista}"
      data-preco_prazo="${prod.preco_prazo}"
      data-estoque="${prod.estoque}">
      <td>${prod.codigo_ref || prod.cEAN || '-'}</td>
      <td>${prod.text}</td>
      <td class="text-end">${prod.preco_vista ? parseFloat(prod.preco_vista).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '-'}</td>
      <td class="text-end">${prod.preco_prazo ? parseFloat(prod.preco_prazo).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '-'}</td>
      <td class="text-center">${prod.estoque ?? '-'}</td>
      <td class="text-center select-column">
      <div class="quantidade-control">
      <input type="checkbox" class="produto-checkbox" ${selecionado ? 'checked' : ''} ${podeSelecionar ? '' : 'disabled'} aria-label="Selecionar produto ${prod.text}" />
      <input type="text" class="form-control form-control-sm input-quantidade" value="${quantidade}" ${selecionado && podeSelecionar ? '' : 'disabled'} aria-label="Quantidade do produto ${prod.text}" />
      </div>
      </td>
      </tr>
      `;
        });
        $(prefix + '#modalBuscaProdutoTable tbody').html(html);
      }

      $(prefix + '#btnModalBuscaProduto').on('click', function() {
        produtosSelecionadosModal.clear();

        $(prefix + '#modalBuscaProduto').modal('show');
        $(prefix + '#modalBuscaProdutoInput').val('').focus();

        buscarProdutos('').done(function(data) {
          state.itensVenda.forEach(item => {
            const prodCompleto = data.find(p => p.id === item.produto_id);
            produtosSelecionadosModal.set(item.produto_id, {
              id: item.produto_id,
              text: item.descricao,
              preco_vista: item.preco,
              preco_prazo: null,
              estoque: prodCompleto ? prodCompleto.estoque : 0,
              quantidade: item.qtd
            });
          });

          renderProdutosModal(data);
        });
      });

      $(prefix + '#modalBuscaProdutoInput').on('input', function() {
        const val = $(this).val().trim();
        if (val.length < 1) {
          buscarProdutos('').done(function(data) {
            renderProdutosModal(data);
          });
          return;
        }
        buscarProdutos(val).done(function(data) {
          renderProdutosModal(data);
        });
      });

      $(prefix + '#modalBuscaProdutoTable tbody').off('click input change').on('click', 'tr', function(e) {
        const $target = $(e.target);
        const $row = $(this);
        const id = $row.data('id');
        const estoqueRaw = $row.data('estoque');
        const estoqueNum = parseInt(estoqueRaw);

        if (isNaN(estoqueNum) || estoqueNum <= 0) {
          showToast('Produto com estoque insuficiente não pode ser selecionado.', 'warning');
          return;
        }

        if ($target.hasClass('produto-checkbox')) {
          const checked = $target.prop('checked');
          if (checked) {
            if (!produtosSelecionadosModal.has(id)) {
              produtosSelecionadosModal.set(id, {
                id,
                text: $row.data('text'),
                preco_vista: $row.data('preco_vista'),
                preco_prazo: $row.data('preco_prazo'),
                estoque: estoqueRaw,
                quantidade: 1
              });
            }
            $row.find('.input-quantidade').val(1).prop('disabled', false);
            let prod = produtosSelecionadosModal.get(id);
            if (prod) {
              prod.quantidade = 1;
              produtosSelecionadosModal.set(id, prod);
            }
            $row.addClass('table-success');
          } else {
            produtosSelecionadosModal.delete(id);
            $row.find('.input-quantidade').val(0).prop('disabled', true);
            $row.removeClass('table-success');
          }
          return;
        }

        if ($target.hasClass('input-quantidade')) {
          return;
        }

        if ($target.closest('td.select-column').length === 0) {
          const $checkbox = $row.find('.produto-checkbox');
          if ($checkbox.prop('disabled')) return;
          $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        }
      });

      $(prefix + '#modalBuscaProdutoTable tbody').on('input', 'input.input-quantidade', function() {
        const $input = $(this);
        let val = $input.val();
        val = val.replace(/\D/g, '');
        $input.val(val);

        if (val !== '') {
          const numero = parseInt(val);
          const $row = $input.closest('tr');
          const id = $row.data('id');
          if (produtosSelecionadosModal.has(id)) {
            let prod = produtosSelecionadosModal.get(id);
            prod.quantidade = numero;
            produtosSelecionadosModal.set(id, prod);
          }
        }
      });

      $(prefix + '#modalBuscaProdutoTable tbody').on('blur', 'input.input-quantidade', function() {
        const $input = $(this);
        let val = $input.val();

        if (val === '' || val === '0') {
          val = '1';
        }

        let numero = parseInt(val);

        const $row = $input.closest('tr');
        const estoqueNum = parseInt($row.data('estoque'));

        if (!isNaN(estoqueNum) && numero > estoqueNum) {
          numero = estoqueNum;
          showToast(`Quantidade ajustada para estoque disponível (${estoqueNum}).`, 'warning');
        }

        $input.val(numero);

        const id = $row.data('id');
        if (produtosSelecionadosModal.has(id)) {
          let prod = produtosSelecionadosModal.get(id);
          prod.quantidade = numero;
          produtosSelecionadosModal.set(id, prod);
        }
      });

      $(prefix + '#modalBuscaProdutoTable tbody').on('change', 'input.produto-checkbox', function() {
        const $checkbox = $(this);
        const checked = $checkbox.prop('checked');
        const $row = $checkbox.closest('tr');
        const id = $row.data('id');
        const estoqueRaw = $row.data('estoque');
        const estoqueNum = parseInt(estoqueRaw);
        if (checked) {
          if (!produtosSelecionadosModal.has(id)) {
            produtosSelecionadosModal.set(id, {
              id,
              text: $row.data('text'),
              preco_vista: $row.data('preco_vista'),
              preco_prazo: $row.data('preco_prazo'),
              estoque: estoqueRaw,
              quantidade: 1
            });
          }
          $row.find('.input-quantidade').val(1);
          $row.find('input.input-quantidade').prop('disabled', false);
          $row.addClass('table-success');
        } else {
          produtosSelecionadosModal.delete(id);
          $row.find('.input-quantidade').val(0);
          $row.find('input.input-quantidade').prop('disabled', true);
          $row.removeClass('table-success');
        }
      });

      $(prefix + '#btnConfirmarProduto').on('click', function() {
        if (produtosSelecionadosModal.size === 0) {
          $(prefix + '#modalBuscaProduto').modal('hide');
          return;
        }

        let algumProdutoAdicionado = false;

        produtosSelecionadosModal.forEach(prod => {
          if (prod.quantidade < 1) prod.quantidade = 1;

          const estoqueNum = parseInt(prod.estoque);
          if (isNaN(estoqueNum) || estoqueNum <= 0) {
            return;
          }
          if (prod.quantidade > estoqueNum) {
            showToast(`Produto "${prod.text}" não possui estoque suficiente para a quantidade ${prod.quantidade}. Ajustado para ${estoqueNum}.`, 'warning');
            prod.quantidade = estoqueNum;
          }

          const itemExistenteIndex = state.itensVenda.findIndex(item => item.produto_id === prod.id);
          const preco = prod.preco_vista ? parseFloat(prod.preco_vista) : 0;
          if (itemExistenteIndex > -1) {
            state.itensVenda[itemExistenteIndex].qtd = prod.quantidade;
            state.itensVenda[itemExistenteIndex].subtotal = preco * prod.quantidade;
          } else {
            state.itensVenda.push({
              id: itemIdCounter++,
              produto_id: prod.id,
              descricao: prod.text,
              qtd: prod.quantidade,
              preco: preco,
              subtotal: preco * prod.quantidade
            });
          }

          algumProdutoAdicionado = true;
        });

        produtosSelecionadosModal.clear();
        $(prefix + '#modalBuscaProduto').modal('hide');

        if (algumProdutoAdicionado) {
          renderizarItensTabela();
          calcularTotais();
        }

        setTimeout(() => {
          $(prefix + '#produtoSearchInput').focus();
        }, 400);
      });

      function renderizarItensTabela() {
        const $tbody = $(prefix + '#listaItensVenda').empty();
        if (!state.itensVenda.length) {
          $tbody.append(`<tr id="nenhumItemMsgRow"><td colspan="6" class="text-center text-muted">Nenhum item.</td></tr>`);
          return;
        }
        state.itensVenda.forEach((item, idx) => {
          $tbody.append(`
      <tr data-id="${item.id}">
      <td>${idx + 1}</td>
      <td>${item.descricao}</td>
      <td class="text-center">${item.qtd}</td>
      <td class="text-end">${formatCurrency(item.preco)}</td>
      <td class="text-end">${formatCurrency(item.subtotal)}</td>
      <td class="text-center">
      <button class="btn btn-sm btn-danger btn-remover-item" data-id="${item.id}" title="Remover item" aria-label="Remover item ${item.descricao}">
      <i class="fas fa-trash-alt"></i>
      </button>
      </td>
      </tr>
      `);
        });
      }

      $(prefix + '#listaItensVenda').on('click', '.btn-remover-item', function() {
        const id = $(this).data('id');
        state.itensVenda = state.itensVenda.filter(item => item.id !== id);
        window.itensVenda = state.itensVenda;
        renderizarItensTabela();
        calcularTotais();
        console.log('Item removido →', state.itensVenda);
      });

      function adicionarItemPDV(prod) {
        if (adicionandoProduto) {
          return;
        }
        adicionandoProduto = true;

        if (!prod || !prod.id) {
          showToast('Produto inválido', 'danger');
          adicionandoProduto = false;
          return;
        }
        const estoqueNum = parseInt(prod.estoque);
        if (isNaN(estoqueNum) || estoqueNum <= 0) {
          showToast('Produto com estoque insuficiente.', 'warning');
          adicionandoProduto = false;
          return;
        }

        const qtdInput = parseInt($(prefix + '#quantidadeInput').val(), 10) || 1;
        const itemExistenteIndex = state.itensVenda.findIndex(item => item.produto_id === prod.id);

        if (itemExistenteIndex > -1) {
          const itemAtual = state.itensVenda[itemExistenteIndex];
          let novaQtd = itemAtual.qtd + qtdInput;

          if (novaQtd > estoqueNum) {
            showToast(`Quantidade solicitada ultrapassa o estoque disponível (${estoqueNum}). Ajustado para o máximo disponível.`, 'warning');
            novaQtd = estoqueNum;
          }

          itemAtual.qtd = novaQtd;
          itemAtual.subtotal = itemAtual.preco * itemAtual.qtd;
          state.itensVenda[itemExistenteIndex] = itemAtual;
        } else {
          let qtdFinal = qtdInput > estoqueNum ? estoqueNum : qtdInput;
          if (qtdInput > estoqueNum) {
            showToast(`Quantidade solicitada ultrapassa o estoque disponível (${estoqueNum}). Ajustado para o máximo disponível.`, 'warning');
          }
          const preco = prod.preco_vista ? parseFloat(prod.preco_vista) : 0;
          state.itensVenda.push({
            id: itemIdCounter++,
            produto_id: prod.id,
            descricao: prod.text,
            qtd: qtdFinal,
            preco: preco,
            subtotal: preco * qtdFinal
          });
        }

        window.itensVenda = state.itensVenda;
        renderizarItensTabela();
        calcularTotais();
        $(prefix + '#quantidadeInput').val('1');
        console.log('Item adicionado →', state.itensVenda);

        produtoFoiAdicionado = true;

        setTimeout(() => {
          adicionandoProduto = false;
        }, 100);
      }

      $(prefix + '#btnReiniciarVenda').click(function() {
        if (!confirm('Reiniciar venda?')) return;
        state.itensVenda = [];
        state.parcelasPagamento = [];
        itemIdCounter = 0;
        parcelaIdCounter = 0;
        window.itensVenda = state.itensVenda;
        window.parcelasPagamento = state.parcelasPagamento;
        renderizarItensTabela();
        calcularTotais();
        $(prefix + '#produtoSearchInput').val('');
        $(prefix + '#produtoSearchResults').empty();
        produtoIndexAtivo = 0;
        console.log('Venda reiniciada.');
      });

      $(prefix + '#btnCancelarOperacao').click(function() {
        $(prefix + '#produtoSearchInput').val('');
        $(prefix + '#produtoSearchResults').empty();
        $(prefix + '#quantidadeInput').val('1').focus();
        produtoIndexAtivo = 0;
      });

      function iniciarMultiplasFormasPagamentoModal() {
        state.parcelasPagamento = [];
        window.parcelasPagamento = state.parcelasPagamento;
        $(prefix + '#multiplasFormasListModal').empty();
        adicionarNovaFormaPagamentoMultiplasModal();
      }

      function adicionarNovaFormaPagamentoMultiplasModal(formaId = '', valor = '') {
        let valorFaltanteTexto = $(prefix + '#valorFaltanteDisplay').text();
        valorFaltanteTexto = valorFaltanteTexto.replace(/[^\d,.-]/g, '').replace(',', '.');
        const valorFaltante = parseFloat(valorFaltanteTexto);

        if (!valorFaltante || valorFaltante <= 0) {
          return;
        }

        const idUnico = 'mpm_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

        const html = `
      <div class="input-group" data-id="${idUnico}">
      <select class="form-select forma-pagamento-select" required aria-label="Selecione forma de pagamento">
      <option value="">--- selecione ---</option>
      @foreach($formasPagamento as $forma)
      <option value="{{ $forma->id }}">{{ str_pad($forma->sequencial, 2, '0', STR_PAD_LEFT) }} - {{ $forma->descricao }}</option>
      @endforeach
      </select>
      <input type="text" class="form-control valor-parcela-input text-end" placeholder="Valor R$" value="${valor}" inputmode="decimal" pattern="[0-9.,]*" required aria-label="Valor da parcela" />
      <button class="btn btn-danger btn-remover-parcela" type="button" title="Remover parcela" aria-label="Remover parcela">
      <i class="fas fa-trash-alt"></i>
      </button>
      </div>
      `;
        $(prefix + '#multiplasFormasListModal').append(html);

        if (formaId) {
          $(prefix + `#multiplasFormasListModal div[data-id="${idUnico}"] select.forma-pagamento-select`).val(formaId);
        }
      }

      function formaPagamentoDuplicada(valorSelecionado, idAtual) {
        let count = 0;
        $(prefix + '#multiplasFormasListModal select.forma-pagamento-select').each(function() {
          const val = $(this).val();
          const grupoId = $(this).closest('.input-group').data('id');
          if (val === valorSelecionado && grupoId !== idAtual) {
            count++;
          }
        });
        return count > 0;
      }

      $(prefix + '#multiplasFormasListModal').on('change', 'select.forma-pagamento-select', function() {
        const $select = $(this);
        const $inputValor = $select.closest('.input-group').find('input.valor-parcela-input');
        const grupoId = $select.closest('.input-group').data('id');

        const valorSelecionado = $select.val();

        if (valorSelecionado && formaPagamentoDuplicada(valorSelecionado, grupoId)) {
          showToast('Essa forma de pagamento já está selecionada.', 'warning');
          $select.val('');
          $inputValor.val('');
          $select.focus();
          return;
        }

        let valorFaltanteTexto = $(prefix + '#valorFaltanteDisplay').text();
        valorFaltanteTexto = valorFaltanteTexto.replace(/[^\d,.-]/g, '').replace(',', '.');
        const valorFaltante = parseFloat(valorFaltanteTexto);

        const valAtual = $inputValor.val().replace(/[^\d,.-]/g, '').replace(',', '.');
        const valAtualNum = parseFloat(valAtual);

        if (!valAtual || valAtualNum === 0) {
          const valorFormatado = valorFaltante.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          $inputValor.val(valorFormatado);
        }

        atualizarParcelasMultiplasModal();
        calcularValorFaltante();
      });


      $(prefix + '#multiplasFormasListModal').on('click', '.btn-remover-parcela', function() {
        $(this).closest('.input-group').remove();
        atualizarParcelasMultiplasModal();
      });

      let ultimoAvisoParcelas = null;

      function atualizarParcelasMultiplasModal() {
        state.parcelasPagamento = [];

        $(prefix + '#multiplasFormasListModal .input-group').each(function(_, el) {
          const forma = $(el).find('select.forma-pagamento-select').val();
          let valorStr = $(el).find('input.valor-parcela-input').val() || '0,00';
          valorStr = valorStr.replace(/\./g, '').replace(',', '.');
          const valorTotal = parseFloat(valorStr) || 0;
          if (!forma || valorTotal <= 0) return;

          const N = condicoes.length;
          const valorBase = valorTotal / N;
          let soma = 0;

          condicoes.forEach((dias, idx) => {
            const dataVenc = new Date();
            dataVenc.setDate(dataVenc.getDate() + dias);
            const valorParcela = idx === N - 1 ?
              valorTotal - soma :
              valorBase;
            soma += idx < N - 1 ? valorBase : 0;

            state.parcelasPagamento.push({
              id: parcelaIdCounter++,
              seq: idx + 1,
              valor: valorParcela,
              vencimento: dataVenc.toLocaleDateString('pt-BR'),
              forma: forma,
              condicao: N
            });
          });
        });

        window.parcelasPagamento = state.parcelasPagamento;
        renderizarParcelasTabelaModal();
        calcularValorFaltante();

        const totalVenda = parseFloatStrict(
          $(prefix + '#totalLiquidoValor').text()
          .replace(/[^0-9,.-]/g, '')
          .replace(',', '.')
        );
        const somaParcelas = state.parcelasPagamento.reduce((sum, p) => sum + p.valor, 0);

        if (somaParcelas < totalVenda) {
          if (ultimoAvisoParcelas !== 'menor') {
            showToast('A soma das formas de pagamento é menor que o total da venda.', 'warning');
            ultimoAvisoParcelas = 'menor';
          }
        } else if (somaParcelas > totalVenda) {
          if (ultimoAvisoParcelas !== 'maior') {
            showToast('A soma das formas de pagamento é maior que o total da venda.', 'warning');
            ultimoAvisoParcelas = 'maior';
          }
        } else {
          ultimoAvisoParcelas = null;
        }
      }





      function renderizarParcelasTabelaModal() {
        const $tb = $(prefix + '#modalTableParcelasGeradas tbody').empty();
        if (!state.parcelasPagamento.length) {
          $tb.append('<tr id="modalNenhumaParcelaMsgRow"><td colspan="5" class="text-center text-muted">Nenhuma parcela.</td></tr>');
          return;
        }

        state.parcelasPagamento.forEach((p, index) => {
          $tb.append(`
      <tr>
      <td>${index + 1}</td>
      <td class="text-end">${formatCurrency(p.valor)}</td>
      <td class="text-center">${p.seq}º</td>
      <td>${p.forma}</td>
      <td class="text-center">${p.vencimento}</td>
      </tr>
      `);
        });
      }



      window.removerParcela = id => {
        state.parcelasPagamento = state.parcelasPagamento.filter(p => p.id !== id);
        state.parcelasPagamento.forEach((p, i) => p.seq = i + 1);
        window.parcelasPagamento = state.parcelasPagamento;
        renderizarParcelasTabelaModal();
        calcularValorFaltante();
        console.log('Parcela removida →', state.parcelasPagamento);
      };


      function clearFinalizacaoModal() {
        console.log('DEBUG clearFinalizacaoModal: limpando modal de finalização');
        state.parcelasPagamento = [];
        window.parcelasPagamento = state.parcelasPagamento;

        document.querySelectorAll('#multiplasFormasListModal .input-group').forEach(el => el.remove());

        const tbody = document.querySelector('#modalTableParcelasGeradas tbody');
        tbody.innerHTML =
          '<tr id="modalNenhumaParcelaMsgRow">' +
          '<td colspan="6" class="text-center text-muted">Nenhuma parcela.</td>' +
          '</tr>';

        const valorDisplay = document.getElementById('valorFaltanteDisplay');
        valorDisplay.textContent = 'R$ 0,00';
        valorDisplay.classList.remove('valor-faltante-zero');
      }

      document.addEventListener('keydown', function(e) {
        const modalFinal = document.getElementById('modalMultiplasFormas');
        const modalNFe = document.getElementById('modalConfirmarNFe');
        const isFinalOpen = modalFinal.classList.contains('show');
        const isNFeOpen = modalNFe.classList.contains('show');

        if (e.key === 'Escape') {
          console.log('DEBUG tecla ESC detectada; finalizandoOrcamento=', finalizandoOrcamento, 'pendingOrcamentoId=', pendingOrcamentoId);
        }

        if (
          e.key === 'Escape' &&
          isFinalOpen &&
          !isNFeOpen &&
          pendingOrcamentoId === null &&
          !finalizandoOrcamento
        ) {
          e.preventDefault();
          console.log('DEBUG ESC: fechando modalMultiplasFormas antes de iniciar finalização');
          clearFinalizacaoModal();
          bootstrap.Modal.getInstance(modalFinal).hide();
        }
      });


      function abrirModalConfirmarNFe() {
        nfeAwaitingEmission = false;
        pendingOrcamentoId = null;
        console.log('DEBUG abrirModalConfirmarNFe: preparando modal NFe');

        document.querySelectorAll('.modal-backdrop.show:not(.second-backdrop)').forEach(el => {
          el.classList.add('first-backdrop');
          el.classList.remove('fade');
        });

        const backdrop2 = document.createElement('div');
        backdrop2.classList.add('modal-backdrop', 'second-backdrop', 'show');
        document.body.appendChild(backdrop2);

        const modalNFeEl = document.getElementById('modalConfirmarNFe');
        const modalNFe = new bootstrap.Modal(modalNFeEl, {
          backdrop: false,
          keyboard: false
        });

        modalNFe.show();
        console.log('DEBUG abrirModalConfirmarNFe: modal mostrado');


        modalNFeEl.addEventListener('shown.bs.modal', () => {
          document.getElementById('btnConfirmarEmitirNFe').focus();
        });


        modalNFeEl.addEventListener('hidden.bs.modal', () => {
          console.log('DEBUG abrirModalConfirmarNFe: removendo second-backdrop');
          document.querySelectorAll('.modal-backdrop.second-backdrop').forEach(el => el.remove());
        });

        modalNFeEl.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            e.preventDefault();
            document.getElementById('btnNaoEmitirNFe').click();
          }
          if (e.key === 'Enter') {
            e.preventDefault();
            const modalNFeEl = document.getElementById('modalConfirmarNFe');
            bootstrap.Modal.getInstance(modalNFeEl).hide();
            document.getElementById('btnConfirmarEmitirNFe').click();
          }
        });
      }


      function finalizarVendaSemNFe(payloadOrc, payloadFin, payloadFat) {
        console.log('DEBUG finalizarVendaSemNFe - payloadOrcamento:', payloadOrc);
        console.log('DEBUG finalizarVendaSemNFe - payloadFinalizacao:', payloadFin);
        console.log('DEBUG finalizarVendaSemNFe - payloadFaturamento:', payloadFat);

        return $.post('{{ route("vendas.orcamento.store") }}', payloadOrc)
          .then(res => {
            if (!res.insert_id) return Promise.reject(res.message || 'Erro ao criar orçamento.');
            payloadFin.orcamento_id = res.insert_id;
            payloadFat.id_orcamento = res.insert_id;
            return $.post('{{ route("vendas.orcamento.finalizar.store") }}', payloadFin);
          })
          .then(res2 => {
            if (res2.status !== 'OK') return Promise.reject(res2.message || 'Erro ao finalizar orçamento.');
            return $.post('{{ route("vendas.orcamento.finalizar.store_faturar") }}', payloadFat);
          })
          .then(res3 => {
            console.log('DEBUG faturar response:', res3);
            if (res3.status !== 'OK') return Promise.reject(res3.data || 'Erro ao faturar.');
            return Promise.resolve({
              orcamentoId: payloadFat.id_orcamento,
              notaNumero: res3.data
            });
          });
      }


      function emitirNotaFiscal(orcamentoId) {
        console.log('DEBUG emitirNotaFiscal: iniciando para orcamentoId=', orcamentoId);

        const payloadNota = {
          orcamento_id: orcamentoId,
          ide_nNF: '',
          ide_mod: '55',
          ide_idDest: parseInt($('#ideIdDestInput').val() || '1', 10),
          consumidor_final: parseInt($('#consumidorFinalInput').val() || '1', 10),
          consumidor_cpf: '',
          consumidor_nome: '',
          total_frete: 0,
          total_seguro: 0,
          total_outros: 0
        };

        console.log('DEBUG emitirNotaFiscal - payloadNota:', payloadNota);

        return $.post(
            '{{ route("vendas.orcamento.finalizar.store_emitir_nota") }}',
            payloadNota
          )
          .then(res4 => {
            console.log('DEBUG emitirNotaFiscal response:', res4);
            if (res4.status !== 'OK') {
              console.log('DEBUG emitirNotaFiscal: falha na resposta');
              return Promise.reject(res4.data);
            }
            console.log('DEBUG emitirNotaFiscal: sucesso');
            return res4.data;
          })
          .catch(err => {
            console.error('DEBUG emitirNotaFiscal erro:', err);
            return Promise.reject(err);
          });
      }





      $(prefix + '#btnFinalizarVenda').off('click').on('click', function() {
        if (!$(prefix + '#clienteInput2').val()) {
          showToast('Selecione um Cliente.', 'warning');
          return;
        }
        if (!state.itensVenda.length) {
          showToast('Adicione ao menos 1 item.', 'warning');
          return;
        }

        state.parcelasPagamento = [];
        parcelaIdCounter = 0;
        window.parcelasPagamento = state.parcelasPagamento;
        iniciarMultiplasFormasPagamentoModal();
        renderizarParcelasTabelaModal();

        const backdrop1 = document.createElement('div');
        backdrop1.classList.add('modal-backdrop', 'fade', 'first-backdrop', 'show');
        document.body.appendChild(backdrop1);

        const modalEl = document.getElementById('modalMultiplasFormas');
        const modal = new bootstrap.Modal(modalEl, {
          backdrop: false,
          keyboard: false
        });
        modal.show();
        atualizarParcelasMultiplasModal();
        calcularValorFaltante();


        $(modalEl).off('shown.bs.modal').on('shown.bs.modal', function() {
          const selectPrimeiro = $(prefix + '#multiplasFormasListModal select.forma-pagamento-select').first()[0];
          if (selectPrimeiro) {
            setTimeout(() => {
              selectPrimeiro.focus();
              if (selectPrimeiro.showPicker) {
                selectPrimeiro.showPicker();
              }
            }, 100);
          }
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
          backdrop1.remove();
        });
      });


      $(document).on('keydown', prefix + ' .forma-pagamento-select', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();

          const $input = $(this).closest('.input-group').find('.valor-parcela-input');
          $input.focus().select();
        }
      });


      $(document).on('input', prefix + ' .valor-parcela-input', function() {
        setTimeout(() => {
          atualizarParcelasMultiplasModal();
          const sumExisting = state.parcelasPagamento.reduce((a, b) => a + b.valor, 0);
          const totalVenda = parseFloatStrict($(prefix + '#totalLiquidoValor').text().replace(/[^0-9,.-]/g, ''));
          const diff = totalVenda - sumExisting;
          $(prefix + '#valorFaltanteDisplay').text(formatCurrency(Math.abs(diff)));
          if (diff <= 0) {
            $(prefix + '#valorFaltanteDisplay').addClass('valor-faltante-zero');
          } else {
            $(prefix + '#valorFaltanteDisplay').removeClass('valor-faltante-zero');
          }
          $(prefix + '#modalBtnConfirmarFinalizacao').prop('disabled', (diff > 0.005));
        }, 0);
      });

      $(document).on('keydown', prefix + ' .valor-parcela-input', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();

          atualizarParcelasMultiplasModal();
          calcularValorFaltante();

          function parseValor(str) {
            return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
          }
          const valorDigitado = parseValor($(this).val());
          if (valorDigitado <= 0) {
            showToast('Informe um valor válido.', 'warning');
            return;
          }

          const totalVenda = parseFloatStrict(
            $(prefix + '#totalLiquidoValor').text().replace(/[^0-9,.-]/g, '')
          );
          const pago = state.parcelasPagamento.reduce((sum, p) => sum + p.valor, 0);
          const faltante = totalVenda - pago;

          if (faltante > 0.005) {
            adicionarNovaFormaPagamentoMultiplasModal();
            setTimeout(() => {
              const $lastSelect = $(prefix + '#multiplasFormasListModal select.forma-pagamento-select').last();
              $lastSelect.focus();
              if ($lastSelect[0].showPicker) $lastSelect[0].showPicker();
            }, 50);
          } else {
            const $btnConfirm = $(prefix + '#modalBtnConfirmarFinalizacao');
            if (!$btnConfirm.prop('disabled')) {
              $btnConfirm.focus();
            }
          }
        }
      });








      $(document)
        .off('click', prefix + '#btnAdicionarFormaPagamentoModal')
        .on('click', prefix + '#btnAdicionarFormaPagamentoModal', function() {
          const lastGroup = $(prefix + '#multiplasFormasListModal .input-group').last();
          const formaSelecionada = lastGroup.find('select.forma-pagamento-select').val();
          const valorStrRaw = lastGroup.find('input.valor-parcela-input').val();

          if (!formaSelecionada) {
            showToast('Selecione a forma de pagamento.', 'warning');
            return;
          }
          if (!valorStrRaw || !valorStrRaw.match(/\d/)) {
            showToast('Informe um valor válido.', 'warning');
            return;
          }

          atualizarParcelasMultiplasModal();
        });





      isProcessandoFinalizacao = false;
      isProcessandoEmissaoNFe = false;

      $(prefix + '#modalBtnConfirmarFinalizacao').off('click').on('click', function() {
        if (isProcessandoFinalizacao) return;
        isProcessandoFinalizacao = true;

        const $btn = $(this);
        if ($btn.prop('disabled')) {
          isProcessandoFinalizacao = false;
          return;
        }
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');

        console.log('DEBUG iniciar Confirmar Finalização - state.parcelasPagamento:', state.parcelasPagamento);

        if (state.parcelasPagamento.length === 0) {
          console.log('DEBUG nenhum método de pagamento adicionado');
          showToast('Adicione ao menos uma forma de pagamento.', 'warning');
          isProcessandoFinalizacao = false;
          $btn.prop('disabled', false).html('Confirmar Finalização');
          return;
        }

        const totalPago = state.parcelasPagamento.reduce((acc, p) => acc + p.valor, 0);
        const totalVenda = parseFloatStrict($(prefix + '#totalLiquidoValor').text().replace(/[^0-9,.-]/g, ''));
        console.log('DEBUG totalPago:', totalPago, 'totalVenda:', totalVenda);

        if (Math.abs(totalPago - totalVenda) > 0.05) {
          console.log('DEBUG soma das parcelas != total da venda');
          showToast('A soma das formas de pagamento deve ser igual ao total da venda.', 'warning');
          isProcessandoFinalizacao = false;
          $btn.prop('disabled', false).html('Confirmar Finalização');
          return;
        }

        const orc_itens = state.itensVenda.map(it => ({
          produto_id: it.produto_id,
          quantidade: it.qtd,
          preco_vista: it.preco,
          preco_prazo: it.preco
        }));
        console.log('DEBUG orc_itens:', orc_itens);

        const condicoes = state.parcelasPagamento.map(p => p.condicao).join('/');
        const formas = state.parcelasPagamento
          .map(p => p.forma.substring(0, 3))
          .map(f => f.match(/^CRE/) ? 'CRE' : f)
          .join('/');
        console.log('DEBUG condicoes:', condicoes, 'formas:', formas);

        function formatDateBRtoISO(br) {
          const [d, m, y] = br.split('/');
          return `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
        }

        const orc_parcelas = state.parcelasPagamento.map(p => ({
          seq: p.seq,
          valor: p.valor,
          vcto: formatDateBRtoISO(p.vencimento),
          forma: (p.forma.match(/^CRE/) ? 'CRE' : p.forma.substring(0, 3)),
          div: p.condicao
        }));
        console.log('DEBUG orc_parcelas:', orc_parcelas);

        const payloadOrcamento = {
          preco_item: $(prefix + '#tipoVendaInput2').val(),
          cliente_id: $(prefix + '#clienteInput2').val(),
          vendedor_id: $(prefix + '#vendedorInput2').val(),
          comprador: $(prefix + '#compradorInput2').val()?.trim() || '',
          tipo_venda: $(prefix + '#tipoVendaInput2').val(),
          desconto_valor: parseFloatStrict($(prefix + '#descontosValor').text().replace(/[^0-9,.-]/g, '')),
          desconto_porc: parseFloatStrict($(prefix + '#descontoPercent').val()),
          acrescimo_valor: parseFloatStrict($(prefix + '#acrescimosValor').text().replace(/[^0-9,.-]/g, '')),
          acrescimo_porc: 0,
          forma_pagamento: formas,
          condicao_pagamento: condicoes,
          forma_somente_fiscal: '0',
          orcamento_itens: orc_itens,
          orcamento_parcelas: orc_parcelas
        };
        console.log('DEBUG payloadOrcamento:', payloadOrcamento);

        const payloadFinalizacao = {
          orcamento_id: null,
          condicao_pagamento: condicoes,
          forma_pagamento: formas,
          parcelamento_cliente: condicoes,
          pre_forma: formas,
          centavo_ultima_parcela: 0,
          desconto_valor: payloadOrcamento.desconto_valor,
          desconto_porc: payloadOrcamento.desconto_porc,
          acrescimo_valor: payloadOrcamento.acrescimo_valor,
          acrescimo_porc: 0,
          orcamento_parcelas: orc_parcelas,
          forma_somente_fiscal: '0'
        };
        console.log('DEBUG payloadFinalizacao:', payloadFinalizacao);

        const movimentoId = parseInt($(prefix + '#tipoVendaInput2 option:selected').data('movimento-id'), 10);
        const payloadFaturamento = {
          id_orcamento: null,
          tipo_movimento_id: movimentoId
        };
        console.log('DEBUG payloadFaturamento:', payloadFaturamento);

        abrirModalConfirmarNFe();

        emitirNFeEscolha = null;
        pendingOrcamentoId = null;
        finalizandoOrcamento = true;
        console.log('DEBUG finalizandoOrcamento set to true');

        finalizarVendaSemNFe(payloadOrcamento, payloadFinalizacao, payloadFaturamento)
          .then(({
            orcamentoId,
            notaNumero
          }) => {
            console.log(
              'DEBUG finalizarVendaSemNFe sucesso →',
              'orcamentoId:', orcamentoId,
              'notaNumero:', notaNumero
            );
            pendingOrcamentoId = orcamentoId;
            finalizandoOrcamento = false;
            isProcessandoFinalizacao = false;
            $btn.prop('disabled', false).html('Confirmar Finalização');

            function limparVenda() {
              state.itensVenda = [];
              state.parcelasPagamento = [];
              itemIdCounter = 0;
              parcelaIdCounter = 0;
              window.itensVenda = state.itensVenda;
              window.parcelasPagamento = state.parcelasPagamento;

              renderizarItensTabela();
              clearFinalizacaoModal();
              calcularTotais();

              $('#descontoReais').val('0,00');
              $('#descontoPercent').val('0,00');
              $('#acrescimoReais').val('0,00');
              $('#precoLivreInput').val('0,00');

              $('#produtoSearchInput').val('').focus();
            }

            const modalNFeEl = document.getElementById('modalConfirmarNFe');
            const modalNFeIsOpen = modalNFeEl.classList.contains('show');

            if (!modalNFeIsOpen) {
              limparVenda();
              const modalFinal = bootstrap.Modal.getInstance(document.getElementById('modalMultiplasFormas'));
              if (modalFinal) {
                modalFinal.hide();
              }
              console.log('DEBUG modalMultiplasFormas escondido');
            } else {
              console.log('DEBUG modalConfirmarNFe aberto, aguardando fechamento para limpar e fechar modal finalização.');

              $('#modalConfirmarNFe').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                limparVenda();
                const modalFinal = bootstrap.Modal.getInstance(document.getElementById('modalMultiplasFormas'));
                if (modalFinal) {
                  modalFinal.hide();
                }
                console.log('DEBUG modalConfirmarNFe fechado, modalMultiplasFormas fechado e venda limpa.');
              });
            }

            if (nfeAwaitingEmission) {
              console.log(
                'DEBUG nfeAwaitingEmission=true, iniciando emitirNotaFiscal para',
                orcamentoId
              );
              emitirNotaFiscal(orcamentoId)
                .then(msg => {
                  console.log('DEBUG emitirNotaFiscal sucesso:', msg);
                  showToast(msg, 'success');
                })
                .catch(err => {
                  console.error('DEBUG emitirNotaFiscal erro:', err);
                  showToast(err || 'Erro ao emitir nota.', 'danger');
                });
            } else {
              console.log('DEBUG aguardando escolha do usuário para emitir NF-e');
            }
          })

          .catch(errMsg => {
            console.error('DEBUG finalizarVendaSemNFe falha:', errMsg);
            finalizandoOrcamento = false;
            isProcessandoFinalizacao = false;
            $btn.prop('disabled', false).html('Confirmar Finalização');
            showToast(errMsg || 'Erro ao finalizar venda.', 'danger');
          });

        $('#btnConfirmarEmitirNFe')
          .off('click')
          .on('click', function() {
            if (isProcessandoEmissaoNFe) return;
            isProcessandoEmissaoNFe = true;

            const $btn = $(this);
            if ($btn.prop('disabled')) {
              isProcessandoEmissaoNFe = false;
              return;
            }
            $btn.prop('disabled', true);

            console.log('DEBUG btnConfirmarEmitirNFe clicado');
            nfeAwaitingEmission = true;

            if (pendingOrcamentoId) {
              console.log('DEBUG imediato emitirNotaFiscal para', pendingOrcamentoId);
              emitirNotaFiscal(pendingOrcamentoId)
                .then(msg => {
                  console.log('DEBUG emitirNotaFiscal sucesso:', msg);
                  showToast(msg, 'success');
                  const modalNFeEl = document.getElementById('modalConfirmarNFe');
                  bootstrap.Modal.getInstance(modalNFeEl).hide();
                })
                .catch(err => {
                  console.error('DEBUG emitirNotaFiscal erro:', err);
                  showToast(err || 'Erro ao emitir nota.', 'danger');
                  $btn.prop('disabled', false);
                })
                .always(() => {
                  isProcessandoEmissaoNFe = false;
                });
            } else {
              console.log('DEBUG aguardando faturamento antes de emitir NF-e');
              isProcessandoEmissaoNFe = false;
            }
          });

        $('#btnNaoEmitirNFe')
          .off('click')
          .on('click', function() {
            const $btn = $(this);
            if ($btn.prop('disabled')) return;
            $btn.prop('disabled', true);

            console.log('DEBUG btnNaoEmitirNFe clicado');
            nfeAwaitingEmission = false;
            const modalNFeEl = document.getElementById('modalConfirmarNFe');
            bootstrap.Modal.getInstance(modalNFeEl).hide();
          });

        $('#modalConfirmarNFe').on('hidden.bs.modal', function() {
          $('#btnConfirmarEmitirNFe').prop('disabled', false);
          $('#btnNaoEmitirNFe').prop('disabled', false);
        });

      });







      function calcularTotais() {
        const sub = state.itensVenda.reduce((a, b) => a + b.subtotal, 0);
        $(prefix + '#subtotalItensValor').text(formatCurrency(sub));

        let dR = parseFloatStrict($(prefix + '#descontoReais').val()),
          dP = parseFloatStrict($(prefix + '#descontoPercent').val()),
          aR = parseFloatStrict($(prefix + '#acrescimosValor').text().replace(/[^0-9,.-]/g, '').replace(',', '.')),
          pL = parseFloatStrict($(prefix + '#precoLivreInput').val());

        let base = pL > 0 ? pL : sub;
        if (dP > 0 && pL === 0) dR += base * (dP / 100);

        $(prefix + '#descontosValor').text(`− ${formatCurrency(dR)}`);
        $(prefix + '#acrescimosValor').text(`+ ${formatCurrency(aR)}`);

        const tot = Math.max(0, base - dR + aR);
        $(prefix + '#totalLiquidoValor').text(formatCurrency(tot));

        calcularValorFaltante();
        console.log('Totais recalculados →', {
          sub,
          dR,
          aR,
          tot
        });
      }

      function calcularValorFaltante() {
        const totL = parseFloatStrict($(prefix + '#totalLiquidoValor').text().replace(/[^0-9,.-]/g, ''));
        const pago = state.parcelasPagamento.reduce((a, b) => a + b.valor, 0);
        const diff = totL - pago;

        $(prefix + '#faltamPagarValor').text(formatCurrency(Math.abs(diff)));
        $(prefix + '#valorFaltanteDisplay').text(formatCurrency(Math.abs(diff)));

        $(prefix + '#modalBtnConfirmarFinalizacao').prop('disabled', Math.abs(diff) >= 0.05);

        if (diff <= 0) {
          $(prefix + '#valorFaltanteDisplay').addClass('valor-faltante-zero');
        } else {
          $(prefix + '#valorFaltanteDisplay').removeClass('valor-faltante-zero');
        }
      }

      function aplicarMascaraMoedaProgressiva(input) {
        let valor = input.val();

        let numeros = valor.replace(/\D/g, '');

        if (!numeros) {
          input.val('0,00');
          return;
        }

        numeros = numeros.replace(/^0+/, '');

        if (!numeros) {
          input.val('0,00');
          return;
        }

        while (numeros.length < 3) {
          numeros = '0' + numeros;
        }

        const inteiro = numeros.slice(0, numeros.length - 2);
        const centavos = numeros.slice(-2);

        const inteiroFormatado = inteiro.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        input.val(`${inteiroFormatado},${centavos}`);
      }

      const inputsMoeda = [
        '#descontoReais',
        '#acrescimoReais',
        '#precoLivreInput',
        '.valor-parcela-input'
      ];

      inputsMoeda.forEach(selector => {
        $(document).on('input', selector, function() {
          aplicarMascaraMoedaProgressiva($(this));
        });

        $(document).on('blur', selector, function() {
          const val = $(this).val();
          if (!val || val.trim() === '') {
            $(this).val('0,00');
          }
        });
      });

      $(function() {
        inputsMoeda.forEach(selector => {
          const $el = $(selector);
          if ($el.length && (!$el.val() || $el.val().trim() === '')) {
            $el.val('0,00');
          }
        });
      });

      $(prefix + '#toggleDescontosFechamento').click(function(e) {
        e.preventDefault();
        $(prefix + '#areaDescontosFechamento').toggleClass('d-none');
        const icon = $(prefix + '#areaDescontosFechamento').hasClass('d-none') ? 'fa-chevron-down' : 'fa-chevron-up';
        $(this).find('i').removeClass('fa-chevron-down fa-chevron-up').addClass(icon);
      });

      $(prefix + '#quantidadeInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          $(prefix + '#produtoSearchInput').focus();
        }
      });

      $(document).keydown(function(e) {
        if (e.ctrlKey) {
          if (e.key === '0') {
            e.preventDefault();
            $(prefix + '#produtoSearchInput').focus();
          }
          if (e.key === '1') {
            e.preventDefault();
            $(prefix + '#quantidadeInput').focus().select();
          }
        }
        if (e.key === 'Escape') {
          e.preventDefault();
          $(prefix + '#btnCancelarOperacao').click();
        }
        if (e.key === 'F4') {
          e.preventDefault();
          $(prefix + '#btnReiniciarVenda').click();
        }
      });
    });
})();
