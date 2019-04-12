<?php

namespace App\Http\Requests;

use App\Enum\EnumPharaosGatewayKeyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed ip
 */
class PharaosGatewayConvertRequest extends FormRequest
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
            'coin' => 'required|exists:coins,abbr',
            'amount' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'coin.required' => 'A moeda deve ser informada.',
            'coin.exists' => 'A moeda deve ser válida. (Abreviatura ou Símbolo informado não existe).',
            'amount.required' => 'O valor deve ser informado.',
            'amount.numeric' => 'O valor deve ser um número válido (somente número e ponto).',
        ];
    }
}
