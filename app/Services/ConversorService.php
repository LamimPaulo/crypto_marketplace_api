<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CoinQuote;

class ConversorService
{

    public static function BTC2BRL($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
        return ($value / $cotacao->average_quote);
    }

    public static function BRL2BTC($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
        return ($cotacao->average_quote / $value);
    }

    public static function BRL2BTCSMAX($value)
    {

        $coin = Coin::getByAbbr('BTC');
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
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
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
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
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
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
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
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
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
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
        $fiat_coin = Coin::getByAbbr('BRL');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $fiat_coin->id])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->buy_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function CRYPTO2FIAT_MIN(float $value, string $crypto, string  $fiat="BRL")
    {
        $crypto_id = Coin::getByAbbr($crypto);
        $fiat_id = Coin::getByAbbr($fiat);

        $cotacao = CoinQuote::where(['coin_id' => $crypto_id->id, 'quote_coin_id' => $fiat_id->id])->first();
        $cotacao->value = sprintf('%.' . $fiat_id->decimal . 'f', floatval($cotacao->sell_quote * $value));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function CRYPTO2FIAT_MAX(float $value, string $crypto, string  $fiat="BRL")
    {
        $crypto_id = Coin::getByAbbr($crypto);
        $fiat_id = Coin::getByAbbr($fiat);

        $cotacao = CoinQuote::where(['coin_id' => $crypto_id->id, 'quote_coin_id' => $fiat_id->id])->first();
        $cotacao->value = sprintf('%.' . $fiat_id->decimal . 'f', floatval($cotacao->buy_quote * $value));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function FIAT2CRYPTO_MAX(float $value, string $crypto, string  $fiat="BRL")
    {
        $crypto_id = Coin::getByAbbr($crypto);
        $fiat_id = Coin::getByAbbr($fiat);

        $cotacao = CoinQuote::where(['coin_id' => $crypto_id->id, 'quote_coin_id' => $fiat_id->id])->first();
        $cotacao->value = sprintf('%.' . $crypto_id->decimal . 'f', floatval($value / $cotacao->buy_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->buy_quote,
            'quote' => $cotacao->average_quote
        ];
    }

    public static function FIAT2CRYPTO_MIN(float $value, string $crypto, string  $fiat="BRL")
    {
        $crypto_id = Coin::getByAbbr($crypto);
        $fiat_id = Coin::getByAbbr($fiat);

        $cotacao = CoinQuote::where(['coin_id' => $crypto_id->id, 'quote_coin_id' => $fiat_id->id])->first();
        $cotacao->value = sprintf('%.' . $crypto_id->decimal . 'f', floatval($value / $cotacao->sell_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote,
        ];
    }

        public static function BTC2USDMIN($value)
    {
        $coin = Coin::getByAbbr('BTC');
        $usd_coin = Coin::getByAbbr('USD');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $usd_coin->id])->first();
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
        $usd_coin = Coin::getByAbbr('USD');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $usd_coin->id])->first();
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
        $usd_coin = Coin::getByAbbr('USD');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $usd_coin->id])->first();
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
        $usd_coin = Coin::getByAbbr('USD');
        $cotacao = CoinQuote::where(['coin_id' => $coin->id, 'quote_coin_id' => $usd_coin->id])->first();
        $cotacao->value = sprintf('%.' . $coin->decimal . 'f', floatval($value / $cotacao->sell_quote));

        return [
            'amount' => $cotacao->value,
            'current' => $cotacao->sell_quote,
            'quote' => $cotacao->average_quote,
        ];
    }

}
