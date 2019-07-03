<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
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
            'zip_code' => 'required',
            'state' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'address' => 'required|string',
            'number' => 'required|string',
            'complement' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'zip_code.required' => 'O cep é obrigatório.',
            'state.required'    => 'O estado/uf é obrigatório.',
            'city.required'     => 'O cidade é obrigatório.',
            'district.required' => 'O bairro é obrigatório.',
            'address.required'  => 'O endereço é obrigatório.',
            'number.required'   => 'O número do endereço é obrigatório.',
        ];
    }
}
