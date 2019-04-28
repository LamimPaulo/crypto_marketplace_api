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

    //dados dashboard
    Route::get('/dashboard', 'DashboardController@index');


    Route::group(['namespace' => 'Operations'], function () {
        //depositos
        Route::group(['prefix' => 'deposits'], function () {
            Route::get('/pending', 'DepositController@index');
            Route::get('/list', 'DepositController@list');
            Route::post('/reject', 'DepositController@reject');
            Route::post('/accept', 'DepositController@accept');
        });

        //saques
        Route::group(['prefix' => 'drafts'], function () {
            Route::get('/list/{status}', 'DraftController@list');
            Route::post('/reject', 'DraftController@reject');
            Route::post('/accept', 'DraftController@accept');
            Route::post('/process', 'DraftController@process');
        });

        //saques nanotech
        Route::group(['prefix' => 'nanotech/withdrawals'], function () {
            Route::get('/list', 'NanotechController@index');
            Route::get('/list/{status}', 'NanotechController@list');
            Route::post('/reject', 'NanotechController@reject');
            Route::post('/accept', 'NanotechController@accept');
        });

        Route::group(['prefix' => 'transactions'], function () {
            //transactions list
            Route::get('/by-status/{status}', 'TransactionsController@byStatus');
            Route::get('/by-type/{type}', 'TransactionsController@byType');
            //transaction reject
            Route::post('/reject', 'TransactionsController@reject');
            //transaction accept
            Route::post('/accept', 'TransactionsController@accept');
        });


    });

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        //dados do usuario admin
        Route::get('/', 'UserController@index');
        //user list
        Route::get('/list', 'UserController@list');
        //user hist
        Route::post('/hist', 'UserController@hist');
        Route::post('/transactions', 'UserController@transactions');
        Route::post('/drafts', 'UserController@drafts');
        Route::post('/deposits', 'UserController@deposits');
        //busca de usuarios
        Route::post('/search', 'UserController@search');
        //listagem de usuários que nao completaram o cadastro
        Route::get('/incomplete', 'UserController@incomplete');
        //verificação de documentos
        Route::post('/documents', 'UserController@documents');
        //levels
        Route::group(['prefix' => 'levels'], function () {
            Route::get('/', 'UserLevelController@index');
            Route::get('/enum-types', 'UserLevelController@enumTypes');
            Route::post('/store', 'UserLevelController@store');
            Route::post('/update', 'UserLevelController@update');
        });
        //documents
        Route::group(['prefix' => 'documents'], function () {
            Route::get('/', 'UserDocumentsController@index');
            //busca de usuarios
            Route::post('/search', 'UserDocumentsController@search');
            Route::post('/accept', 'UserDocumentsController@accept');
            Route::post('/reject', 'UserDocumentsController@reject');
        });
    });

    //funds
    Route::group(['prefix' => 'funds', 'namespace' => 'Funds'], function () {
        //resumo
        Route::get('/resume/{fund}', 'FundsController@resume');
        //gravar fundo de investimentos
        Route::post('/store', 'FundsController@store');
        //atualizar fundo de investimentos
        Route::post('/update', 'FundsController@update');
        //atualizar as moedas componentes do fundo de investimento
        Route::post('/update-coins', 'FundsController@updateCoins');
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

    //exchanges - coin providers
    Route::group(['prefix' => 'exchanges', 'namespace' => 'Exchanges'], function () {
        //listagem de exchanges arbitrage
        Route::get('/arbitrage', 'ExchangesController@arbitrage');
        Route::get('/arbitrage-provider/{exchange}', 'ExchangesController@arbitrageExchange');

        //gravar exchanges
        Route::post('/store', 'ExchangesController@store');

        //detalhe da exchange
        Route::get('/show/{exchange}', 'ExchangesController@show');
    });

    Route::group(['prefix' => 'config'], function () {
        //coins
        Route::get('/dashboard', 'DashboardController@index');
        Route::group(['prefix' => 'coins'], function () {
            //listagem de moedas
            Route::get('/', 'CoinsController@index');
            //gravar moedas
            Route::post('/store', 'CoinsController@store');
            //atualizar moedas
            Route::post('/update', 'CoinsController@update');
            //listagem de ordem das carteiras
            Route::get('/wallets-order', 'CoinsController@walletsOrder');
            //gravar order das carteiras
            Route::post('/wallets-order', 'CoinsController@updateWalletsOrder');

        });

        Route::post('/bank-search', 'SystemAccountController@bankSearch');
        //system accounts
        Route::group(['prefix' => 'accounts'], function () {
            //listagem de contas
            Route::get('/', 'SystemAccountController@index');
            //gravar contas
            Route::post('/store', 'SystemAccountController@store');
            //atualizar contas
            Route::post('/update', 'SystemAccountController@update');
        });
        //configuracoes do sistema
        Route::get('/system', 'SystemConfigController@index');
        Route::post('/system', 'SystemConfigController@update');
        //configurcoes nanotech
        Route::get('/nanotech', 'NanotechTypeController@index');
        Route::post('/nanotech', 'NanotechTypeController@update');
    });
});


