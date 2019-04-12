<?php

namespace App\Http\Requests;

use App\Enum\EnumFundType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @property mixed quotes
 * @property mixed fund_id
 */
class FundRequest extends FormRequest
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
            'quotes' => 'required|integer',
            'fund_id' => 'required|exists:funds,id',
        ];
    }

    public function messages()
    {
        return [
            'quotes.required'   => trans('validation.fund.quotes_required'),
            'quotes.integer'    => trans('validation.fund.quotes_integer'),
            'fund_id.exists'    => trans('validation.fund.fund_id_exists'),
            'fund_id.required'  => trans('validation.fund.fund_id_required'),
        ];
    }
}
