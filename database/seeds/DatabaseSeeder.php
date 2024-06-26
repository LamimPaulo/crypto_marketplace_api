<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if(!\App\Models\Bank::first()){
            $this->call(BanksSeeder::class);
        }

        if(!\App\Models\CoinQuote::first()){
            $this->call(CoinQuotesSeeder::class);
        }

        if(!\App\Models\Coin::first()){
            $this->call(CoinSeeder::class);
        }

        if(!\App\Models\Country::first()){
            $this->call(CountriesTableSeeder::class);
        }

        if(!\App\Models\User\DocumentType::first()){
            $this->call(DocumentTypes::class);
        }

        if(!App\Models\Exchange\Exchanges::first()){
            $this->call(ExchangesSeeder::class);
        }

        if(!\App\Models\Exchange\ExchangeTax::first()){
            $this->call(ExchangeTaxSeeder::class);
        }

        if(!\App\Models\Nanotech\NanotechType::first()){
            $this->call(NanotechTypesSeeder::class);
        }

        if(!\App\Models\Product::first()){
            $this->call(ProductSeeder::class);
        }

        if(!\App\Models\ProductType::first()){
            $this->call(ProductTypeSeeder::class);
        }

        if(!\App\Models\SysConfig::first()){
            $this->call(SysConfigsSeeder::class);
        }

        if(!\App\Models\System\SystemAccount::first()){
            $this->call(SystemAccountsSeeder::class);
        }

        if(!\App\Models\TaxCoin::first()){
            $this->call(TaxCoinSeeder::class);
        }

        if(!\App\Models\User\UserLevel::first()){
            $this->call(UserLevelSeeder::class);
        }
    }
}
