<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDecimalsCypto extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('amount', 28, 8)->change();
            $table->decimal('fee', 28, 8)->default(0)->change();
            $table->decimal('tax', 28, 8)->default(0)->change();
            $table->decimal('price', 28, 8)->nullable()->change();
            $table->decimal('market', 28, 8)->nullable()->change();
        });

        Schema::table('exchange_trades', function (Blueprint $table) {
            $table->decimal('amount', 28, 8)->default(0)->change();
            $table->decimal('price', 28, 8)->default(0)->change();
            $table->decimal('total', 28, 8)->default(0)->change();
            $table->decimal('fee', 28, 8)->default(0)->change();
            $table->decimal('profit', 28, 8)->default(0)->change();
            $table->decimal('profit_percent', 28, 8)->default(0)->change();
            $table->decimal('base_price', 28, 8)->default(0)->change();
            $table->decimal('quote_price', 28, 8)->default(0)->change();
        });

        Schema::table('user_levels', function (Blueprint $table) {
            $table->decimal('limit_btc_diary', 28, 8)->change();
            $table->decimal('limit_brl_diary', 28, 8)->change();
            $table->decimal('limit_transaction_auto', 28, 8)->change();
        });

        Schema::table('tax_coins', function (Blueprint $table) {
            $table->decimal('value', 28, 8)->change();
        });

        Schema::table('coins', function (Blueprint $table) {
            $table->decimal('fee_high', 28, 8)->default(0)->change();
            $table->decimal('fee_avg', 28, 8)->default(0)->change();
            $table->decimal('fee_low', 28, 8)->default(0)->change();
        });

        Schema::table('gateway', function (Blueprint $table) {
            $table->decimal('fiat_amount', 28, 8)->change();
            $table->decimal('amount', 28, 8)->nullable()->change();
            $table->decimal('value', 28, 8)->nullable()->change();
            $table->decimal('received', 28, 8)->nullable()->change();
            $table->decimal('tax', 28, 8)->change();
        });

        Schema::table('exchange_limits', function (Blueprint $table) {
            $table->decimal('max_trade_amount', 28, 8)->default(1)->change();
            $table->decimal('min_trade_profit', 28, 8)->default(0.5)->change();
        });

        Schema::table('sys_configs', function (Blueprint $table) {
            $table->decimal('send_min_btc', 28, 8)->default(0.0004)->change();
        });

        Schema::table('nanotech_operations', function (Blueprint $table) {
            $table->decimal('amount', 28, 8)->change();
            $table->decimal('brokerage_fee', 28, 8)->default(0)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price', 28, 8)->default(0)->change();
            $table->decimal('orig_qty', 28, 8)->default(0)->change();
            $table->decimal('executed_qty', 28, 8)->default(0)->change();
            $table->decimal('cummulative_quote_qty', 28, 8)->default(0)->change();
            $table->decimal('stop_price', 28, 8)->default(0)->change();
            $table->decimal('iceberg_qty', 28, 8)->default(0)->change();
        });
        
        Schema::table('tax_coin_transactions', function (Blueprint $table) {
            $table->decimal('crypto', 28, 8)->default(0)->change();
        });
        
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->decimal('balance',28, 8)->default(0)->unsigned()->change();
        });
        
        Schema::table('nanotech', function (Blueprint $table) {
            $table->decimal('amount', 28, 8)->change();
        });

        Schema::table('fund_balances', function (Blueprint $table) {
            $table->decimal('balance_blocked', 28, 8)->default(0)->change();
            $table->decimal('balance_free', 28, 8)->default(0)->change();
        });

        DB::statement("UPDATE user_wallets SET sync = 0 WHERE coin_id = 1");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
