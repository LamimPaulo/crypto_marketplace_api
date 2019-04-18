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

        //retorna as contas de transaferencia favoritas do usuario
        Route::get('/fav-accounts', 'UserFavAccountController@index');
        //cadastra novo favorito
        Route::post('/fav-account', 'UserFavAccountController@store');
        //mostra os detalhes do favorito
        Route::get('/fav-account/{email}', 'UserFavAccountController@show');
        //busca novo favorito para cadastro
        Route::post('/search-account', 'UserFavAccountController@search');
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
    });

    //google 2fa
    Route::group(['prefix' => '2fa'], function () {
        Route::get('/qr-code', 'Google2faController@getQrCodeUrl');
        Route::post('/activate', 'Google2faController@activate2fa')->middleware('pincheck');
        Route::post('/deactivate', 'Google2faController@deactivate2fa')->middleware('pincheck');
    });

    //lista de bancos padrões
    Route::get('banks', 'BankController@index');
    //provedores de pagamentos online
    Route::get('providers', 'BankController@providers');

    Route::group(['prefix' => 'system', 'namespace' => 'System', 'as' => 'system.'], function () {
        //lista de contas bancarias/ pagamentos online do sistema
        Route::get('accountsList', 'SystemAccountController@index');
    });

    //solicitar deposito
    Route::post('deposit/send', 'DepositController@store');
    Route::post('depositPaypal/send', 'DepositController@storePaypal');

    //solicitar saque
    Route::post('draft/send', 'DraftController@store')->middleware(['tokencheck', 'pincheck']);
    //cancelar saque
    Route::post('draft/cancel', 'DraftController@cancel')->middleware(['tokencheck', 'pincheck']);
    //estimar taxas de saque
    Route::post('draft/tax', 'DraftController@estimateTax');

    Route::group(['prefix' => 'transactions'], function () {
        //enviar transações
        Route::post('/send', 'TransactionsController@store')->middleware(['tokencheck', 'pincheck']);
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
        Route::post('/buy', 'ProductController@buyLevel')->middleware(['tokencheck', 'pincheck']);
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
    Route::post('/convert', 'OrderController@convert')->middleware('allowsellforfiat');
    //estimar conversao de moeda fiat/crypto - compra
    Route::post('/convert-buy', 'OrderController@convertBuy')->middleware('allowbuywithfiat');
    //realizar conversao de moeda fiat/crypto
    Route::post('/convert-amount', 'OrderController@convertAmount')->middleware(['pincheck', 'allowsellforfiat']);
    Route::post('/convert-buy-amount', 'OrderController@convertBuyAmount')->middleware(['pincheck', 'allowbuywithfiat']);
    //listagem de conversoes realizadas - barra lateral
    Route::get('/conversion-list', 'OrderController@conversionList');
    //listagem de moedas em que o user possui carteira
    Route::get('/my-coins-list', 'OrderController@myCoinsList');
    //detalhes da conversao pela tx - retorna as duas transações geradas
    Route::get('/conversion/{tx}', 'OrderController@conversion');

    //gateway de pagamentos
    Route::group(['prefix' => 'gateway', 'middleware' => 'gatewayelegible'], function () {
        //listar chave(s)
        Route::get('/get-key', 'GatewayApiKeyController@index');
        //gerar api key
        Route::post('/new-key', 'GatewayApiKeyController@store')->middleware('pincheck');
        //atualizar informações da chave
        Route::post('/update-key', 'GatewayApiKeyController@update')->middleware('pincheck');
        //estimar solicitacao de pagamento
        Route::post('/estimate-payment', 'GatewayApiKeyController@estimatePayment');
        //gerar solicitacao de pagamento
        Route::post('/new-payment', 'GatewayApiKeyController@payment');
        //lista de pagamentos
        Route::get('/list-payments', 'GatewayApiKeyController@listPayments');
        //detalhes do pagamento
        Route::get('/show/{payment}', 'GatewayApiKeyController@showPayment');
    });

    Route::group(['prefix' => 'exchange', 'namespace' => 'Exchange', 'as' => 'exchange.'], function () {
        Route::get('/', 'ExchangesController@index');
        Route::get('/comparison', 'ExchangesController@comparison');
        Route::get('/orderBook/{exchange}', 'ExchangesController@orderBook');
        Route::get('/execute', 'ExchangesController@execute');
        Route::get('/last-trades', 'ExchangesController@last_trades');
    });

    Route::group(['prefix' => 'investments', 'namespace' => 'Investments', 'as' => 'investments.'], function () {
        Route::get('/data/{type}', 'InvestmentController@index');
        Route::get('/chart/{type}', 'InvestmentController@chart');

        Route::post('/send', 'InvestmentController@send')->middleware('pincheck');
        Route::post('/draft', 'InvestmentController@draft')->middleware('pincheck');
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
});

//mostra a tela de pagamento externamente na plataforma
Route::get('/gateway/tx/{tx}', 'GatewayApiKeyController@showPayment');
Route::get('/payments/status/{tx}', 'GatewayApiKeyController@showPayment');
Route::post('/gateway/update-tx', 'GatewayApiKeyController@updatePayment');

//verificar validade da api key
Route::post('/payments/check-key', 'GatewayController@checkKey');

Route::get('/uuid', function () {
    $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
    return response([
        'uuid' => $id,
        'key' => str_replace("-", "", $id)
    ], 200);
});

Route::get('/time', function () {
    return response(['time' => \Carbon\Carbon::now()->toIso8601ZuluString()], 200);
});

Route::post('operation', 'OperationController@index');

//gateway de saques credminer
Route::group(['prefix' => 'credminer/payments', 'middleware' => 'credminer'],
    function () {
        Route::post('/withdrawal', 'Credminer\PaymentController@withdrawal');//<---ok
        Route::post('/check-key', 'Credminer\PaymentController@checkKey');//<---ok
    });