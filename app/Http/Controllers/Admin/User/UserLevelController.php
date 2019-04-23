<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelRequest;
use App\Models\Product;
use App\Models\TaxCoin;
use App\Models\User\UserLevel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserLevelController extends Controller
{
    public function index()
    {
        try {
            $levels = UserLevel::with(['tax_brl', 'tax_crypto', 'product'])->get();
            return response([
                'message' => trans('messages.general.success'),
                'levels' => $levels
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function enumTypes()
    {
        return [
            'calc_types' => [
                1 => 'Porcentagem',
                2 => 'Decimal'
            ],
            'operation_types' => [
                3 => 'Envio',
                5 => 'Saque',
            ],
            'tax_types' => [
                1 => 'TED',
                2 => 'Operação',
            ]
        ];
    }

    public function store(LevelRequest $request)
    {
        try {
            DB::beginTransaction();
            $level = UserLevel::create($request->all());

            foreach ($request->tax_crypto as $tax) {
                TaxCoin::create([
                    'coin_id' => 1,
                    'user_level_id' => $level->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            foreach ($request->tax_brl as $tax) {
                TaxCoin::create([
                    'coin_id' => 2,
                    'user_level_id' => $level->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Nível Criado com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(LevelRequest $request)
    {
        $request->validate([
            'tax_crypto' => 'nullable|array',
            'tax_brl' => 'nullable|array',
            'tax_brl.*.user_level_id' => 'required_with:tax_brl|exists:user_levels,id',
            'tax_crypto.*.user_level_id' => 'required_with:tax_crypto|exists:user_levels,id'
        ], [
            'tax_brl.*.user_level_id.required_with' => 'O nível de usuário deve ser informado corretamente para a atualização. (BRL)',
            'tax_brl.*.user_level_id.exists' => 'O nível de usuário informado é inválido. (BRL)',
            'tax_crypto.*.user_level_id.required_with' => 'O nível de usuário deve ser informado corretamente para a atualização. (Crypto)',
            'tax_crypto.*.user_level_id.exists' => 'O nível de usuário informado é inválido. (Crypto)'
        ]);

        try {
            DB::beginTransaction();
            $level = UserLevel::findOrFail($request->id);
            $level->update($request->all());

            $product = Product::findOrFail($request->product_id);
            $product->value = $request->product['value'];
            $product->value_lqx = $request->product['value_lqx'];
            $product->save();

            //tax coins update
            foreach ($level->taxes as $tax_) {
                $tax_->delete();
            }

            foreach ($request->tax_crypto as $tax) {
                TaxCoin::create([
                    'coin_id' => 1,
                    'user_level_id' => $request->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            foreach ($request->tax_brl as $tax) {
                TaxCoin::create([
                    'coin_id' => 2,
                    'user_level_id' => $request->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Nível Atualizado com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
