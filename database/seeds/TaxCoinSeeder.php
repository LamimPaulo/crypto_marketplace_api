<?php

use App\Enum\EnumCalcType;
use App\Enum\EnumOperations;
use App\Enum\EnumTaxType;
use App\Enum\EnumUserLevel;
use App\Enum\EnumFee;
use App\Models\TaxCoin;
use Illuminate\Database\Seeder;

class TaxCoinSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TaxCoin::truncate();

        /////////////////////////////// SAQUE EM REAL - NIVEL INICIANTE
        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::TED,
            'value' => 19.60,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::OPERACAO,
            'value' => 1.99,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        /////////////////////////////// SAQUE EM REAL - NIVEL BASIC

        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::TED,
            'value' => 19.60,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::OPERACAO,
            'value' => 1.99,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        /////////////////////////////// SAQUE EM REAL - NIVEL PRO
        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::TED,
            'value' => 19.60,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::OPERACAO,
            'value' => 1.5,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        /////////////////////////////// SAQUE EM REAL - NIVEL GOLD
        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::TED,
            'value' => 19.60,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::OPERACAO,
            'value' => 1,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        /////////////////////////////// SAQUE EM REAL - NIVEL INFINIT
        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::TED,
            'value' => 19.60,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 2,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::OPERACAO,
            'value' => 0.5,
            'operation' => EnumOperations::FIAT_WITHDRAW,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        ///////////////////////////////////////////////////////////////// PAGAMENTO GATEWAY

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::GATEWAY_PAID,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::GATEWAY_PAID,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::GATEWAY_PAID,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::GATEWAY_PAID,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::GATEWAY_WITHDRAW,
            'calc_type' => EnumCalcType::DECIMAL
        ]);

        ///////////////////////////////////////////////////////////////// ENVIAR BITCOIN

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);


        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::FEE,
            'value' => EnumFee::BTC,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::FEE,
            'value' => EnumFee::BTC,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::FEE,
            'value' => EnumFee::BTC,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::FEE,
            'value' => EnumFee::BTC,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::FEE,
            'value' => EnumFee::BTC,
            'operation' => EnumOperations::CRYPTO_SEND,
            'calc_type' => EnumCalcType::PERCENT
        ]);


        ///////////////////////////////////////////////////////////////// VENDER BITCOIN


        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SELL,
            'calc_type' => EnumCalcType::PERCENT
        ]);


        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SELL,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SELL,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SELL,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_SELL,
            'calc_type' => EnumCalcType::PERCENT
        ]);


        ///////////////////////////////////////////////////////////////// COMPRAR BITCOIN

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::FREE,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_BUY,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::BASIC,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_BUY,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::PRO,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_BUY,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::GOLD,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_BUY,
            'calc_type' => EnumCalcType::PERCENT
        ]);

        TaxCoin::create([
            'coin_id' => 1,
            'user_level_id' => EnumUserLevel::INFINITY,
            'coin_tax_type' => EnumTaxType::SISTEMA,
            'value' => 1,
            'operation' => EnumOperations::CRYPTO_BUY,
            'calc_type' => EnumCalcType::PERCENT
        ]);
    }

}
