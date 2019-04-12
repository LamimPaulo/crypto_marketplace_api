<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertRequest extends FormRequest
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
            'amount'    => 'required|numeric',
            'base'      => 'required|different:quote',
            'quote'     => 'required|different:base',
        ];
    }

    public function messages()
    {
        return [
            'amount.required'   => trans('validation.convert.amount_required'),
            'amount.numeric'    => trans('validation.convert.amount_numeric'),
            'base.required'     => trans('validation.convert.base_required'),
            'quote.required'    => trans('validation.convert.quote_required'),
            'base.different'    => trans('validation.convert.base_different'),
            'quote.different'   => trans('validation.convert.quote_different'),
        ];
    }
}
