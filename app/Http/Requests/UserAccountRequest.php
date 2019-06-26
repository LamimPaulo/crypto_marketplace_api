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
            'nickname'  => 'required',
            'email'     => 'nullable|email',
            'type'      => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nickname.required' => trans('validation.user_account.nickname_required'),
            'email.email'       => trans('validation.user_account.email_email'),
            'type.required'     => trans('validation.user_account.type_required'),
        ];
    }
}
