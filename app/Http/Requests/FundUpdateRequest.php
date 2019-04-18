<?php

namespace App\Http\Requests;

use App\Enum\EnumFundType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @property mixed coins
 * @property mixed start_price
 */
class FundUpdateRequest extends FormRequest
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
            'name' => 'required|unique:funds,name,'.$request->id.',id',
            'buy_tax' => 'required|numeric',
            'redemption_tax' => 'required|numeric',
            'early_redemption_tax' => 'required|numeric',
            'coin_id' => 'required|exists:coins,id',
            'price' => 'required|numeric',
            'monthly_profit' => 'required|numeric',
            'validity' => 'required|numeric',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome do fundo é obrigatório.',
            'buy_tax.required' => 'A taxa de compra deve ser informada.',
            'redemption_tax' => 'A taxa de retirada deve ser informada.',
            'early_redemption_tax' => 'A taxa de antecipação deve ser informada.',
            'coin_id' => 'A moeda deve ser informada.',
            'price' => 'O preço do fundo deve ser informado.',
            'monthly_profit' => 'A percentagem de lucro deve ser informada.',
            'validity' => 'O prazo do fundo deve ser informado.',
            'is_active' => 'O status de atividade deve ser informado.',

            'name.unique' => 'O nome do fundo já está sendo utilizado',
            'buy_tax.numeric' => 'A taxa de compra deve ser em formato númerico (%).',
            'redemption_tax.numeric' => 'A taxa de retirada deve ser em formato númerico (%).',
            'early_redemption_tax.numeric' => 'A taxa de antecipação deve ser em formato númerico (%).',
            'coin_id.exists',
            'price.numeric' => 'O preço do fundo deve ser em formato númerico (%).',
            'monthly_profit.numeric' => 'A percentagem de lucro deve ser em formato númerico (%).',
            'validity.numeric' => 'O prazo do fundo deve ser em formato númerico (meses).',
            'is_active.boolean' => 'O status do fundo deve ser ativo ou inativo.',
        ];
    }
}
