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

Route::post('/login', 'AuthController@login')->middleware('throttle');
Route::get('/logout', 'AuthController@logout')->middleware('auth:api');

Route::post('admin/login', 'Admin\AuthController@login')->middleware('throttle');
Route::get('admin/logout', 'Admin\AuthController@logout')->middleware('auth:api');

Route::post('/register', 'AuthController@register');

Route::post('/sendPasswordResetLink', 'ResetPasswordController@sendEmail');
Route::post('/resetPassword', 'ResetPasswordController@process');

Route::get('/countries', 'CountryController@list');

Route::middleware(['auth:api', 'localization'])->group(function () {
    Route::group(['prefix' => 'user', 'namespace' => 'User', 'as' => 'user.'], function () {
        //retorna dados do usuario
        Route::get('/', 'UserController@index');
        //atualizar dados de cadastro
        Route::post('/update', 'UserController@update')->middleware(['tokencheck', 'pincheck']);
        Route::post('/update-international', 'UserController@updateInternational')->middleware(['tokencheck', 'pincheck']);
        //atualizar senha
        Route::post('/update-password', 'UserController@updatePassword')->middleware(['tokencheck', 'pincheck']);//token check
        //atualizar pin
        Route::post('/update-pin', 'UserController@updatePin')->middleware('tokencheck');
        //preencher dados pelo cpf informado
        Route::get('/cpf/{cpf}', 'UserController@cpf');
        //get address zip
        Route::get('/address/get/{zip}', 'UserAddressController@show');
        //save address
        Route::post('/address/store', 'UserAddressController@store');
        //gerar codigo para validar telefone
        Route::post('/verify-phone', 'UserController@sendPhoneCode');
        //validar telefone
        Route::post('/validate-phone', 'UserController@verifyPhoneCode');
        //retorna os dados do pais do usuario
        Route::get('/country', 'UserController@country');

        //retorna os saldos do usuario
        Route::get('/balances', 'UserWalletController@balances');
        //retorna a carteira de acordo com a moeda
        Route::get('/wallet/{coin}', 'UserWalletController@walletByCoin');
        Route::get('/secondaryWallet', 'UserWalletController@secondary');
        //retorna as carteiras com seus respectivos saldos
        Route::get('/wallets', 'UserWalletController@index');

        //ordem de conversão das carteiras
        Route::get('/wallets/conversion-order', 'UserWalletController@walletsConversionOrder');
        Route::post('/wallets/update-conversion-order', 'UserWalletController@walletsUpdateConversionOrder');

        //contas do usuário
        Route::get('accountsList', 'UserAccountController@index');
        //conta especifica do usuário
        Route::get('account/{account}', 'UserAccountController@show');
        //criar uma conta
        Route::post('storeAccount', 'UserAccountController@store');
        //atualizar uma conta
        Route::post('updateAccount', 'UserAccountController@update')->middleware(['tokencheck', 'pincheck']);
        //criar uma conta
        Route::post('deleteAccount', 'UserAccountController@delete')->middleware(['tokencheck', 'pincheck']);

        //lista de níves disponíveis
        Route::get('levels', 'UserLevelController@index');

        //hist
        Route::get('/hist', 'UserController@hist');
        Route::get('/dashboard', 'UserController@dashboard');


        Route::group(['prefix' => 'documents', 'as' => 'documents.'], function () {
            //gravar documento do usuario atual por tipo
            Route::post('/store', 'DocumentController@store');
            //listar todos os documentos do usuarios logado
            Route::get('/list', 'DocumentController@index');
            //gerar url temporaria de acesso ao documento requisitado do usuario
            Route::get('/show/{document}', 'DocumentController@show');
            //verificar situacao do documeto requisitado por tipo
            Route::get('/verified/{document}', 'DocumentController@verified');
        });

        Route::group(['prefix' => 'tickets', 'as' => 'tickets.'], function () {
            //tickets de suporte
            Route::get('/', 'UserTicketController@index');
            Route::post('/', 'UserTicketController@store');
            Route::post('/message', 'UserTicketController@storeMessage');
            Route::get('/status', 'UserTicketController@status');
            Route::get('/departments', 'UserTicketController@departments');
        });

        Route::post('/cancel', 'UserController@cancel')->middleware(['pincheck', 'tokencheck']);
    });

    //Notificações
    Route::get('/mining/status', 'Mining\MiningPoolController@status');
    Route::get('/mining/blocks', 'Mining\MiningPoolController@blocks');
    Route::get('/mining/rewards', 'Mining\MiningPoolController@rewardChart');

    Route::get('/messages/notifications', 'Admin\Messages\MessageController@notificationsList');
    Route::get('/messages/general', 'Admin\Messages\MessageController@generalMessages');
    Route::get('/messages/edit/{id}', 'Admin\Messages\MessageController@edit');
    Route::get('/messages/total', 'Admin\Messages\MessageController@totalMessages');
    //google 2fa
    Route::group(['prefix' => '2fa'], function () {
        Route::get('/qr-code', 'Google2faController@getQrCodeUrl');
        Route::post('/activate', 'Google2faController@activate2fa')->middleware('pincheck');
        Route::post('/deactivate', 'Google2faController@deactivate2fa')->middleware('pincheck');
    });

    //lista de bancos padrões
    Route::get('banks', 'BankController@index');
    //taxas de saque
    Route::get('withdrawal/deadlines', 'WithdrawalDeadlineController@index');
    Route::post('withdrawal/calc', 'WithdrawalDeadlineController@calc');
    //provedores de pagamentos online
    Route::get('providers', 'BankController@providers');

    Route::group(['prefix' => 'system', 'namespace' => 'System', 'as' => 'system.'], function () {
        //lista de contas bancarias/ pagamentos online do sistema
        Route::get('accountsList', 'SystemAccountController@index');
    });


    //solicitar deposito
    Route::post('deposit/send', 'DepositController@store')->middleware('docscheck');

    Route::middleware(['tokencheck', 'pincheck'])->group(function () {
        //solicitar saque
        Route::post('draft/send', 'DraftController@store')->middleware(['withdrawalallowed', 'docscheck']);
        //Envia R$ para CredminerAu
        Route::post('draft/credminer', 'DraftController@sendBrlCredminer');
        Route::post('draft-usd/credminer', 'DraftController@sendUsdCredminer');
        //cancelar saque
        Route::post('draft/cancel', 'DraftController@cancel')->middleware(['tokencheck', 'pincheck']);
    });

    //estimar taxas de saque
    Route::post('draft/tax', 'DraftController@estimateTax');

    Route::group(['prefix' => 'transactions'], function () {
        //enviar transações
        Route::post('/send', 'TransactionsController@store')->middleware(['tokencheck', 'pincheck', 'checkkeycodelevel']);
        //retorna o valor transacionado do usuario no dia
        Route::get('/sum-day/{user}', 'TransactionsController@getValueByDayUser');
        //verifica se o usuário pode efetuar a transação
        Route::post('/verifyBalance', 'TransactionsController@verifyBalance');
        //estima as taxas da transação
        Route::post('/estimateFee', 'TransactionsController@estimateFee');
        //verifica o valor minimo para transacao
        Route::get('/min-value', 'TransactionsController@getValueMinTransaction');
        //lista de transacoes
        Route::get('/miniList', 'TransactionsController@list');
        Route::get('/list', 'TransactionsController@list');
        Route::get('/listByWallet/{abbr}/{address}', 'TransactionsController@listByWallet');
        //realizar transferencias
        Route::post('/transfer', 'TransactionsController@transfer')->middleware(['tokencheck', 'pincheck']);
    });

    Route::group(['prefix' => 'levels', 'as' => 'levels.'], function () {
        Route::post('/buy', 'ProductController@buyLevel')->middleware(['tokencheck', 'pincheck', 'internationalUserNotAllowed', 'docscheck']);
        Route::post('/buyLqx', 'ProductController@buyLevel')->middleware(['tokencheck', 'pincheck', 'docscheck']);
        Route::post('/buyUsd', 'ProductController@buyLevelUsd')->middleware(['tokencheck', 'pincheck', 'nationalUserNotAllowed', 'docscheck']);
    });

    Route::group(['prefix' => 'coins', 'as' => 'coins.'], function () {
        Route::get('/', 'Admin\CoinsController@index');
        //preco da moeda indicada pelo simbolo (abbr)
        Route::get('/quotes', 'CoinQuoteController@quotes');

    });

    //crypto ativos
    Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
        //compra de ativo
        Route::post('/buy', 'OrderController@orderBuy')->middleware('pincheck');
        //vendas de ativo
        Route::post('/sell', 'OrderController@orderSell')->middleware('pincheck');
        //estimar taxa de compra
        Route::post('/estimateBuyTax', 'OrderController@estimateBuyTax');
        //estimar taxa de venda
        Route::post('/estimateSellTax', 'OrderController@estimateSellTax');
    });

    //enviar token de confirmação por email
    Route::post('/send-mail-token', 'Token\TokenMailController@generate');
    //estimar conversao de moeda fiat/crypto - venda
    Route::post('/convert', 'OrderController@convert')->middleware(['allowsellforfiat', 'docscheck']);
    //estimar conversao de moeda fiat/crypto - compra
    Route::post('/convert-buy', 'OrderController@convertBuy')->middleware(['allowbuywithfiat', 'docscheck']);
    //realizar conversao de moeda fiat/crypto
    Route::post('/convert-amount', 'OrderController@convertAmount')->middleware(['pincheck', 'allowsellforfiat', 'docscheck']);
    Route::post('/convert-buy-amount', 'OrderController@convertBuyAmount')->middleware(['pincheck', 'allowbuywithfiat', 'docscheck']);
    //listagem de conversoes realizadas - barra lateral
    Route::get('/conversion-list', 'OrderController@conversionList');
    //listagem de moedas em que o user possui carteira
    Route::get('/my-coins-list', 'OrderController@myCoinsList');
    //detalhes da conversao pela tx - retorna as duas transações geradas
    Route::get('/conversion/{tx}', 'OrderController@conversion');

    Route::group(['prefix' => 'exchange', 'namespace' => 'Exchange', 'as' => 'exchange.'], function () {
        Route::get('/', 'ExchangesController@index');
        Route::get('/comparison', 'ExchangesController@comparison');
        Route::get('/orderBook/{exchange}', 'ExchangesController@orderBook');
        Route::get('/execute', 'ExchangesController@execute');
        Route::get('/last-trades', 'ExchangesController@last_trades');
    });

    Route::group(['prefix' => 'nanotech', 'namespace' => 'Nanotech', 'as' => 'nanotech.'], function () {
        Route::get('/data/{type}', 'NanotechController@index');
        Route::get('/chart/{type}', 'NanotechController@chart');

        Route::post('/send', 'NanotechController@send')->middleware('pincheck');
        Route::post('/withdrawal', 'NanotechController@withdrawal')->middleware('pincheck');
    });

    Route::group(['prefix' => 'masternode', 'as' => 'masternode.'], function () {
        Route::get('/list', 'MasternodeController@listNodes');
    });

    //funds
    Route::group(['prefix' => 'funds', 'namespace' => 'Funds', 'as' => 'funds.'], function () {
        //negociar fundo de investimentos
        Route::post('/buy', 'FundsController@buy')->middleware('pincheck');
        //saque antecipado
        Route::post('/earlyRedemption', 'FundsController@earlyRedemption')->middleware('pincheck');
        Route::post('/withdrawal', 'FundsController@withdrawal')->middleware('pincheck');
        //estimar taxa de compra
        Route::post('/estimate-buy-tax', 'FundsController@estimateBuyTax');

        Route::get('/list', 'FundsController@index');
        Route::get('/chart/{fund}', 'FundsController@pieChart');
        Route::get('/user-chart', 'FundsController@userChart');
        Route::get('/user-list', 'FundsController@userList');

        Route::get('/update/{fund}', 'FundsController@updateFund');

    });
    Route::prefix('admin')->middleware('admin')->group(base_path('routes/admin.php'));
});

//gateway de pagamentos
Route::group(['prefix' => 'payments', 'middleware' => 'gateway'], function () {
    //gerar solicitação de pagamento
    Route::post('/new', 'GatewayController@store');
    Route::post('/list', 'GatewayController@list');
    Route::post('/status/{tx}', 'GatewayController@status');
});

//verificar validade da api key
Route::post('/payments/check-key', 'GatewayController@checkKey');

Route::get('/uuid', function () {

    $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
    return response([
        'uuid' => $id,
        'key' => str_replace("-", "", $id),
    ], 200);
});

Route::get('/time', function () {
    return response(['time' => \Carbon\Carbon::now()->toIso8601ZuluString()], 200);
});

Route::post('operation', 'OperationController@index');
Route::get('public/lqx', 'ApiController@lqx');
Route::post('public/api', 'ApiController@index');

//credminer products
Route::group(['prefix' => 'credminer/product', 'middleware' => 'credminer'],
    function () {
        Route::get('/nanotech', 'Credminer\NanotechController@index');
        Route::post('/nanotech/info', 'Credminer\NanotechController@info');
        Route::post('/nanotech/invest', 'Credminer\NanotechController@invest');

        Route::get('/investments', 'Credminer\InvestmentController@index');
        Route::post('/investment/invest', 'Credminer\InvestmentController@invest');
        Route::post('/investment/estimate', 'Credminer\InvestmentController@estimate');
        Route::post('/investment/acquired', 'Credminer\InvestmentController@acquired');
    });

//gateway de saques credminer
Route::group([
    'prefix' => 'credminer/payments',
    'middleware' => 'credminer'
],
    function () {
        Route::post('/withdrawal', 'Credminer\PaymentController@withdrawal');
        Route::post('/check-key', 'Credminer\PaymentController@checkKey');
    });

Route::group([
    'prefix' => 'credminer/gateway'
],
    function () {
        Route::post('/new', 'GatewayController@new')->middleware('credminer');
        Route::get('/status/{tx}', 'GatewayController@showGatewayData');
    });
