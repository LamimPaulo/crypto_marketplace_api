<?php

namespace App\Http\Requests;

use App\Enum\EnumUserTicketsDepartments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserTicketRequest extends FormRequest
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
            'department' => [
                'required',
                Rule::in(array_keys(EnumUserTicketsDepartments::DEPARTMENT))
            ],
            'subject' => 'required',
            'message' => 'required',
            'file' => 'nullable|file|max:1024|mimes:jpeg,png,pdf',
        ];
    }

    public function messages()
    {
        return [
            'department.required' => "É necessário informar um departamento.",
            'department.in' => "O departamento informado é inválido.",
            'subject.required' => "É necessário informar o assunto.",
            'message.required' => "É necessário enviar uma mensagem.",
            'file.mimes' => trans('validation.deposit.file.mimes'),
            'file.max' => trans('validation.deposit.file.max'),
        ];
    }
}
