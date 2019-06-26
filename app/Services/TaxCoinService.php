<?php

namespace App\Services;

use App\Enum\EnumCalcType;
use App\Enum\EnumOperations;
use App\Enum\EnumOperationType;
use App\Enum\EnumTaxType;
use App\Models\TaxCoin;
use App\Models\TaxCoinTransaction;

class TaxCoinService
{
//    public static function tax($userStatus, $coin_id)
//    {
//        $tax = TaxCoin::sum($userStatus, $coin_id);
//        $quote_coin = auth()->user()->country_id ==31 ? Coin::getByAbbr("BRL")->id : Coin::getByAbbr("USD")->id;
//        $quote = CoinQuote::where(['coin_id'=> $coin_id, 'quote_coin_id'=> $quote_coin])->first();
//
//        $quote->average_quote = floatval($quote->average_quote);
//        $quote->tax = floatval($tax);
//        return $quote;
//    }

    public static function sum($userStatus, $coin_id)
    {
//        $taxas = self::tax($userStatus, $coin_id);
//        return ($taxas->tax / $taxas->average_quote);

    }

    /**
     * Recuperar as taxas de uma operacao baseada na operacao e no status do usuario.
     * @param int $coin_id
     * @param int $operation
     * @param int $user_status
     * @param float $amount valor requisitado
     * @param float $value cotacao do bitcoin
     * @return TaxCoin
     */
    public function show($coin_id = 1, $operation, $user_status, $amount, $value)
    {
        $taxes = TaxCoin::getByOperationAndUserLevel($coin_id, $operation, $user_status);
        return $this->convertValue($taxes, $value, $amount);
    }

    /**
     *
     * @param mixed $taxCoin
     * @param mixed $_value
     * @param mixed $amount
     * @return taxCoin
     */
    private function convertValue($taxCoin, $_value, $amount)
    {

        foreach ($taxCoin as $key => $value) {
            if ($taxCoin[$key]->coin_tax_type !== EnumTaxType::FEE AND $taxCoin[$key]->calc_type === EnumCalcType::DECIMAL) {
                $taxCoin[$key]->value = sprintf('%.8f', $taxCoin[$key]->value / $_value);
            }

            if ($taxCoin[$key]->coin_tax_type !== EnumTaxType::FEE AND $taxCoin[$key]->calc_type === EnumCalcType::PERCENT) {
                $percent = $amount * ($taxCoin[$key]->value / 100);
                $taxCoin[$key]->value = sprintf('%.8f', floatval(($percent / $_value)));
            }
        }
        return $taxCoin;
    }

    public function sumTax($taxCoin)
    {
        $sum = 0;
        foreach ($taxCoin as $key => $value) {
            $sum += $taxCoin[$key]->value;
        }

        return $sum;
    }

    public function calcTax(TaxCoin $taxCoin)
    {
        return $taxCoin;
    }

    public function taxCoinTransactionCreate($taxCoins, $transaction)
    {
        foreach ($taxCoins as $taxCoin) {
            $tax = TaxCoinTransaction::create([
                'tax_coin_id' => $taxCoin->id,
                'crypto' => $taxCoin->value,
                'operation_type' => $taxCoin->operation,
                'operation_id' => $transaction->id
            ]);
        }
    }

    /**usando essa para envios 19062019
     * @param $user_level
     * @param $amount
     * @return float|int
     */
    public static function sumTaxSendCrypto($user_level, $amount)
    {
        $taxCoins = TaxCoin::where([
            'user_level_id' => $user_level,
            'operation' => EnumOperations::CRYPTO_SEND
        ])->get();
        $taxes = 0;

        foreach ($taxCoins as $tax) {
            if ($tax->calc_type == EnumCalcType::DECIMAL) {
                $taxes += $tax->value;
            }

            if ($tax->calc_type == EnumCalcType::PERCENT) {
                $taxes += $amount * $tax->value / 100;
            }
        }

        return $taxes;
    }
}
