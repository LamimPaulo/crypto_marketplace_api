<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed user_account_id
 * @property mixed amount
 */
class DraftRequest extends FormRequest
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
            'amount'          => 'required',
            'user_account_id' => 'required|exists:user_accounts,id',
            'tax_id'          => 'required|exists:withdrawal_deadlines,id',
        ];
    }

    public function messages()
    {
        return [
            'amount.required'          => trans('validation.amount_required'),
            'user_account_id.required' => trans('validation.user_account_id_required'),
            'user_account_id.exists'   => trans('validation.user_account_id_exists'),
        ];
    }
}
