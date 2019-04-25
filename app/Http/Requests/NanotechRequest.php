<?php

namespace App\Http\Requests;

use App\Enum\EnumNanotechOperationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed amount
 * @property mixed type
 * @property mixed operation_type
 */
class NanotechRequest extends FormRequest
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
            'operation_type' => [
                'required',
                Rule::in([
                    EnumNanotechOperationType::IN,
                    EnumNanotechOperationType::PROFIT_IN,
                ])
            ],
            'type' => 'required|exists:nanotech_types,id',
            'amount' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'operation_type.required' => trans('validation.investment.operation_type_required'),
            'operation_type.in'       => trans('validation.investment.operation_type_in'),
            'type.required'           => trans('validation.investment.type_required'),
            'type.exists'             => trans('validation.investment.type_exists'),
            'amount.required'         => trans('validation.investment.amount_required'),
            'amount.numeric'          => trans('validation.investment.amount_numeric'),
        ];
    }
}
