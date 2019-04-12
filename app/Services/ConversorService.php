<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CoinQuote;

class ConversorService
{

    public static function BTC2BRL($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        return ($value / $cotacao->average_quote);
    }

    public static function BRL2BTC($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        return ($cotacao->average_quote / $value);
    }

    public static function BRL2BTCSMAX($value)
    {

        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->buy_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }


    public static function BRL2BTCMIN($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->sell_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function BTC2BRLMAX(float $value)
    {

        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($cotacao->buy_quote * $value));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }


    public static function BTC2BRLMIN($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($cotacao->sell_quote * $value));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote
        ];
    }


    public static function BRLTAX2BTCMIN($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->sell_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote,
        ];
    }

    public static function BRLTAX2BTCMAX($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->buy_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function BTC2USDMIN($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 3])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($cotacao->sell_quote * $value));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function BTC2USDMAX(float $value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 3])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($cotacao->buy_quote * $value));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function USD2BTCMAX($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 3])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->buy_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function USDTAX2BTCMIN($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $cotacao = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 3])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->sell_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote,
        ];
    }


}
