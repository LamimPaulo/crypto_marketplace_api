<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed cpf
 * @property mixed document
 */
class InternationalUserRequest extends FormRequest
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
            'document' => 'required|numeric|unique:users|min:4',
            'birthdate' => 'required|date',
            'name' => 'required|min:4',
            'gender' => 'required',
            'mothers_name' => 'required|min:4',
        ];
    }

    public function messages()
    {
        return [
            'document.required' => 'The document is required.',
            'document.unique' => 'The document number is already in use.',
            'document.numeric' => 'The document must be a valid number (only numbers).',
        ];
    }

}
