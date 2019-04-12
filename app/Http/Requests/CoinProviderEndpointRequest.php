<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoinProviderEndpointRequest extends FormRequest
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
            'endpoint.*' => 'required',
            'name.*' => 'required',
            'method.*' => ['required', Rule::in(['get', 'post', 'delete', 'put'])],
            'provider_id.*' => 'required|exists:coin_providers,id'
        ];
    }
}
