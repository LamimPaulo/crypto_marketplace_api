<?php

namespace App\Http\Requests;

use App\Enum\EnumPharaosGatewayKeyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed ip
 */
class PharaosGatewayApiKeyRequest extends FormRequest
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
            'type' => [
                'required',
                Rule::in(EnumPharaosGatewayKeyType::TYPE),
            ],
            'ip' => 'required|ip'
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'O tipo de chave deve ser selecionado.',
            'type.in' => 'O tipo de chave selecionado é inválido.',
            'ip.ip' => 'O IP de acesso deve ser válido.',
        ];
    }
}
