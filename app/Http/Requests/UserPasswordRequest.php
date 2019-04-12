<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed cpf
 * @property mixed document
 */
class UserPasswordRequest extends FormRequest
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
            'password' => 'required|confirmed|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
        ];
    }

    public function messages()
    {
        return [
            'password.min' => 'A senha deve conter um mínimo de 6 caracteres.',
            'password.regex' => 'A senha deve conter ao menos uma letra Maíuscula, uma letra mínuscula, um número e um caracter especial.',
            'password.confirmed' => 'A confirmação deve corresponder com a senha.'
        ];
    }

}
