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
            'sell_tax' => 'required|numeric',
            'admin_tax' => 'required|numeric',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome do fundo é obrigatório.',
            'buy_tax.required' => 'A porcentagem de comissão de compra é obrigatória.',
            'sell_tax.required' => 'A porcentagem de comissão de venda é obrigatória.',
            'admin_tax.required' => 'A taxa administrativa é obrigatória.',

            'name.unique' => 'O nome do fundo já está sendo utilizado',
            'buy_tax.numeric' => 'A porcentagem de comissão de compra deve ser númerica.',
            'sell_tax.numeric' => 'A porcentagem de comissão de venda deve ser númerica.',
            'admin_tax.numeric' => 'A taxa administrativa deve ser númerica (%).',
            'is_active.required' => 'A situação do fundo deve ser informada.',
        ];
    }
}
