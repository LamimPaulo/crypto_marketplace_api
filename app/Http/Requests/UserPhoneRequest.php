<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed phone
 */
class UserPhoneRequest extends FormRequest
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
            'phone' => ['required', 'min:11', 'max:11'],
        ];
    }

    public function messages()
    {
        return [
            'phone.required'=> trans('validation.user_phone.phone_required'),
            'phone.min'     => trans('validation.user_phone.phone_min'),
            'phone.max'     => trans('validation.user_phone.phone_max'),
        ];
    }
}
