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
class CheckCpfRequest extends FormRequest
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
            'cpf' => 'required|cpf'
        ];
    }

    public function messages()
    {
        return [
            'cpf.required' => "O número de cpf é obrigatório.",
            'cpf.cpf' => "O número de cpf informado é inválido.",
        ];
    }
}
