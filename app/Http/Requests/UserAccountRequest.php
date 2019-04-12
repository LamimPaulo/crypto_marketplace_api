<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAccountRequest extends FormRequest
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
            'bank_id'   => 'nullable|exists:banks,id',
            'nickname'  => 'required',
            'email'     => 'nullable|email',
            'type'      => 'required',
        ];
    }

    public function messages()
    {
        return [
            'bank_id.exists'    => trans('validation.user_account.bank_id_exists'),
            'nickname.required' => trans('validation.user_account.nickname_required'),
            'email.email'       => trans('validation.user_account.email_email'),
            'type.required'     => trans('validation.user_account.type_required'),
        ];
    }
}
