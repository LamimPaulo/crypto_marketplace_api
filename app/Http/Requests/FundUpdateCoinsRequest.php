<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed coins
 */
class FundUpdateCoinsRequest extends FormRequest
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
            'id' => 'required|exists:funds,id',
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

            'coins.*.percent.required' => 'É necessário informar os percentuais corretamente.',
            'coins.*.percent.numeric' => 'É necessário informar os percentuais em formato númerico válido.',

            'id.required' => 'O fundo pertencente das moedas é necessário.',
            'id.exists' => 'O fundo pertencente das moedas informado é inválido.',
        ];
    }
}
