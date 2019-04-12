<?php

namespace App\Http\Requests\Admin;

use App\Enum\EnumMiningPriceType;
use App\Enum\EnumMiningProfitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @property mixed ths_total
 * @property mixed profit
 * @property mixed ths_quota_price
 * @property mixed profit_payout
 */
class MiningPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param Request $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => 'required|unique:mining_plans,name,' . $request->id . ',id',
            'ths_total' => 'required|numeric',
            'ths_quota_price' => 'required|numeric',
            'ths_quota_price_type' => ['required', Rule::in(EnumMiningPriceType::DECIMAL)],
            'profit' => 'required|numeric',
            'profit_type' => ['required', Rule::in([EnumMiningProfitType::DECIMAL, EnumMiningProfitType::CRYPTO, EnumMiningProfitType::PERCENT])],
            'profit_payout' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome do plano é obrigatório.',
            'name.unique' => 'O nome do plano já está em uso.',
            'ths_total.required' => 'O total de ths é obrigatório.',
            'ths_total.numeric' => 'O total de ths deve ser um número válido.',
            'ths_quota_price.required' => 'O preço do ths é obrigatório.',
            'ths_quota_price.numeric' => 'O preço do ths deve ser um número válido.',
            'ths_quota_price_type.required' => 'O tipo de preço do ths é obrigatório.',
            'ths_quota_price_type.in' => 'O tipo de preço do ths selecionado é inválido.',
            'profit.required' => 'O valor de comissão é obrigatório.',
            'profit.numeric' => 'O valor de comissão deve ser um número válido.',
            'profit_type.required' => 'O tipo de comissão é obrigatório.',
            'profit_type.in' => 'O tipo de comissão selecionado é inválido.',
            'profit_payout.required' => 'O pagamento mínimo é obrigatório.',
            'profit_payout.numeric' => 'O pagamento mínimo deve ser um número válido.',
        ];
    }
}
