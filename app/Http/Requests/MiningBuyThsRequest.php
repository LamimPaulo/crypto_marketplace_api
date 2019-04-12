<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed ths_quantity
 * @property mixed payment
 */
class MiningBuyThsRequest extends FormRequest
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
            'payment' => [
                'required',
                Rule::in([1, 2, 3]), //coin_id: BTC, BRL, USD respectivamente
            ],
            'ths_quantity' => 'required|int'
        ];
    }

    public function messages()
    {
        return [
            'payment.required'      => trans('validation.mining_buy_ths.payment_required'),
            'payment.in'            => trans('validation.mining_buy_ths.payment_in'),
            'ths_quantity.required' => trans('validation.mining_buy_ths.ths_quantity_required'),
            'ths_quantity.int'      => trans('validation.mining_buy_ths.ths_quantity_int'),
        ];
    }
}
