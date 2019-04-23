<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CoinRequest extends FormRequest
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
            'name' => 'required|unique:coins,name,' . $request->id . ',id',
            'shortname' => 'required|unique:coins,shortname,' . $request->id . ',id',
            'abbr' => 'required|unique:coins,abbr,' . $request->id . ',id',
            'is_active' => 'required|boolean',
            'decimal' => 'required|numeric',
            'is_crypto' => 'required|boolean',
            'is_wallet' => 'required|boolean',
            'buy_tax' => 'required|numeric',
            'sell_tax' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'name.required'          => 'É necessário informar um nome para a moeda.',
            'name.unique'            => 'O nome informado já está em uso.',
            'shortname.required'     => 'É necessário informar um simbolo para a moeda.',
            'shortname.unique'       => 'O símbolo informado já está em uso.',
            'abbr.required'          => 'É necessário informar a abreviação para a moeda.',
            'abbr.unique'            => 'A abreviação informada já está em uso.',
            'is_active.required'     => 'É necessário informar a situação da moeda.',
            'decimal.required'       => 'É necessário informar a quantidade de casas decimais para a moeda.',
            'decimal.numeric'        => 'A quantidade de casas decimais deve ser em formato numérico.',
            'is_crypto.required'     => 'É necessário informar o tipo de moeda.',
            'is_wallet.required'     => 'É necessário informar o tipo de produto.',
            'buy_tax.required'       => 'É necessário informar a taxa de venda para a moeda.',
            'buy_tax.numeric'        => 'A taxa de venda deve ser em formato numérico.',
            'sell_tax.required'       => 'É necessário informar a taxa de compra para a moeda.',
            'sell_tax.numeric'        => 'A taxa de venda deve ser em formato numérico.',
        ];
    }
}
