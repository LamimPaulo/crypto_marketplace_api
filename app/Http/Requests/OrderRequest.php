<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed symbol
 * @property mixed side
 * @property mixed type
 * @property mixed quantity
 * @property mixed price
 */
class OrderRequest extends FormRequest
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
            'symbol' => 'required|exists:coin_pairs,name',
            'quantity' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'symbol.required' => trans('validation.order.symbol_required'),
            'symbol.exists' => trans('validation.order.symbol_exists'),
            'side.required' => trans('validation.order.side_required'),
            'side.in' => trans('validation.order.side_in'),
        ];
    }
}
