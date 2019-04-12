<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed amount
 * @property mixed system_account_id
 * @property mixed file
 */
class DepositRequest extends FormRequest
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
            'amount'            => 'required',
            'system_account_id' => 'required|exists:system_accounts,id',
            'file'              => 'required|file|max:1024|mimes:jpeg,png,pdf',
        ];
    }

    public function messages()
    {
        return [
            'amount.required'               => trans('validation.deposit.amount.required'),
            'system_account_id.required'    => trans('validation.deposit.system_account_id.required'),
            'system_account_id.exists'      => trans('validation.deposit.system_account_id.exists'),
            'file.mimes'                    => trans('validation.deposit.file.mimes'),
            'file.max'                      => trans('validation.deposit.file.max'),
            'file.required'                 => trans('validation.deposit.file.required'),
        ];
    }
}
