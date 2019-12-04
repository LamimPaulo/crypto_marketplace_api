<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Admin'], function () {

    //dados do usuario admin
    Route::get('/user', 'User\UserController@index');
    //dados dashboard
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/dashboard/coins', 'DashboardController@coins');
    Route::get('/dashboard/fiat/{abbr}', 'DashboardController@fiat');

    Route::get('/dashboard/crypto/{abbr}', 'DashboardController@crypto');
    Route::get('/dashboard/crypto_balance/{abbr}', 'DashboardController@crypto_balance');
    Route::get('/dashboard/crypto_above_limit/{abbr}', 'DashboardController@crypto_above_limit');
    Route::get('/dashboard/crypto_above_internal/{abbr}', 'DashboardController@crypto_above_internal');
    Route::get('/dashboard/crypto_in/{abbr}', 'DashboardController@crypto_in');
    Route::get('/dashboard/crypto_out/{abbr}', 'DashboardController@crypto_out');
    Route::get('/dashboard/crypto_out_internal/{abbr}', 'DashboardController@crypto_out_internal');
    Route::get('/dashboard/crypto_buy/{abbr}', 'DashboardController@crypto_buy');
    Route::get('/dashboard/crypto_sell/{abbr}', 'DashboardController@crypto_sell');

    Route::group(['namespace' => 'Operations'], function () {
        //depositos
        Route::group([
            'prefix' => 'deposits',
            'middleware' => ['can_access:fiat_menu', 'can_access:fiat_deposits']
        ], function () {
            Route::post('/pending', 'DepositController@index');
            Route::post('/list', 'DepositController@list');
            Route::post('/reject', 'DepositController@reject')->middleware('can_execute:fiat_deposits');
            Route::post('/accept', 'DepositController@accept')->middleware('can_execute:fiat_deposits');
        });

        //saques
        Route::group([
            'prefix' => 'withdrawals',
            'middleware' => ['can_access:fiat_menu', 'can_access:fiat_withdrawals']
        ], function () {
            Route::post('/list', 'DraftController@list');
            Route::post('/reject', 'DraftController@reject')->middleware('can_execute:fiat_withdrawals');
            Route::post('/accept', 'DraftController@accept')->middleware('can_execute:fiat_withdrawals');
            Route::post('/process', 'DraftController@process')->middleware('can_execute:fiat_withdrawals');
        });

        //saques nanotech
        Route::group([
            'prefix' => 'nanotech/withdrawals',
            'middleware' => ['can_access:nanotech_menu']
        ], function () {
            Route::get('/list', 'NanotechController@index');
            Route::get('/list/{status}', 'NanotechController@list');
            Route::post('/reject', 'NanotechController@reject')->middleware('can_execute:nanotech_withdrawals');
            Route::post('/accept', 'NanotechController@accept')->middleware('can_execute:nanotech_withdrawals');
        });

        Route::group([
            'prefix' => 'transactions',
            'middleware' => 'can_access:crypto_above_limit'
        ], function () {
            //transactions list
            Route::post('/by-status', 'TransactionsController@byStatus');
            Route::post('/by-type', 'TransactionsController@byType');
            //transaction reject
            Route::post('/reject', 'TransactionsController@reject')->middleware('can_execute:crypto_above_limit');
            //transaction accept
            Route::post('/accept', 'TransactionsController@accept')->middleware('can_execute:crypto_above_limit');
        });

        Route::group([
            'prefix' => 'transactions/credminer',
            'middleware' => 'can_access:credminer_transactions'
        ], function () {
            //transactions list
            Route::post('/', 'CredminerController@index');
            Route::post('/by-status', 'CredminerController@byStatus');
            Route::post('/by-coin', 'CredminerController@byCoin');
            //transaction cancel
            Route::post('/cancel', 'CredminerController@cancel')->middleware('can_execute:credminer_transactions');
            //transaction accept
            Route::post('/accept', 'CredminerController@accept')->middleware('can_execute:credminer_transactions');
        });

        Route::get('/balance/verify/{user_email}', 'TransactionsController@balanceVerify')->middleware('is_dev');

    });

    Route::group([
        'prefix' => 'withdrawals',
        'middleware' => ['can_access:fiat_menu']
    ], function () {
        Route::get('/deadlines', 'WithdrawalDeadlineController@index')->middleware('can_access:fiat_withdrawals_taxes');
        Route::post('/deadlines', 'WithdrawalDeadlineController@update')->middleware('can_execute:fiat_withdrawals_taxes');

        Route::get('/holydays', 'WithdrawalDeadlineController@holydays')->middleware('can_access:fiat_holidays');
        Route::post('/holydays', 'WithdrawalDeadlineController@storeHolydays')->middleware('can_execute:fiat_holidays');
        Route::post('/holyday', 'WithdrawalDeadlineController@deleteHolyday')->middleware('can_execute:fiat_holidays');
    });

    Route::group([
        'prefix' => 'user', 'namespace' => 'User',
        'middleware' => 'can_access:user_list'
    ], function () {
        //user list
        Route::get('/list', 'UserController@list');
        Route::get('/balance', 'UserController@balance');
        Route::post('/searchBalance', 'UserController@searchBalance');
        Route::get('/list/deactivated', 'UserController@listDeactivated');
        Route::post('/update-email', 'UserController@updateEmail')->middleware('can_execute:user_mail_change');
        //user hist
        Route::post('/hist', 'UserController@hist');
        Route::post('/transactions', 'UserController@transactions');
        Route::post('/transactions/nanotech', 'UserController@transactionsNanotech');
        Route::post('/drafts', 'UserController@drafts');
        Route::post('/deposits', 'UserController@deposits');
        Route::post('/masternodes', 'UserController@masternodes');
        //busca de usuarios
        Route::post('/search', 'UserController@search');
        Route::post('/search/deactivated', 'UserController@searchDeactivated')->middleware('can_execute:user_reactivate');
        //listagem de usuários que nao completaram o cadastro
        Route::get('/incomplete', 'UserController@incomplete');
        //listagem de ações(logs) dos usuários
        Route::get('/logs/{email}', 'UserController@userActivity');
        //verificação de documentos
        Route::post('/documents', 'UserController@documents')->middleware('can_access:user_documents');
        //remove 2FA
        Route::get('/remove2fa/{email}', 'UserController@remove2fa')->middleware('can_execute:user_2fa_disable');
        //reativa o usuario
        Route::get('/reactivateuser/{email}', 'UserController@reactivateUser')->middleware('can_execute:user_reactivate');
        Route::get('/makeAdmin/{email}', 'UserController@makeAdmin')->middleware('is_dev');
        Route::get('/revogueAdmin/{email}', 'UserController@revogueAdmin')->middleware('is_dev');
        //levels
        Route::group([
            'prefix' => 'levels',
            'middleware' => 'can_access:levels'
        ], function () {
            Route::get('/', 'UserLevelController@index');
            Route::post('/report', 'UserLevelController@soldReport');
            Route::get('/enum-types', 'UserLevelController@enumTypes');
            Route::post('/sold', 'UserLevelController@soldList');
            Route::post('/store', 'UserLevelController@store')->middleware('can_execute:levels');
            Route::post('/update', 'UserLevelController@update')->middleware('can_execute:levels');
        });
        //documents
        Route::group([
            'prefix' => 'documents',
            'middleware' => 'can_access:user_documents'
        ], function () {
            Route::get('/', 'UserDocumentsController@index');
            //busca de usuarios
            Route::post('/search', 'UserDocumentsController@search');
            Route::post('/accept', 'UserDocumentsController@accept')->middleware('can_execute:user_documents');
            Route::post('/reject', 'UserDocumentsController@reject')->middleware('can_execute:user_documents');
        });

        Route::group(['middleware' => 'is_dev'], function () {
            Route::get('/analysis', 'UserAnalysisController@list');
            Route::post('/analysis/search', 'UserAnalysisController@search');
            Route::get('/analysis/release/{email}', 'UserAnalysisController@release');
            Route::get('/analysis/block/{email}', 'UserAnalysisController@block');
            Route::post('/analysis/transaction/update', 'UserAnalysisController@transactionUpdate');
            Route::post('/analysis/transaction/delete', 'UserAnalysisController@transactionDelete');
            Route::post('/analysis/balance/update', 'UserAnalysisController@balanceUpdate');
        });

        //tickets de suporte
        Route::group([
            'prefix' => 'tickets', 'as' => 'tickets.',
            'middleware' => 'can_access:user_tickets'
        ], function () {
            Route::get('/', 'UserTicketController@index');
            Route::post('/byDepartment', 'UserTicketController@byDepartment');
            Route::post('/byStatus', 'UserTicketController@byStatus');
            Route::post('/message', 'UserTicketController@storeMessage')->middleware('can_execute:user_tickets');
            Route::get('/status', 'UserTicketController@status');
        });
    });

    //funds
    Route::group([
        'prefix' => 'funds', 'namespace' => 'Funds',
        'middleware' => 'can_access:funds'
    ], function () {
        //resumo
        Route::get('/resume/{fund}', 'FundsController@resume');
        //gravar fundo de investimentos
        Route::post('/store', 'FundsController@store')->middleware('can_execute:funds');
        //atualizar fundo de investimentos
        Route::post('/update', 'FundsController@update')->middleware('can_execute:funds');
        //atualizar as moedas componentes do fundo de investimento
        Route::post('/update-coins', 'FundsController@updateCoins')->middleware('can_execute:funds');
        //listagem de fundo de investimentos
        Route::get('/list', 'FundsController@index');
        //detalhe do fundo de investimento
        Route::get('/list/{fund}', 'FundsController@show');
        //retorna a lista de moedas possíveis para o fundo
        Route::get('/coins', 'FundsController@coins');
        //retorna as moedas que ainda não compõem o fundo
        Route::post('/remaining-coins', 'FundsController@remaining');

        Route::get('/transactions/{fund}', 'FundTransactionsController@transactions');
        Route::get('/profits/{fund}', 'FundTransactionsController@profits');
    });

    //funds
    Route::group([
        'prefix' => 'masternodes', 'middleware' => 'can_access:funds'
    ], function () {
        //resumo
        Route::get('/resume', 'MasternodesController@index');
        Route::post('/', 'MasternodesController@list');
    });

    //exchanges - coin providers
    Route::group([
        'prefix' => 'exchanges', 'namespace' => 'Exchanges',
        'middleware' => 'is_dev'
    ], function () {
        //listagem de exchanges arbitrage
        Route::get('/arbitrage', 'ExchangesController@arbitrage');
        Route::get('/arbitrage-provider/{exchange}', 'ExchangesController@arbitrageExchange');

        //gravar exchanges
        Route::post('/store', 'ExchangesController@store');

        //detalhe da exchange
        Route::get('/show/{exchange}', 'ExchangesController@show');
    });

    //messages - send message to users
    Route::group([
        'prefix' => 'messages', 'namespace' => 'Messages',
        'middleware' => 'can_access:messages'
    ], function () {
        //listagem de exchanges arbitrage
        Route::get('/list', 'MessageController@index');
        Route::post('/readed', 'MessageController@readed');
        Route::get('/edit/{id}', 'MessageController@edit');
        Route::post('/user/list', 'MessageController@userList');
        Route::post('/store', 'MessageController@store');
        Route::post('/update/{id}', 'MessageController@update');
        Route::delete('/delete/{id}', 'MessageController@delete');
    });


    Route::get('/wallet/coins', 'CoinsController@wallet');

    Route::group([
        'prefix' => 'config',
        'middleware' => 'can_access:config_menu'
    ], function () {
        //coins
        Route::group([
            'prefix' => 'coins'
        ], function () {
            //listagem de moedas
            Route::get('/', 'CoinsController@index')->middleware('can_access:coins_config');
            //gravar moedas
            Route::post('/store', 'CoinsController@store')->middleware('can_execute:coins_config');
            //atualizar moedas
            Route::post('/update', 'CoinsController@update')->middleware('can_execute:coins_config');
            Route::post('/update/lqx', 'CoinsController@updateLqx')->middleware('can_execute:coins_config');
            //listagem de ordem das carteiras
            Route::get('/wallets-order', 'CoinsController@walletsOrder')->middleware('can_access:wallet_order');
            //gravar order das carteiras
            Route::post('/wallets-order', 'CoinsController@updateWalletsOrder')->middleware('can_execute:wallet_order');

        });

        Route::post('/bank-search', 'SystemAccountController@bankSearch')->middleware('can_access:system_accounts');
        //system accounts
        Route::group([
            'prefix' => 'accounts',
            'middleware' => 'can_access:system_accounts'
        ], function () {
            //listagem de contas
            Route::get('/', 'SystemAccountController@index');
            //gravar contas
            Route::post('/store', 'SystemAccountController@store')->middleware('can_execute:system_accounts');
            //atualizar contas
            Route::post('/update', 'SystemAccountController@update')->middleware('can_execute:system_accounts');
        });
        //configuracoes do sistema
        Route::get('/system', 'SystemConfigController@index')->middleware('can_access:system');
        Route::post('/system', 'SystemConfigController@update')->middleware('can_execute:system');
        //configuracoes do suporte
        Route::get('/support', 'SupportConfigController@index')->middleware('can_access:support_config');
        Route::post('/support', 'SupportConfigController@update')->middleware('can_execute:support_config');
        //configurcoes nanotech
        Route::get('/nanotech', 'NanotechTypeController@index')->middleware('can_access:nanotech_configs');
        Route::post('/nanotech', 'NanotechTypeController@update')->middleware('can_execute:nanotech_configs');
    });

    //gateway de pagamentos
    Route::group(['prefix' => 'gateway'], function () {
        //listar pagamentos
        Route::post('/', 'Operations\GatewayController@index');
        Route::get('/status', 'Operations\GatewayController@status');
        Route::get('/transaction/{address}', 'Operations\GatewayController@transaction');

        //listar chave(s)
        Route::get('/get-key/{email}', 'GatewayApiKeyController@index');
        //gerar api key
        Route::post('/new-key', 'GatewayApiKeyController@store');
        //atualizar informações da chave
        Route::post('/update-key', 'GatewayApiKeyController@update');
        //lista de pagamentos
        Route::get('/list-payments/{user_email}', 'GatewayApiKeyController@listPayments');

        //POS
        //detalhes do pagamento
        Route::get('/show/{payment}', 'GatewayApiKeyController@showPayment');
        //estimar solicitacao de pagamento
        Route::post('/estimate-payment', 'GatewayApiKeyController@estimatePayment');
        //gerar solicitacao de pagamento
        Route::post('/new-payment', 'GatewayApiKeyController@payment');
    });

    Route::group(['middleware' => 'is_dev'], function () {
        Route::post('/navi/payment/filter', 'NaviPaymentController@filter');
        Route::get('/navi/payment', 'NaviPaymentController@index');
        Route::post('/navi/payment', 'NaviPaymentController@payment');
    });

    Route::group([
        'prefix' => 'roles',
        'middleware' => 'can_access:assign_permission'
    ], function () {
        Route::get('/', 'PermissionsController@roles');
        Route::get('/enum_permissions', 'PermissionsController@enum_permissions');
        Route::post('/store', 'PermissionsController@storeRole')->middleware('can_execute:assign_permission');
        Route::post('/permissions/update', 'PermissionsController@updatePermissions')->middleware('can_execute:assign_permission');
        Route::post('/delete/{role_id}/{user_email}', 'PermissionsController@deleteUserRole')->middleware('can_execute:assign_permission');
    });

    Route::get('/lqx/withdrawals', 'LqxWithdrawalController@index')->middleware('can_access:lqx_withdrawals');
    Route::post('/lqx/withdrawals/update', 'LqxWithdrawalController@update')->middleware('can_execute:lqx_withdrawals');

});
