<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed payment_coin
 * @property mixed ip
 */
class GatewayApiKeyRequest extends FormRequest
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
            'payment_coin' => [
                'required',
                Rule::in([1, 2, 3]), //coin_id: BTC, BRL, USD respectivamente
            ],
            'ip' => 'nullable|ip'
        ];
    }

    public function messages()
    {
        return [
            'payment_coin.required' => trans('validation.gateway_api.payment_coin_required'),
            'payment_coin.in'       => trans('validation.gateway_api.payment_coin_in'),
            'ip.ip'                 => trans('validation.gateway_api.ip_ip'),
        ];
    }
}
