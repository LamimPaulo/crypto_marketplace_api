<?php

use App\Models\CoinQuote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CoinQuotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CoinQuote::truncate();

        $json = File::get("database/json/coin_quotes.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinQuote::create([
                'coin_id' => $obj->coin_id,
                'quote_coin_id' => $obj->quote_coin_id,
                'average_quote' => $obj->average_quote,
                'last_quote' => $obj->last_quote,
                'buy_quote' => $obj->buy_quote,
                'sell_quote' => $obj->sell_quote,
            ]);
        }
    }
}
