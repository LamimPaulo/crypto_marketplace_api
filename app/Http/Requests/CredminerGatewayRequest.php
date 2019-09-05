<?php

namespace App\Http\Requests;

use App\Models\Coin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CredminerGatewayRequest extends FormRequest
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
            'fiat_amount' => 'required|numeric',
            'fiat_abbr' => [
                'required',
                Rule::in([Coin::getByAbbr("BRL")->abbr, Coin::getByAbbr("USD")->abbr])
            ],
            'crypto_abbr' => [
                'required',
                Rule::in(Coin::where([
                    'is_crypto' => true,
                    'is_wallet' => true,
                    'is_active' => true,
                ])->pluck('abbr'))
            ]
        ];
    }
}
