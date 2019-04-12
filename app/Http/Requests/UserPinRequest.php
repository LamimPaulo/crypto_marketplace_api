<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed pin
 */
class UserPinRequest extends FormRequest
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
            'pin' => 'required|confirmed|min:4|max:4',
        ];
    }

    public function messages()
    {
        return [
            'pin.min' => trans('validation.pin.min'),
            'pin.max' => trans('validation.pin.max'),
            'pin.confirmed' => trans('validation.pin.confirmed'),
        ];
    }

}
