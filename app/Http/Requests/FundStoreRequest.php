<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed coins
 * @property mixed start_price
 */
class FundStoreRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:funds,name',
            'buy_tax' => 'required|numeric',
            'redemption_tax' => 'required|numeric',
            'early_redemption_tax' => 'required|numeric',
            'coin_id' => 'required|exists:coins,id',
            'price' => 'required|numeric',
            'monthly_profit' => 'required|numeric',
            'validity' => 'required|numeric',
            'is_active' => 'required|boolean',

            'coins' => 'required|array',
            'coins.*.coin_id' => 'required|distinct|exists:coins,id',
            'coins.*.percent' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'coins.required' => 'O fundo deve ser composto de ao menos uma moeda.',
            'coins.*.coin_id.required' => 'É necessário informar a moeda corretamente.',
            'coins.*.coin_id.distinct' => 'É necessário informar moedas distintas.',
            'coins.*.coin_id.exists' => 'É necessário informar moedas válidas.',

            'name.required' => 'O nome do fundo deve ser informado.',
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
