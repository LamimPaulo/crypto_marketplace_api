<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed amount
 * @property mixed type
 * @property mixed operation_type
 */
class WithdrawalCredminerRequest extends FormRequest
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
            'amount' => 'required|numeric',
            'api_key' => 'required|exists:users,api_key',
            'coin' => [
                'required',
                'exists:coins,abbr',
                Rule::in(['LQXD', 'LQX'])
            ]
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => "O valor é obrigatório.",
            'api_key.required' => "O Keycode é obrigatório.",
            'coin.required' => "A moeda é obrigatória.",

            'amount.numeric' => "O valor informado é inválido. (usar somente números e ponto).",
            'api_key.exists' => "O Keycode é inválido.",
            'coin.exists' => "A moeda é inválida.",
        ];
    }
}
