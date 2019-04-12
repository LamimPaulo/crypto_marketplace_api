<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendCryptoRequest extends FormRequest
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
            'toAddress' => 'required',
            'amount'    => 'required|numeric',
            'address'   => 'required|exists:user_wallets',
            'priority'  => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'address.required'  => trans('validation.send_crypto.address_required'),
            'address.exists'    => trans('validation.send_crypto.address_exists'),
            'priority.required' => trans('validation.send_crypto.priority_required'),
            'toAddress.required'=> trans('validation.send_crypto.toAddress_required'),
            'amount.required'   => trans('validation.send_crypto.amount_required'),
            'amount.numeric'    => trans('validation.send_crypto.amount_numeric'),
        ];
    }
}
