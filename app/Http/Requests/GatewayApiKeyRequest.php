<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed payment_coin
 * @property mixed ip
 * @property mixed device_number
 * @property mixed serial_number
 * @property mixed activation_code
 * @property mixed infinitepay_wallet
 * @property mixed status
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
            'user_email' => 'required|exists:users,email',
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
            'user_email.required' => "A identificação do usuário deve ser informada.",
            'user_email.exists' => "O usuário informado é inválido.",
            'payment_coin.required' => trans('validation.gateway_api.payment_coin_required'),
            'payment_coin.in' => trans('validation.gateway_api.payment_coin_in'),
            'ip.ip' => trans('validation.gateway_api.ip_ip'),
        ];
    }
}
