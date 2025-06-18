<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\Cadastro\PessoaController;
use App\Http\Controllers\Cadastro\ProdutoController;
use App\Http\Controllers\Cadastro\TabelaAuxiliarController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\BancoController;
use App\Http\Controllers\Cadastro\EmpresaController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\CentroCustoController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\GrupoProdutoController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\MarcaProdutoController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\NaturezaOperacaoController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\NcmController;
use App\Http\Controllers\Venda\OrcamentoController;
use App\Http\Controllers\Venda\OrcamentoFinalizacaoController;
use App\Http\Controllers\Cadastro\VendedorController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\Parametro\GeralController;
use App\Http\Controllers\Parametro\EstoqueController;
use App\Http\Controllers\Parametro\FinanceiroController;
use App\Http\Controllers\Parametro\FiscalController;
use App\Http\Controllers\Financeiro\DespesaController;
use App\Http\Controllers\Financeiro\ReceitaController;
use App\Http\Controllers\Financeiro\CaixaMovimentoController;
use \App\Http\Controllers\Financeiro\PeoController;
use App\Http\Controllers\Financeiro\CaixaAberturaController;
use App\Http\Controllers\Compra\EntradaNotaController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\ParametroCartaoController;
use App\Http\Controllers\Cadastro\PerfilFiscalController;
use App\Http\Controllers\Fiscal\NFeNFCeController;
use App\Http\Controllers\Cadastro\TabelaAuxiliar\ObsFiscalController;
use App\Http\Controllers\NPanelController;
use App\Http\Controllers\PdvController;


Auth::routes();





Route::group([
    'middleware' => ['auth', 'client.active','user.active','init']
], function(){
    
    // tela do PDV
    Route::get('/pdv', [PdvController::class, 'index'])
         ->name('pdv.index');
    
    // AJAX do Select2 de produtos
    Route::get('/pdv/search-products', [PdvController::class, 'searchProducts'])
         ->name('pdv.search.products');
    
    // criar orçamento (via AJAX no PDV)
    Route::post('/vendas/orcamento', [OrcamentoController::class, 'store'])
         ->name('vendas.orcamento.store');

    Route::get('/vendas/orcamento/{orcamento}/print', [OrcamentoController::class, 'print'])
         ->name('vendas.orcamento.print');

         

    
     

    Route::put('/user/update/{user}/active', [\App\Http\Controllers\Auth\RegisterController::class, 'updateActive'])
        ->name('user.update.active');


    Route::get('/npanel', [NPanelController::class, 'index'])
        ->middleware(['root.only'])
        ->name('npanel');
    Route::get('/npanel/table', [NPanelController::class, 'table'])
        ->middleware(['root.only', 'ajax.only'])
        ->name('npanel.table');
    Route::post('/npanel', [NPanelController::class, 'store'])
        ->name('npanel.store');
    Route::get('/npanel/create', [NPanelController::class, 'create'])
        ->name('npanel.create');
    Route::put('/npanel/{cliente}',[NPanelController::class, 'update'])
        ->name('npanel.update');
    Route::get('/npanel/{cliente}/edit', [NPanelController::class, 'edit'])
        ->name('npanel.edit');
    Route::get('/npanel/{cliente}/empresas', [NPanelController::class, 'empresas'])
        ->name('npanel.empresas');
    Route::get('/npanel/update_tables_dbs', [NPanelController::class, 'update_tables_dbs'])
        ->name('npanel.update_tables_dbs');    


    Route::get('/test', [\App\Http\Controllers\TestController::class, 'index']);
    Route::get('/relatorio', [\App\Http\Controllers\ReportController::class, 'index']);
    Route::get('/mpdf', [\App\Http\Controllers\ReportController::class, 'mpdf']);
    Route::get('/cupom', [\App\Http\Controllers\Venda\ReportController::class, 'cupom']);
    Route::get('/caixa', [\App\Http\Controllers\Financeiro\CaixaAberturaController::class, 'index']);

    Route::get('/', [HomeController::class, 'index']);
    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');

    //------------
    // PARAMETROS
    //------------
    Route::get('/parametros', [ParametroController::class, 'index'])
        ->name('parametros');
    Route::post('/parametros/fiscal/upload_certificado', [FiscalController::class, 'upload_certificado'])
        ->name('parametros.fiscal.upload_certificado');

    Route::group([
        'middleware' => ['ajax.only']
    ], function(){
        //GERAL
        Route::get('/parametros/geral', [GeralController::class, 'index'])
            ->name('parametros.geral');
        Route::put('/parametros/geral/update_timezone', [GeralController::class, 'updateTimezone'])
            ->name('parametros.geral.update_timezone');
        //FINANCEIRO
        Route::get('/parametros/financeiro', [FinanceiroController::class, 'index'])
            ->name('parametros.financeiro');
        Route::get('/parametros/financeiro/busca_id', [FinanceiroController::class, 'busca_id'])
            ->name('parametros.financeiro.busca_id');    
        Route::post('/parametros/financeiro/salva_informacoes', [FinanceiroController::class, 'updateInformacoes'])
            ->name('parametros.financeiro.salva_informacoes'); 
        Route::post('/parametros/financeiro/resetar_informacoes', [FinanceiroController::class, 'resetarInformacoes'])
            ->name('parametros.financeiro.resetar_informacoes');       
        //ESTOQUE
        Route::get('/parametros/estoque', [EstoqueController::class, 'index'])
            ->name('parametros.estoque');
        Route::put('/parametros/estoque/update_estoque_casas_decimais', [EstoqueController::class, 'updateEstoqueCasasDecimais'])
            ->name('parametros.estoque.update_estoque_casas_decimais');
        Route::put('/parametros/estoque/update_estoque_vender_sem', [EstoqueController::class, 'updateEstoqueVedaSemEstoque'])
            ->name('parametros.estoque.update_estoque_vender_sem');

        //FISCAL


        Route::put('/parametros/fiscal/tpAmb', [FiscalController::class, 'updateTpAmb'])
            ->name('parametros.fiscal.tpAmb');
        Route::put('/parametros/fiscal/update_serie_nota', [FiscalController::class, 'updateSerieNota'])
            ->name('parametros.fiscal.update_serie_nota');
        Route::get('/parametros/fiscal', [FiscalController::class, 'index'])
            ->name('parametros.fiscal');
        Route::put('/parametros/fiscal/update_usuarioapi', [FiscalController::class, 'updateUsuarioApi'])
            ->name('parametros.fiscal.update_usuarioapi');
        Route::put('/parametros/fiscal/update_senhaapi', [FiscalController::class, 'updateSenhaApi'])
            ->name('parametros.fiscal.update_senhaapi');
        Route::put('/parametros/fiscal/usar_api', [FiscalController::class, 'updateUsarApi'])
            ->name('parametros.fiscal.update_usarapi');

        Route::put('/parametros/fiscal/cert_hash', [FiscalController::class, 'updateCertHash'])
            ->name('parametros.fiscal.update_certhash');
        Route::put('/parametros/fiscal/cert_pass', [FiscalController::class, 'updateCertPass'])
            ->name('parametros.fiscal.update_certpass');

        Route::put('/parametros/fiscal/IdCSC', [FiscalController::class, 'updateIdCSC'])
            ->name('parametros.fiscal.update_idcsc');
        Route::put('/parametros/fiscal/CSC', [FiscalController::class, 'updateCSC'])
            ->name('parametros.fiscal.update_csc');    

    });

    // -------------------------------
    //         CADASTROS
    // -------------------------------
    Route::group([
        'as' => 'cadastros.'
    ], function(){

        // CADASTROS->EMPRESA
        Route::get('/cadastros/empresa', [EmpresaController::class, 'index'])
            ->name('empresa.index');
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/empresa/table', [EmpresaController::class, 'table'])
                ->name('empresa.table');
            Route::post('/cadastros/empresa', [EmpresaController::class, 'store'])
                ->name('empresa.store');
            Route::get('/cadastros/empresa/create', [EmpresaController::class, 'create'])
                ->name('empresa.create');
            Route::put('/cadastros/empresa/{empresa}',[EmpresaController::class, 'update'])
                ->name('empresa.update');
            Route::get('/cadastros/empresa/{empresa}/edit', [EmpresaController::class, 'edit'])
                ->name('empresa.edit');
        });

        Route::get('/cadastros/empresa/selecionar_empresa_lista', [EmpresaController::class, 'selecionar_empresa_lista'])
            ->middleware(['ajax.only'])
            ->name('empresa.selecionar_empresa_list');
        Route::post('/cadastros/empresa/selecionar_empresa/{empresa}', [EmpresaController::class, 'selecionar_empresa'])
            ->name('empresa.selecionar_empresa');


        // ----------------------
        //   CADASTROS->PESSOAS
        // ----------------------
        Route::get('/cadastros/pessoa', [PessoaController::class, 'index'])
            ->name('pessoa.index');

        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/pessoa/table', [PessoaController::class, 'table'])
                ->name('pessoa.table');
            Route::post('/cadastros/pessoa', [PessoaController::class, 'store'])
                ->name('pessoa.store');
            Route::get('/cadastros/pessoa/create', [PessoaController::class, 'create'])
                ->name('pessoa.create');
            Route::put('/cadastros/pessoa/{pessoa}',[PessoaController::class, 'update'])
                ->name('pessoa.update');
            Route::get('/cadastros/pessoa/{pessoa}/edit', [PessoaController::class, 'edit'])
                ->name('pessoa.edit');
        });

        // --------------------------
        //   CADASTROS->VENDEDORES
        // --------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/vendedor/table', [VendedorController::class, 'table'])
                ->name('vendedor.table');
            Route::post('/cadastros/vendedor', [VendedorController::class, 'adicionar'])
                ->name('vendedor.adicionar');
            Route::get('/cadastros/vendedor', [VendedorController::class, 'index'])
                ->name('vendedor.index');
            Route::delete('/cadastros/vendedor/{id}', [VendedorController::class,'remover'])
                ->name('vendedor.remover');
        });

        // ----------------------
        //   CADASTROS->PRODUTOS
        // ----------------------
        Route::get('/cadastros/produto', [ProdutoController::class, 'index'])
            ->name('produto.index');

        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/produto/table', [ProdutoController::class, 'table'])
                ->name('produto.table');
            Route::post('/cadastros/produto', [ProdutoController::class, 'store'])
                ->name('produto.store');
            Route::get('/cadastros/produto/create', [ProdutoController::class, 'create'])
                ->name('produto.create');
            Route::put('/cadastros/produto/{produto}',[ProdutoController::class, 'update'])
                ->name('produto.update');
            Route::get('/cadastros/produto/{produto}/edit', [ProdutoController::class, 'edit'])
                ->name('produto.edit');
            Route::get('/cadastros/produto/{produto}/form_ajuste_estoque', [ProdutoController::class, 'form_ajuste_estoque'])
                ->name('produto.form_ajuste_estoque');
            Route::post('/cadastros/produto/ajuste_estoque', [ProdutoController::class, 'ajuste_estoque'])
                ->name('produto.ajuste_estoque');
            Route::get('/cadastros/produto/get_produto/{per_field?}/{produto?}', [ProdutoController::class, 'getProduto'])
                ->name('produto.get_produto');
            Route::get('/cadastros/produto/produtos_cadastrados', [ProdutoController::class, 'produtos_cadastrados'])
                ->name('produto.produtos_cadastrados');
            Route::get('/cadastros/produto/produtos_sem_estoque', [ProdutoController::class, 'produtos_sem_estoque'])
                ->name('produto.produtos_sem_estoque');
            Route::get('/cadastros/produto/produtos_para_venda', [ProdutoController::class, 'produtos_para_venda'])
                ->name('produto.produtos_para_venda');    
        });

        //--------------------------
        // CADASTROS->PERFIL FISCAL
        //--------------------------
        Route::get('/cadastros/perfil_fiscal', [PerfilFiscalController::class, 'index'])
            ->name('perfil_fiscal.index');
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){

            Route::get('/cadastros/perfil_fiscal/table', [PerfilFiscalController::class, 'table'])
                ->name('perfil_fiscal.table');
            Route::post('/cadastros/perfil_fiscal', [PerfilFiscalController::class, 'store'])
                ->name('perfil_fiscal.store');
            Route::get('/cadastros/perfil_fiscal/create', [PerfilFiscalController::class, 'create'])
                ->name('perfil_fiscal.create');
            Route::put('/cadastros/perfil_fiscal/{perfil_fiscal}',[PerfilFiscalController::class, 'update'])
                ->name('perfil_fiscal.update');
            Route::get('/cadastros/perfil_fiscal/{perfil_fiscal}/edit', [PerfilFiscalController::class, 'edit'])
                ->name('perfil_fiscal.edit');

        });

        // CADASTROS->TABELAS AUXILIARES
        Route::get('/cadastros/tabelas_auxiliares', [TabelaAuxiliarController::class, 'index'])
            ->name('tabelas_auxiliares.index');

        // -------------------------------------
        // CADASTROS->TABELAS AUXILIARES->BANCOS
        // -------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/tabelas_auxiliares/banco/table', [BancoController::class, 'table'])
                ->name('tabelas_auxiliares.banco.table');
            Route::post('/cadastros/tabelas_auxiliares/banco', [BancoController::class, 'store'])
                ->name('tabelas_auxiliares.banco.store');
            Route::get('/cadastros/tabelas_auxiliares/banco', [BancoController::class, 'index'])
                ->name('tabelas_auxiliares.banco.index');
            Route::get('/cadastros/tabelas_auxiliares/banco/create', [BancoController::class, 'create'])
                ->name('tabelas_auxiliares.banco.create');
            Route::put('/cadastros/tabelas_auxiliares/banco/{banco}',[BancoController::class, 'update'])
                ->name('tabelas_auxiliares.banco.update');
            Route::get('/cadastros/tabelas_auxiliares/banco/{banco}/edit', [BancoController::class, 'edit'])
                ->name('tabelas_auxiliares.banco.edit');
        });

        // --------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->CENTRO CUSTOS
        // --------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/tabelas_auxiliares/centro_custo/table', [CentroCustoController::class, 'table'])
                ->name('tabelas_auxiliares.centro_custo.table');
            Route::post('/cadastros/tabelas_auxiliares/centro_custo', [CentroCustoController::class, 'store'])
                ->name('tabelas_auxiliares.centro_custo.store');
            Route::get('/cadastros/tabelas_auxiliares/centro_custo', [CentroCustoController::class, 'index'])
                ->name('tabelas_auxiliares.centro_custo.index');
            Route::get('/cadastros/tabelas_auxiliares/centro_custo/create', [CentroCustoController::class, 'create'])
                ->name('tabelas_auxiliares.centro_custo.create');
            Route::put('/cadastros/tabelas_auxiliares/centro_custo/{centroCusto}',[CentroCustoController::class, 'update'])
                ->name('tabelas_auxiliares.centro_custo.update');
            Route::get('/cadastros/tabelas_auxiliares/centro_custo/{centroCusto}/edit', [CentroCustoController::class, 'edit'])
                ->name('tabelas_auxiliares.centro_custo.edit');
        });

        // ------------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->GRUPOS PRODUTOS
        // ------------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/tabelas_auxiliares/grupo_produto/table', [GrupoProdutoController::class, 'table'])
                ->name('tabelas_auxiliares.grupo_produto.table');
            Route::post('/cadastros/tabelas_auxiliares/grupo_produto', [GrupoProdutoController::class, 'store'])
                ->name('tabelas_auxiliares.grupo_produto.store');
            Route::get('/cadastros/tabelas_auxiliares/grupo_produto', [GrupoProdutoController::class, 'index'])
                ->name('tabelas_auxiliares.grupo_produto.index');
            Route::get('/cadastros/tabelas_auxiliares/grupo_produto/create', [GrupoProdutoController::class, 'create'])
                ->name('tabelas_auxiliares.grupo_produto.create');
            Route::put('/cadastros/tabelas_auxiliares/grupo_produto/{grupoProduto}',[GrupoProdutoController::class, 'update'])
                ->name('tabelas_auxiliares.grupo_produto.update');
            Route::get('/cadastros/tabelas_auxiliares/grupo_produto/{grupoProduto}/edit', [GrupoProdutoController::class, 'edit'])
                ->name('tabelas_auxiliares.grupo_produto.edit');
        });

        // ----------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->MARCAS PRODUTOS
        // ----------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function (){
            Route::get('/cadastros/tabelas_auxiliares/marca_produto/table', [MarcaProdutoController::class, 'table'])
                ->name('tabelas_auxiliares.marca_produto.table');
            Route::post('/cadastros/tabelas_auxiliares/marca_produto', [MarcaProdutoController::class, 'store'])
                ->name('tabelas_auxiliares.marca_produto.store');
            Route::get('/cadastros/tabelas_auxiliares/marca_produto', [MarcaProdutoController::class, 'index'])
                ->name('tabelas_auxiliares.marca_produto.index');
            Route::get('/cadastros/tabelas_auxiliares/marca_produto/create', [MarcaProdutoController::class, 'create'])
                ->name('tabelas_auxiliares.marca_produto.create');
            Route::put('/cadastros/tabelas_auxiliares/marca_produto/{marcaProduto}',[MarcaProdutoController::class, 'update'])
                ->name('tabelas_auxiliares.marca_produto.update');
            Route::get('/cadastros/tabelas_auxiliares/marca_produto/{marcaProduto}/edit', [MarcaProdutoController::class, 'edit'])
                ->name('tabelas_auxiliares.marca_produto.edit');
        });

        // --------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->NATUREZA OPERACAO
        // --------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/tabelas_auxiliares/natureza_operacao/table', [NaturezaOperacaoController::class, 'table'])
                ->name('tabelas_auxiliares.natureza_operacao.table');
            Route::post('/cadastros/tabelas_auxiliares/natureza_operacao', [NaturezaOperacaoController::class, 'store'])
                ->name('tabelas_auxiliares.natureza_operacao.store');
            Route::get('/cadastros/tabelas_auxiliares/natureza_operacao', [NaturezaOperacaoController::class, 'index'])
                ->name('tabelas_auxiliares.natureza_operacao.index');
            Route::get('/cadastros/tabelas_auxiliares/natureza_operacao/create', [NaturezaOperacaoController::class, 'create'])
                ->name('tabelas_auxiliares.natureza_operacao.create');
            Route::put('/cadastros/tabelas_auxiliares/natureza_operacao/{naturezaOperacao}',[NaturezaOperacaoController::class, 'update'])
                ->name('tabelas_auxiliares.natureza_operacao.update');
            Route::get('/cadastros/tabelas_auxiliares/natureza_operacao/{naturezaOperacao}/edit', [NaturezaOperacaoController::class, 'edit'])
                ->name('tabelas_auxiliares.natureza_operacao.edit');
        });

        // --------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->NCM
        // --------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function (){
            Route::get('/cadastros/tabelas_auxiliares/ncm/table', [NcmController::class, 'table'])
                ->name('tabelas_auxiliares.ncm.table');
            Route::post('/cadastros/tabelas_auxiliares/ncm', [NcmController::class, 'store'])
                ->name('tabelas_auxiliares.ncm.store');
            Route::get('/cadastros/tabelas_auxiliares/ncm', [NcmController::class, 'index'])
                ->name('tabelas_auxiliares.ncm.index');
            Route::get('/cadastros/tabelas_auxiliares/ncm/create', [NcmController::class, 'create'])
                ->name('tabelas_auxiliares.ncm.create');
            Route::put('/cadastros/tabelas_auxiliares/ncm/{ncm}',[NcmController::class, 'update'])
                ->name('tabelas_auxiliares.ncm.update');
            Route::get('/cadastros/tabelas_auxiliares/ncm/{ncm}/edit', [NcmController::class, 'edit'])
                ->name('tabelas_auxiliares.ncm.edit');
        });

        // --------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->CAIXA
        // --------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function (){
            Route::get('/cadastros/tabelas_auxiliares/caixa/table', [\App\Http\Controllers\Cadastro\TabelaAuxiliar\CaixaController::class, 'table'])
                ->name('tabelas_auxiliares.caixa.table');
            Route::post('/cadastros/tabelas_auxiliares/caixa', [\App\Http\Controllers\Cadastro\TabelaAuxiliar\CaixaController::class, 'store'])
                ->name('tabelas_auxiliares.caixa.store');
            Route::get('/cadastros/tabelas_auxiliares/caixa', [\App\Http\Controllers\Cadastro\TabelaAuxiliar\CaixaController::class, 'index'])
                ->name('tabelas_auxiliares.caixa.index');
            Route::get('/cadastros/tabelas_auxiliares/caixa/create', [\App\Http\Controllers\Cadastro\TabelaAuxiliar\CaixaController::class, 'create'])
                ->name('tabelas_auxiliares.caixa.create');
            Route::put('/cadastros/tabelas_auxiliares/caixa/{caixa}',[\App\Http\Controllers\Cadastro\TabelaAuxiliar\CaixaController::class, 'update'])
                ->name('tabelas_auxiliares.caixa.update');
            Route::get('/cadastros/tabelas_auxiliares/caixa/{caixa}/edit', [\App\Http\Controllers\Cadastro\TabelaAuxiliar\CaixaController::class, 'edit'])
                ->name('tabelas_auxiliares.caixa.edit');
        });

        // -----------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->PARAMETRO CARTAO
        // -----------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function (){
            Route::get('/cadastros/tabelas_auxiliares/parametro_cartao/table', [ParametroCartaoController::class, 'table'])
                ->name('tabelas_auxiliares.parametro_cartao.table');
            Route::post('/cadastros/tabelas_auxiliares/parametro_cartao', [ParametroCartaoController::class, 'store'])
                ->name('tabelas_auxiliares.parametro_cartao.store');
            Route::get('/cadastros/tabelas_auxiliares/parametro_cartao', [ParametroCartaoController::class, 'index'])
                ->name('tabelas_auxiliares.parametro_cartao.index');
            Route::get('/cadastros/tabelas_auxiliares/parametro_cartao/create', [ParametroCartaoController::class, 'create'])
                ->name('tabelas_auxiliares.parametro_cartao.create');
            Route::put('/cadastros/tabelas_auxiliares/parametro_cartao/{parametro_cartao}',[ParametroCartaoController::class, 'update'])
                ->name('tabelas_auxiliares.parametro_cartao.update');
            Route::get('/cadastros/tabelas_auxiliares/parametro_cartao/{parametro_cartao}/edit', [ParametroCartaoController::class, 'edit'])
                ->name('tabelas_auxiliares.parametro_cartao.edit');
        });

        // -----------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->OBS FISCAL
        // -----------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function (){
            Route::get('/cadastros/tabelas_auxiliares/obs_fiscal', [ObsFiscalController::class, 'index'])
                ->name('tabelas_auxiliares.obs_fiscal.index');
            Route::post('/cadastros/tabelas_auxiliares/obs_fiscal', [ObsFiscalController::class, 'store'])
                ->name('tabelas_auxiliares.obs_fiscal.store');
        });

        // -------------------------------------------------
        // CADASTROS->TABELAS AUXILIARES->TIPOS DE PAGAMENTO
        // -------------------------------------------------
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/cadastros/tabelas_auxiliares/tipos_pagamento', [App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'index'])
                ->name('tabelas_auxiliares.tipos_pagamento.index');
            Route::get('/cadastros/tabelas_auxiliares/tipos_pagamento/table', [App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'table'])
                ->name('tabelas_auxiliares.tipos_pagamento.table');
            Route::get('/cadastros/tabelas_auxiliares/tipos_pagamento/{pagamento}/edit', [App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'edit'])
                ->name('tabelas_auxiliares.tipos_pagamento.edit');
            Route::put('/cadastros/tabelas_auxiliares/tipos_pagamento/{pagamento}',[App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'update'])
                ->name('tabelas_auxiliares.tipos_pagamento.update');
            Route::put('/cadastros/tabelas_auxiliares/tipos_pagamento_novo',[App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'criar_form'])
                ->name('tabelas_auxiliares.tipos_pagamento.criar_form');
            Route::get('/cadastros/tabelas_auxiliares/tipos_pagamento/create', [App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'create'])
                ->name('tabelas_auxiliares.tipos_pagamento.create');
            Route::put('/cadastros/tabelas_auxiliares/tipos_pagamento/excluir/{pagamento}',[App\Http\Controllers\Cadastro\TabelaAuxiliar\TiposPagamentoController::class, 'excluir'])
                ->name('tabelas_auxiliares.tipos_pagamento.excluir');        
        });

        // ------------------------------
        // RELATORIOS
        // ------------------------------
        Route::group([

        ], function (){
            Route::get('/relatorios/cadastros', [\App\Http\Controllers\Cadastro\ReportController::class, 'index'])
                ->name('report.cadastros.index')
                ->middleware(['ajax.only']);

            Route::get('/relatorios/cadastros/pessoas', [\App\Http\Controllers\Cadastro\ReportController::class, 'pessoas'])
                ->name('report.cadastros.pessoas');
            Route::get('/relatorios/cadastros/pessoas/aniversariantes', [\App\Http\Controllers\Cadastro\ReportController::class, 'aniversariantes'])
                ->name('report.cadastros.pessoas.aniversariantes');
            Route::get('/relatorios/cadastros/produtos', [\App\Http\Controllers\Cadastro\ReportController::class, 'produtos'])
                ->name('report.cadastros.produtos');
        });

    });

    //---------------------
    //       COMPRAS
    //---------------------
    Route::group([
       'as' => 'compras.'
    ], function(){

        Route::get('/compras/entrada_nota/table', [EntradaNotaController::class, 'table'])
            ->name('nota_entrada.table');
        Route::get('/compras/entrada_nota', [EntradaNotaController::class, 'index'])
            ->name('nota_entrada.index');
        Route::get('/compras/entrada_nota/serializar_xml', [EntradaNotaController::class, 'serializarXml'])
            ->name('nota_entrada.serializar_xml');
        Route::post('/compras/entrada_nota/upload_xml', [EntradaNotaController::class, 'upload_xml'])
            ->name('nota_entrada.upload_xml');
        Route::post('/compras/entrada_nota', [EntradaNotaController::class, 'store'])
            ->name('nota_entrada.store');
        Route::get('/compras/entrada_nota/create', [EntradaNotaController::class, 'create'])
            ->name('nota_entrada.create');
        Route::get('/compras/entrada_nota/{id}/edit', [EntradaNotaController::class, 'edit'])
            ->name('nota_entrada.edit');
        Route::put('/compras/entrada_nota/{id}',[EntradaNotaController::class, 'update'])
            ->name('nota_entrada.update');
        Route::put('/compras/entrada_nota/excluir_nota/{id}', [EntradaNotaController::class, 'excluir_nota'])
            ->name('nota_entrada.excluir');

    });

    //---------------------
    //       VENDAS
    //---------------------
    Route::group([
        'as' => 'vendas.'
    ], function(){

        Route::get('/vendas', [OrcamentoController::class, 'index'])
            ->name('index');

        Route::group([
            'middleware' => ['ajax.only']
        ], function(){

            // ORCAMENTO
            Route::get('/vendas/orcamento/table', [OrcamentoController::class, 'table'])
                ->name('orcamento.table');
            Route::post('/vendas/orcamento', [OrcamentoController::class, 'store'])
                ->name('orcamento.store');
            Route::get('/vendas/orcamento/create', [OrcamentoController::class, 'create'])
                ->name('orcamento.create');
            Route::get('/vendas/orcamento/{orcamento}/edit', [OrcamentoController::class, 'edit'])
                ->name('orcamento.edit');
            Route::put('/vendas/orcamento/{orcamento}',[OrcamentoController::class, 'update'])
                ->name('orcamento.update');


            Route::get('/vendas/orcamento/total_vendas_hoje', [OrcamentoController::class, 'total_vendas_hoje'])
                ->name('orcamento.total_vendas_hoje');   

            Route::get('/vendas/orcamento/vendas_mes_grafico', [OrcamentoController::class, 'vendas_mes_grafico'])
                ->name('orcamento.vendas_mes_grafico');

            Route::get('/vendas/orcamento/orcamentos_criados_graf', [OrcamentoController::class, 'orcamentos_criados_graf'])
                ->name('orcamento.orcamentos_criados_graf');    

            // faturar
            Route::post('/vendas/orcamento/store_faturar', [OrcamentoController::class, 'store_faturar'])
                ->name('orcamento.store_faturar');
            Route::get('/vendas/orcamento/{orcamento}/create_faturar', [OrcamentoController::class, 'create_faturar'])
                ->name('orcamento.create_faturar');

            // cancelar
            Route::get('/vendas/orcamento/{orcamento}/create_cancelar', [OrcamentoController::class, 'create_cancelar'])
                ->name('orcamento.create_cancelar');
            Route::post('/vendas/orcamento/store_cancelar', [OrcamentoController::class, 'store_cancelar'])
                ->name('orcamento.store_cancelar');

            // estornar
            Route::get('/vendas/orcamento/{orcamento}/create_estornar', [OrcamentoController::class, 'create_estornar'])
                ->name('orcamento.create_estornar');
            Route::post('/vendas/orcamento/store_estornar', [OrcamentoController::class, 'store_estornar'])
                ->name('orcamento.store_estornar');

            // ver finalizacao
            Route::get('/vendas/orcamento/{orcamento}/show_finalizacao', [OrcamentoController::class, 'show_finalizacao'])
                ->name('orcamento.show_finalizacao');

            // decidir o que vai fazer com o orçamento
            Route::get('/vendas/orcamento/finalizar/oque_fazer', [OrcamentoFinalizacaoController::class, 'oque_fazer'])
                ->name('orcamento.finalizar.oque_fazer'); 
                
            // caso tenha clicado em FATURAR no final do orçamento
            Route::get('/vendas/orcamento/finalizar/{orcamento}/clicou_faturou', [OrcamentoFinalizacaoController::class, 'clicou_faturou'])
            ->name('orcamento.finalizar.clicou_faturou');
            
            // excluir um item do orçamento
            Route::get('/vendas/orcamento/excluir_item', [OrcamentoController::class, 'excluir_item'])
            ->name('orcamento.excluir_item');

            // procura a forma de pagamento para e faz a validação se tem C.P personalizadas
            Route::get('/vendas/orcamento/pesquisa_forma_pagamento', [OrcamentoFinalizacaoController::class, 'pesquisa_forma_pagamento'])
            ->name('orcamento.pesquisa_forma_pagamento');

            // faturar no momento de finalização
            Route::post('/vendas/orcamento/finalizar/store_faturar', [OrcamentoFinalizacaoController::class, 'store_faturar'])
                ->name('orcamento.finalizar.store_faturar');
                
            // emitir nfe na tela de finalização do orçamento/venda
            Route::post('/vendas/orcamento/finalizar/store_emitir_nota', [OrcamentoFinalizacaoController::class, 'store_emitir_nota'])
            ->name('orcamento.finalizar.store_emitir_nota');

            // emitir nfce na tela de finalização do orçamento/venda
            Route::post('/vendas/orcamento/finalizar/store_emitir_notanfce', [OrcamentoFinalizacaoController::class, 'store_emitir_notanfce'])
            ->name('orcamento.finalizar.store_emitir_notanfce');

            // finalizar
            Route::get('/vendas/orcamento/finalizar/create', [OrcamentoFinalizacaoController::class, 'create'])
                ->name('orcamento.finalizar.create');
            Route::post('/vendas/orcamento/finalizar', [OrcamentoFinalizacaoController::class, 'store'])
                ->name('orcamento.finalizar.store');

            //Enviar email para clientes e usuarios
            Route::get('/vendas/orcamento/email_create', [OrcamentoController::class, 'email_create'])
            ->name('orcamento.email_create');
            Route::post('/vendas/orcamento/email_store', [OrcamentoController::class, 'email_store'])
            ->name('orcamento.email_store');   

        });


        // ------------------------------
        // RELATORIOS
        // ------------------------------
        Route::group([

        ], function(){
            Route::get('/vendas/orcamento/{orcamento_id}/imprimir_cupom', [\App\Http\Controllers\Venda\ReportController::class,'cupom'])
                ->name('report.cupom');
            Route::get('/vendas/orcamento/{orcamento_id}/imprimir_transferencia', [\App\Http\Controllers\Venda\ReportController::class,'imprimir_transferencia'])
                ->name('report.imprimir_transferencia');
            Route::get('/vendas/orcamento/{orcamento_id}/imprimir_orcamento', [\App\Http\Controllers\Venda\ReportController::class,'orcamento'])
                ->name('report.orcamento');

            Route::get('/relatorios/vendas', [\App\Http\Controllers\Venda\ReportController::class, 'index'])
                ->name('report.index')
                ->middleware(['ajax.only']);

            Route::get('/relatorios/vendas/analise_vendas', [\App\Http\Controllers\Venda\ReportController::class, 'analise_vendas'])
                ->name('report.analise_vendas');
            
            Route::get('/relatorios/vendas/download_xml', [\App\Http\Controllers\Venda\ReportController::class, 'download_xml'])
                ->name('report.download_xml');


        });

    });

    //----------------------
    //      FISCAL
    //----------------------
    Route::group([
        'as' => 'fiscal.'
    ], function(){

        Route::get('/fiscal/proximo_nro_nota', [NFeNFCeController::class, 'proximo_nro_nota'])
            ->name('proximo_nro_nota')
            ->middleware(['ajax.only']);

        /*INUTILIZAÇÃO FISCAL*/
        Route::get('/fiscal/inutilizacao', [NFeNFCeController::class, 'inutilizacao_create'])
            ->name('inutilizacao_create')   
            ->middleware(['ajax.only']);
        Route::get('/fiscal/inutilizacao_tabela', [NFeNFCeController::class, 'inutilizacao_tabela_create'])
            ->name('inutilizacao_tabela_create');
            

        /*GERAR NOTA FISCAL*/
        Route::get('/fiscal/{orcamento_id}/gerar_xml', [NFeNFCeController::class, 'create_gerar_xml'])
            ->name('gerar_xml_create')
            ->middleware(['ajax.only']);
        Route::post('/fiscal/gerar_xml/store', [NFeNFCeController::class, 'store_gerar_xml'])
            ->name('gerar_xml_store')
            ->middleware(['ajax.only']);

        /*CANCELAR NOTA FISCAL*/
        Route::get('/fiscal/{nf_id}/cancelar_nf', [NFeNFCeController::class, 'create_cancelar_nf'])
            ->name('cancelar_nf_create')
            ->middleware(['ajax.only']);
        Route::post('/fiscal/cancelar_nf/store', [NFeNFCeController::class, 'store_cancelar_xml'])
            ->name('cancelar_nf_store')
            ->middleware(['ajax.only']);

        /*DOWNLOAD XML*/
        Route::get('/fiscal/download_xml', [NFeNFCeController::class, 'download_xml'])
            ->name('download_xml');

        /* CARTA CORRECAO NOTA FISCAL*/
        Route::get('/fiscal/{nf_id}/correcao_nfe', [NFeNFCeController::class, 'create_correcao_nfe'])
            ->name('correcao_nfe_create')   
            ->middleware(['ajax.only']);
        Route::post('/fiscal/correcao_nfe/store', [NFeNFCeController::class, 'store_correcao_nfe'])
            ->name('correcao_nfe_store')
            ->middleware(['ajax.only']);

            /* IMPRIMIR CARTA CORRECAO NOTA FISCAL*/
        Route::get('/fiscal/imprimir_correcao_nfe', [NFeNFCeController::class, 'imprimir_correcao_nfe'])
               ->name('correcao_nfe_imprimir');

        Route::get('/fiscal/cert_validade', [NFeNFCeController::class, 'apiFiscal_validade_certificado'])
            ->name('cert_validade')
            ->middleware(['ajax.only']);

        Route::get('/fiscal/info_certificado', [NFeNFCeController::class, 'apiFiscal_info_certificado'])
            ->name('info_certificado')
            ->middleware(['ajax.only']);

        Route::get('/fiscal/danfe', [NFeNFCeController::class, 'apiFiscal_danfe'])
            ->name('danfe');

        /* DADOS PARA TELA INICIAL*/    
        Route::get('/fiscal/nfe_nfce_criadas', [NFeNFCeController::class, 'nfe_nfce_criadas'])
            ->name('nfe_nfce_criadas');

        Route::get('/fiscal/nfe_nfce_canceladas', [NFeNFCeController::class, 'nfe_nfce_canceladas'])
            ->name('nfe_nfce_canceladas');
            
        Route::get('/fiscal/nfe_nfce_enviadas', [NFeNFCeController::class, 'nfe_nfce_enviadas'])
            ->name('nfe_nfce_enviadas');    


        // NFe
        Route::group([
            'as' => 'nfe.'
        ], function() {

            Route::group([
                'middleware' => ['ajax.only']
            ], function() {
                Route::get('/fiscal/nfe/status_servico', [NFeNFCeController::class, 'apiFiscal_status_servico'])
                    ->name('status_servico')
                    ->middleware(['ajax.only']);
            });

        });

        // NFCe
        Route::group([
            'as' => 'nfce.'
        ], function() {

        });

    });



    //-------------------------
    // FINANCEIRO
    //-------------------------
    Route::group([
       'as' => 'financeiro.'
    ], function(){

        // DESPESAS
        Route::get('/financeiro/despesa', [DespesaController::class, 'index'])
            ->name('despesa.index');
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/financeiro/despesa/table', [DespesaController::class, 'table'])
                ->name('despesa.table');
            // CREATE
            Route::post('/financeiro/despesa', [DespesaController::class, 'store'])
                ->name('despesa.store');
            Route::get('/financeiro/despesa/create', [DespesaController::class, 'create'])
                ->name('despesa.create');
            // EDITAR
            Route::put('/financeiro/despesa/{despesa}',[DespesaController::class, 'update'])
                ->name('despesa.update');
            Route::get('/financeiro/despesa/{despesa}/edit', [DespesaController::class, 'edit'])
                ->name('despesa.edit');
            // CANCELAR
            Route::get('/financeiro/despesa/{despesa}/cancelar', [DespesaController::class, 'form_cancelar'])
                ->name('despesa.form_cancelar');
            Route::post('/financeiro/despesa/cancelar', [DespesaController::class, 'cancelar'])
                ->name('despesa.cancelar');
            // LIQUIDAR
            Route::get('/financeiro/despesa/{despesa}/liquidar', [DespesaController::class, 'form_liquidar'])
                ->name('despesa.form_liquidar');
            Route::post('/financeiro/despesa/liquidar', [DespesaController::class, 'liquidar'])
                ->name('despesa.liquidar');
            // EXTRATO
            Route::get('/financeiro/despesa/{despesa}/extrato', [DespesaController::class, 'extrato'])
                ->name('despesa.extrato');
            Route::post('/financeiro/despesa/{despesa}/{parcela}/estornar', [DespesaController::class, 'estornar'])
                ->name('despesa.estornar');

            Route::get('/financeiro/despesa/total_despesas_em_atrasos', [DespesaController::class, 'total_despesas_em_atrasos'])
                ->name('receita.total_despesas_em_atrasos');
        });


        // RECEITAS
        Route::get('/financeiro/receita', [ReceitaController::class, 'index'])
            ->name('receita.index');
        Route::group([
            'middleware' => ['ajax.only']
        ], function(){
            Route::get('/financeiro/receita/table', [ReceitaController::class, 'table'])
                ->name('receita.table');
            // CREATE
            Route::post('/financeiro/receita', [ReceitaController::class, 'store'])
                ->name('receita.store');
            Route::get('/financeiro/receita/create', [ReceitaController::class, 'create'])
                ->name('receita.create');
            // EDITAR
            Route::put('/financeiro/receita/{receita}',[ReceitaController::class, 'update'])
                ->name('receita.update');
            Route::get('/financeiro/receita/{receita}/edit', [ReceitaController::class, 'edit'])
                ->name('receita.edit');
            // CANCELAR
            Route::get('/financeiro/receita/{receita}/cancelar', [ReceitaController::class, 'form_cancelar'])
                ->name('receita.form_cancelar');
            Route::post('/financeiro/receita/cancelar', [ReceitaController::class, 'cancelar'])
                ->name('receita.cancelar');
            // LIQUIDAR
            Route::get('/financeiro/receita/{receita}/liquidar', [ReceitaController::class, 'form_liquidar'])
                ->name('receita.form_liquidar');
            Route::post('/financeiro/receita/liquidar', [ReceitaController::class, 'liquidar'])
                ->name('receita.liquidar');
            // EXTRATO
            Route::get('/financeiro/receita/{receita}/extrato', [ReceitaController::class, 'extrato'])
                ->name('receita.extrato');
            Route::post('/financeiro/receita/{receita}/{parcela}/estornar', [ReceitaController::class, 'estornar'])
                ->name('receita.estornar');

            Route::get('/financeiro/receita/total_receitas_em_atrasos', [ReceitaController::class, 'total_receitas_em_atrasos'])
                ->name('receita.total_receitas_em_atrasos');    
        });


        // CAIXA MOVIMENTO
        Route::get('/financeiro/caixa_movimento', [CaixaMovimentoController::class, 'index'])
            ->name('caixa_movimento.index');
        Route::get('/financeiro/caixa_movimento/table', [CaixaMovimentoController::class, 'table'])
            ->name('caixa_movimento.table');

        // CAIXA-ABERTURA
        Route::get('/financeiro/caixa_abertura/caixa_is_aberto', [CaixaAberturaController::class, 'checkCaixaIsAberto'])
            ->name('caixa_abertura.is_aberto');
        Route::get('/financeiro/caixa_abertura/caixa_is_aberto_current_user', [CaixaAberturaController::class, 'checkCaixaIsAbertoCurrentUser'])
            ->name('caixa_abertura.is_aberto_current_user');
        Route::get('/financeiro/caixa_abertura/fechamento/open', [CaixaAberturaController::class, 'openFechamento'])
            ->name('caixa_abertura.fechamento.open_fechamento');

        Route::put('/financeiro/caixa_abertura/fechamento/put', [CaixaAberturaController::class, 'putFechamento'])
            ->name('caixa_abertura.fechamento.put_fechamento');

        Route::get('/financeiro/caixa_abertura/abertura/open', [CaixaAberturaController::class, 'openAbertura'])
            ->name('caixa_abertura.abertura.open_abertura');

        Route::post('/financeiro/caixa_abertura/abertura', [CaixaAberturaController::class, 'postAbertura'])
            ->name('caixa_abertura.abertura.post_abertura');

        // BANCO
        Route::get('/financeiro/banco', [\App\Http\Controllers\Financeiro\BancoController::class, 'index'])
            ->name('banco.index');
        Route::get('/financeiro/banco_table', [\App\Http\Controllers\Financeiro\BancoController::class, 'table'])
            ->name('banco.banco_table');


        //CALCULO DE PEO
        Route::get('/financeiro/calculo_peo', [PeoController::class, 'index'])
            ->name('calculo_peo.index');   
        Route::get('/financeiro/calculo_peo/table', [PeoController::class, 'table'])
            ->name('calculo_peo.peo.table');
        Route::get('/financeiro/calculo_peo/grafico_peo', [PeoController::class, 'grafico_peo'])
            ->name('calculo_peo.peo.grafico_peo')
            ->middleware(['ajax.only']);     

        // ------------------------------
        // RELATORIOS
        // ------------------------------
        Route::group([
            
        ], function (){
            Route::get('/relatorios/financeiro', [\App\Http\Controllers\Financeiro\ReportController::class, 'index'])
                ->name('report.financeiro.index')
                ->middleware(['ajax.only']);

                Route::get('/relatorios/financeiro/contas_receber', [\App\Http\Controllers\Financeiro\ReportController::class, 'contas_receber'])
                ->name('report.contas_receber');
                Route::get('/relatorios/financeiro/contas_pagar', [\App\Http\Controllers\Financeiro\ReportController::class, 'contas_pagar'])
                ->name('report.contas_pagar'); 
                Route::get('/relatorios/financeiro/contas_recebidas', [\App\Http\Controllers\Financeiro\ReportController::class, 'contas_recebidas'])
                ->name('report.contas_recebidas'); 
                Route::get('/relatorios/financeiro/contas_pagas', [\App\Http\Controllers\Financeiro\ReportController::class, 'contas_pagas'])
                ->name('report.contas_pagas');  

            // RECIBO DO PAGAMENTO FEITO PARA CLIENTE   
             Route::get('/financeiro/receita/recibo_cliente', [ReceitaController::class, 'recibo_cliente'])
            ->name('receita.recibo_cliente');
            // RECIBO DO PAGAMENTO FEITO PARA EMPRESA   
            Route::get('/financeiro/despesa/recibo_empresa', [DespesaController::class, 'recibo_empresa'])
            ->name('despesa.recibo_empresa');


            // CONTRATO PARA OS CLIENTES 
            Route::get('/documentos/contratos', [\App\Http\Controllers\Contrato\ContratoController::class, 'index'])
            ->name('documentos.index');

            //VERIFICAÇÃO DE ABAS PARA O CONTRATO
            Route::get('/documentos/verifica', [\App\Http\Controllers\Contrato\ContratoController::class, 'verificacao'])
            ->name('documentos.verificacao');

            //DEMAIS ABAS PARA PREENCHIMENTO DE INFORMAÇÕES
            Route::get('/documentos/contratos/info_contratada', [\App\Http\Controllers\Contrato\ContratoController::class, 'info_contratada'])
            ->name('documentos.info_contratada');
            Route::get('/documentos/contratos/resp_legal', [\App\Http\Controllers\Contrato\ContratoController::class, 'resp_legal'])
            ->name('documentos.resp_legal');
            Route::get('/documentos/contratos/resp_legal_contratante', [\App\Http\Controllers\Contrato\ContratoController::class, 'resp_legal_contratante'])
            ->name('documentos.resp_legal_contratante');
            Route::get('/documentos/contratos/repre_vendedor', [\App\Http\Controllers\Contrato\ContratoController::class, 'repre_vendedor'])
            ->name('documentos.repre_vendedor');
            Route::get('/documentos/contratos/info_contrato', [\App\Http\Controllers\Contrato\ContratoController::class, 'info_contrato'])
            ->name('documentos.info_contrato');
            Route::get('/documentos/contratos/view_contrato', [\App\Http\Controllers\Contrato\ContratoController::class, 'view_contrato'])
            ->name('documentos.view_contrato');

            //ESPERANDO ATUALIZAÇÕES DO CONTRATO
            Route::get('/documentos/contratos/atua_contrato', [\App\Http\Controllers\Contrato\ContratoController::class, 'atu_contrato'])
            ->name('documentos.atu_contrato');
            //VERIFICA DE MODO MANUAL SE O CONTRATO FOI APROVADO OU REJEITADO
            Route::post('/documentos/contratos/verifica_contrato', [\App\Http\Controllers\Contrato\ContratoController::class, 'verifica_contrato'])
            ->name('documentos.verifica_contrato');

            //CONTRATO EM VIGENCIA
            Route::get('/documentos/contratos/contrato_vigente', [\App\Http\Controllers\Contrato\ContratoController::class, 'contrato_vigente'])
            ->name('documentos.contrato_vigente');

            //CONTRATO REJEITADO
            Route::get('/documentos/contratos/contrato_rejeitado', [\App\Http\Controllers\Contrato\ContratoController::class, 'contrato_rejeitado'])
            ->name('documentos.contrato_rejeitado');

            //CONTRATO COM AS INFORMAÇÕES DO CLIENTE RESP.
            Route::get('/documentos/contratos/ver_contrato_info', [\App\Http\Controllers\Contrato\ContratoController::class, 'ver_contrato_info'])
            ->name('documentos.ver_contrato_info'); 

            //VER CONTRATO E REALIZAR O DOWNLOAD
            Route::get('/documentos/contratos/ver_e_download', [\App\Http\Controllers\Contrato\ContratoController::class, 'ver_e_download'])
            ->name('documentos.ver_e_download');

            //BAIXAR CONTRATO A SER ENVIADO
            Route::get('/documentos/contratos/baixar_contrato', [\App\Http\Controllers\Contrato\ContratoController::class, 'baixar_contrato'])
            ->name('documentos.baixar_contrato');

            //BAIXAR CONTRATO FINAL
            Route::get('/documentos/contratos/ver_contrato_final', [\App\Http\Controllers\Contrato\ContratoController::class, 'ver_contrato_final'])
            ->name('documentos.ver_contrato_final');

            //GRAVANDO INFORMAÇÕES NO BANCO DE DADOS - ETAPAS E INFO EM ORDEM RESPECTIVAS COM AS ABAS
            Route::post('/documentos/contratos/gravar_info_1', [\App\Http\Controllers\Contrato\ContratoController::class, 'gravar_info_1'])
            ->name('documentos.gravar_info_1');
            Route::post('/documentos/contratos/gravar_info_2', [\App\Http\Controllers\Contrato\ContratoController::class, 'gravar_info_2'])
            ->name('documentos.gravar_info_2');
            Route::post('/documentos/contratos/gravar_info_3', [\App\Http\Controllers\Contrato\ContratoController::class, 'gravar_info_3'])
            ->name('documentos.gravar_info_3');
            Route::post('/documentos/contratos/gravar_info_4', [\App\Http\Controllers\Contrato\ContratoController::class, 'gravar_info_4'])
            ->name('documentos.gravar_info_4');
            Route::post('/documentos/contratos/gravar_info_5', [\App\Http\Controllers\Contrato\ContratoController::class, 'gravar_info_5'])
            ->name('documentos.gravar_info_5');
            Route::post('/documentos/contratos/gravar_info_6', [\App\Http\Controllers\Contrato\ContratoController::class, 'gravar_info_6'])
            ->name('documentos.gravar_info_6');
            

        });



    });

    Route::get('/util_view_logo_banco/{id}', [\App\Http\Controllers\UtilController::class,'viewLogoBanco'])
        ->name('util.logo.banco');

    Route::get('/util_endereco_of_cep/{cep?}', [\App\Http\Controllers\UtilController::class,'getEnderecoOfCep'])
        ->name('util.enderecoOfCep');


    //----------------------
    //      SELECT2
    //----------------------
    Route::group([
       'middleware' => ['ajax.only']
    ], function () {

        Route::get('/select2_empresas', [Select2Controller::class, 'empresas'])
            ->name('select2_empresas');
        //----
        Route::get('/select2_produtos_grupos_tipos', [Select2Controller::class, 'produtos_grupos_tipos'])
            ->name('select2_produtos_grupos_tipos');
        //----
        Route::get('/select2_formas_pagamento_orcamento', [Select2Controller::class, 'formas_pagamentos_orcamento'])
        ->name('select2_formas_pagamento_orcamento');
        //----
        Route::get('/select2_pessoas', [Select2Controller::class, 'pessoas'])
            ->name('select2_pessoas');
        //----
        Route::get('/select2_clientes', [Select2Controller::class, 'clientes'])
            ->name('select2_clientes'); 
        //----    
        Route::get('/select2_clientes_email', [Select2Controller::class, 'busca_email'])
            ->name('select2_clientes_busca_email');    
        //----
        Route::get('/select2_pessoas_classificacoes', [Select2Controller::class, 'classificacoes_pessoas'])
            ->name('select2_pessoas_classificacoes');
        //----
        Route::get('/select2_cidades', [Select2Controller::class, 'cidades'])
            ->name('select2_cidades');
        //----    
        Route::get('/select2_gruposproduto', [Select2Controller::class, 'grupos_produto'])
            ->name('select2_gruposproduto');
        Route::get('/select2_marcasproduto', [Select2Controller::class, 'marcas_produto'])
            ->name('select2_marcasproduto');
        Route::get('/select2_unidadesproduto', [Select2Controller::class, 'unidades_produto'])
            ->name('select2_unidadesproduto');
        //----
        Route::get('/select2_pessoas_para_vendedor', [Select2Controller::class, 'pessoas_para_vendedor'])
            ->name('select2_pessoas_para_vendedor');
        //----
        Route::get('/select2_vendedores', [Select2Controller::class, 'vendedores'])
            ->name('select2_vendedores');
        //----
        Route::get('/select2_bancos', [Select2Controller::class, 'bancos'])
        ->name('select2_bancos');
        //----
        Route::get('/select2_produtos', [Select2Controller::class, 'produtos'])
            ->name('select2_produtos');
        Route::get('/select2_produtos_all_empresas', [Select2Controller::class, 'produtos_all_empresas'])
            ->name('select2_produtos_all_empresas');
        //----
        Route::get('/select2_formas_pagamentos', [Select2Controller::class, 'formas_pagamentos'])
            ->name('select2_formas_pagamentos');
        //----
        Route::get('/select2_timezones', [Select2Controller::class, 'timezones'])
            ->name('select2_timezones');

        Route::get('/select2_caixas', [Select2Controller::class, 'caixas'])
            ->name('select2_caixas');

        Route::get('/select2_caixas_aberturas', [Select2Controller::class, 'caixas_aberturas'])
            ->name('select2_caixas_aberturas');

        Route::get('/select2_tipos_movimentos', [Select2Controller::class, 'tipos_movimentos'])
            ->name('select2_tipos_movimentos');

        Route::get('/select2_bandeiras_cartao', [Select2Controller::class, 'bandeiras_cartao'])
            ->name('select2_bandeiras_cartao');

        Route::get('/select2_parametro_cartao', [Select2Controller::class, 'parametro_cartao'])
            ->name('select2_parametro_cartao');    

        Route::get('/select2_centros_custos', [Select2Controller::class, 'centros_custos'])
            ->name('select2_centros_custos');

        Route::get('/select2_controles_contabeis_debito', [Select2Controller::class, 'controles_contabeis_debito'])
            ->name('select2_controles_contabeis_debito');

        Route::get('/select2_controles_contabeis_credito', [Select2Controller::class, 'controles_contabeis_credito'])
            ->name('select2_controles_contabeis_credito');

        Route::get('/select2_fiscal_crt', [Select2Controller::class, 'fiscal_crt'])
            ->name('select2_fiscal_crt');

        Route::get('/select2_fiscal_cfop', [Select2Controller::class, 'fiscal_cfop'])
            ->name('select2_fiscal_cfop');

        Route::get('/select2_fiscal_st_origem', [Select2Controller::class, 'fiscal_st_origem'])
            ->name('select2_fiscal_st_origem');

        Route::get('/select2_fiscal_st_icms', [Select2Controller::class, 'fiscal_st_icms'])
            ->name('select2_fiscal_st_icms');

        Route::get('/select2_fiscal_csosn', [Select2Controller::class, 'fiscal_csosn'])
            ->name('select2_fiscal_csosn');

        Route::get('/select2_fiscal_st_ipi', [Select2Controller::class, 'fiscal_st_ipi'])
            ->name('select2_fiscal_st_ipi');

        Route::get('/select2_fiscal_st_pis', [Select2Controller::class, 'fiscal_st_pis'])
            ->name('select2_fiscal_st_pis');

        Route::get('/select2_fiscal_st_cofins', [Select2Controller::class, 'fiscal_st_cofins'])
            ->name('select2_fiscal_st_cofins');

        Route::get('/select2_fiscal_modbc', [Select2Controller::class, 'fiscal_modbc'])
            ->name('select2_fiscal_modbc');

        Route::get('/select2_fiscal_modbcst', [Select2Controller::class, 'fiscal_modbcst'])
            ->name('select2_fiscal_modbcst');

        Route::get('/select2_fiscal_natureza_operacao', [Select2Controller::class, 'fiscal_natureza_operacao'])
            ->name('select2_fiscal_natureza_operacao');

        Route::get('/select2_fiscal_perfis', [Select2Controller::class, 'fiscal_perfis'])
            ->name('select2_fiscal_perfis');

        Route::get('/select2_transporte_frete', [Select2Controller::class, 'transporte_frete'])
            ->name('select2_transporte_frete');

        Route::get('/select2_nf_referenciada', [Select2Controller::class, 'nf_referenciada'])
            ->name('select2_nf_referenciada');

        Route::get('/select2_info_xml', [Select2Controller::class, 'info_xml'])
            ->name('select2_info_xml');
            
        Route::get('/select2_transporte_frete_busca', [Select2Controller::class, 'busca_frete'])
            ->name('select2_transporte_frete_busca');    

        Route::get('/select2_fiscal_ncm', [Select2Controller::class, 'fiscal_ncm'])
            ->name('select2_fiscal_ncm');

        Route::get('/select2_representantes', [Select2Controller::class, 'representantes'])
            ->name('select2_representantes');

    });


});
