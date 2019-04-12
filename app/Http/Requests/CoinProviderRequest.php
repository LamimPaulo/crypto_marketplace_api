<?php

namespace App\Http\Requests;

use App\Enum\EnumCoinProviderComissionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoinProviderRequest extends FormRequest
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
     * @param Request $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => 'required|unique:coin_providers,name,' . $request->id . ',id',
            'endpoint' => 'required|unique:coin_providers,endpoint,' . $request->id . ',id',
            'comission' => 'required|numeric',
            'comission_type' => ['required', Rule::in([EnumCoinProviderComissionType::PERCENT, EnumCoinProviderComissionType::DECIMAL])],
            'service_key' => 'required|unique:coin_providers,service_key,' . $request->id . ',id',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Você deve informar um nome para a exchange.',
            'name.unique' => 'O nome informado para a exchange já está sendo utilizado.',
            'endpoint.required' => 'Você deve informar um endpoint base para a exchange.',
            'endpoint.unique' => 'O endpoint base informado para a exchange já está sendo utilizado.',
            'comission.required' => 'Você deve informar um valor de comissão da exchange.',
            'comission.numeric' => 'O valor de comissão da exchange deve ser um número.',
            'service_key.required' => 'A chave de configuração deve ser informada.',
            'service_key.unique' => 'A chave de configuração informada já está sendo utilizada.',
            'comission_type.required' => 'O tipo de comissão deve ser informado para a exchange.',
            'comission_type.in' => 'O tipo de comissão deve informado é inválido.',
            'is_active.required' => 'A situação da exchange deve ser informada.',
            'is_active.boolean' => 'A situação da exchange deve ser ativa ou inativa.',
        ];
    }
}
