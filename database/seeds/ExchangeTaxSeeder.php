<?php

use App\Enum\EnumCalcType;
use App\Enum\EnumExchangeOperation;
use App\Models\Coin;
use App\Models\Exchange\Exchanges;
use App\Models\Exchange\ExchangeTax;
use Illuminate\Database\Seeder;

class ExchangeTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $btc = Coin::getByAbbr('BTC')->id;
        $brl = Coin::getByAbbr('BRL')->id;
        $braziliex = Exchanges::where('name', 'braziliex')->first()->id;
        $mercado = Exchanges::where('name', 'mercado')->first()->id;
        $negociecoins = Exchanges::where('name', 'negociecoins')->first()->id;

        $tax = [
            [
                'exchange_id' => $braziliex,
                'coin_id' => $btc,
                'type' => EnumExchangeOperation::TRADE,
                'calc_type' => EnumCalcType::PERCENT,
                'value' => 0.5
            ], [
                'exchange_id' => $braziliex,
                'coin_id' => $brl,
                'type' => EnumExchangeOperation::DRAFT,
                'calc_type' => EnumCalcType::DECIMAL,
                'value' => 9
            ], [
                'exchange_id' => $braziliex,
                'coin_id' => $brl,
                'type' => EnumExchangeOperation::DRAFT,
                'calc_type' => EnumCalcType::PERCENT,
                'value' => 0.25
            ],
            [
                'exchange_id' => $mercado,
                'coin_id' => $btc,
                'type' => EnumExchangeOperation::TRADE,
                'calc_type' => EnumCalcType::PERCENT,
                'value' => 0.7
            ], [
                'exchange_id' => $mercado,
                'coin_id' => $brl,
                'type' => EnumExchangeOperation::DRAFT,
                'calc_type' => EnumCalcType::DECIMAL,
                'value' => 2.9
            ], [
                'exchange_id' => $mercado,
                'coin_id' => $brl,
                'type' => EnumExchangeOperation::DRAFT,
                'calc_type' => EnumCalcType::PERCENT,
                'value' => 1.99
            ], [
                'exchange_id' => $negociecoins,
                'coin_id' => $btc,
                'type' => EnumExchangeOperation::TRADE,
                'calc_type' => EnumCalcType::PERCENT,
                'value' => 0.4
            ], [
                'exchange_id' => $negociecoins,
                'coin_id' => $brl,
                'type' => EnumExchangeOperation::DRAFT,
                'calc_type' => EnumCalcType::DECIMAL,
                'value' => 8.9
            ], [
                'exchange_id' => $negociecoins,
                'coin_id' => $brl,
                'type' => EnumExchangeOperation::DRAFT,
                'calc_type' => EnumCalcType::PERCENT,
                'value' => 0.9
            ],
        ];

        foreach ($tax as $tx) {
            ExchangeTax::create($tx);
        }
    }
}
