<?php

use App\Models\CoinProvider;
use App\Models\CoinProviderEndpoint;
use App\Models\CoinProviderEndpointParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CoinProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CoinProvider::truncate();

        $json = File::get("database/json/coin_providers.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinProvider::create([
                'id' => $obj->id,
                'name' => $obj->name,
                'endpoint' => $obj->endpoint,
                'service_key' => $obj->service_key,
                'comission_type' => $obj->comission_type,
                'comission' => $obj->comission,
                'is_active' => $obj->is_active,
            ]);
        }

        CoinProviderEndpoint::truncate();

        $json = File::get("database/json/coin_provider_endpoints.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinProviderEndpoint::create([
                'id' => $obj->id,
                'name' => $obj->name,
                'endpoint' => $obj->endpoint,
                'method' => $obj->method,
                'description' => $obj->description,
                'provider_id' => $obj->provider_id,
            ]);
        }

        CoinProviderEndpointParameter::truncate();

        $json = File::get("database/json/coin_provider_endpoint_parameters.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinProviderEndpointParameter::create([
                'id' => $obj->id,
                'parameter' => $obj->parameter,
                'required' => $obj->required,
                'decription' => $obj->decription,
                'endpoint_id' => $obj->endpoint_id,
            ]);
        }
    }
}
