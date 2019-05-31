<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyLevelRequest extends FormRequest
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
            'level_id' => 'required|exists:user_levels,id',
            'abbr' => [
                'required',
                Rule::in(['BRL', 'LQX', 'USD'])
            ]
        ];
    }

    public function messages()
    {
        return [
            'level_id.required' => "O identificador do nível é obrigatório.",
            'level_id.exists' => "O nível informado é inválido.",
            'abbr.required' => "A moeda de compra deve ser definida.",
            'abbr.exists' => "A moeda de compra informada é inválida.",
        ];
    }
}
