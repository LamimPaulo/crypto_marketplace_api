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
            'sell_tax' => 'required|numeric',
            'admin_tax' => 'required|numeric',
            'is_active' => 'required|boolean',
            'start_amount' => 'required|numeric',
            'start_price' => 'required|numeric',
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

            'name.required' => 'O nome do fundo é obrigatório.',
            'buy_tax.required' => 'A porcentagem de comissão de compra é obrigatória.',
            'sell_tax.required' => 'A porcentagem de comissão de venda é obrigatória.',
            'admin_tax.required' => 'A taxa administrativa é obrigatória.',
            'start_amount.required' => 'O valor inicial investido é obrigatório.',
            'start_price.required' => 'O valor inicial da cota é obrigatório.',

            'name.unique' => 'O nome do fundo já está sendo utilizado',
            'buy_tax.numeric' => 'A porcentagem de comissão de compra deve ser númerica.',
            'sell_tax.numeric' => 'A porcentagem de comissão de venda deve ser númerica.',
            'admin_tax.numeric' => 'A taxa administrativa deve ser númerica (%).',
            'start_amount.numeric' => 'O valor inicial investido deve ser númerico',
            'start_price.numeric' => 'O valor inicial da cota deve ser númerico',
        ];
    }
}
