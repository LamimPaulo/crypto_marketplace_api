<?php

namespace App\Http\Requests;

use App\Enum\EnumNanotechOperationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed amount
 * @property mixed type
 * @property mixed operation_type
 */
class NanotechInfoRequest extends FormRequest
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
            'type' => 'required|exists:nanotech_types,id',
            'api_key' => 'required|exists:users,api_key'
        ];
    }

    public function messages()
    {
        return [
            'type.required' => "O identificador do produto é obrigatório.",
            'api_key.required' => "O Keycode é obrigatório.",
            'type.exists' => "O identificador do produto é inválido.",
            'api_key.exists' => "O Keycode é inválido.",
        ];
    }
}
