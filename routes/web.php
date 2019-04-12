<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $url = \Shivella\Bitly\Facade\Bitly::getUrl('https://www.google.com/');
    echo '<a href="https://wa.me/5547999565570?text=Cristiano%20te%20enviou%20R$100.000,00.%20Acesse:'.$url.'">Enviar</a>';
});

Route::get('/verifyEmail/{token}', 'AuthController@verifyUser');
Route::get('/gateway/{tx}', 'GatewayController@showGateway');
