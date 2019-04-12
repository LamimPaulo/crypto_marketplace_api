<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed email
 * @property mixed amount
 */
class TransferRequest extends FormRequest
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
            'amount' => 'required|numeric',
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => trans('validation.transfer.amount_required'),
            'amount.numeric'  => trans('validation.transfer.amount_numeric'),
            'email.required'  => trans('validation.transfer.email_required'),
            'email.email'     => trans('validation.transfer.email_email'),
            'email.exists'    => trans('validation.transfer.email_exists'),
        ];
    }
}
