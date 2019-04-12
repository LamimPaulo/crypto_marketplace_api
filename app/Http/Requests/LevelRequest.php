<?php

namespace App\Http\Requests;

use App\Enum\EnumCalcType;
use App\Enum\EnumOperations;
use App\Enum\EnumTaxType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @property mixed id
 * @property mixed tax_crypto
 * @property mixed tax_brl
 * @property mixed tax_usd
 */
class LevelRequest extends FormRequest
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
            'name' => 'required|unique:user_levels,name,'.$request->id.',id',
            'limit_btc_diary' => 'required|numeric',
            'limit_brl_diary' => 'required|numeric',
            'limit_usd_diary' => 'required|numeric',
            'limit_transaction_auto' => 'required|numeric',
            'brokerage_fee' => 'required|numeric',
            'is_referrable' => 'required|boolean',
            'referral_profit' => 'required|numeric',
            'gateway_tax' => 'required|numeric',
            'is_active' => 'required|boolean',
            'is_allowed_buy_with_fiat' => 'required|boolean',
            'is_allowed_sell_by_fiat' => 'required|boolean',

            'tax_crypto' => 'nullable|array',
            'tax_brl' => 'nullable|array',
            'tax_usd' => 'nullable|array',

            'tax_crypto.*.coin_tax_type' => ['required_with:tax_crypto', Rule::in(array_keys(EnumTaxType::OPERATIONS))],
            'tax_crypto.*.value'         => 'required_with:tax_crypto|numeric',
            'tax_crypto.*.operation'     => ['required_with:tax_crypto', Rule::in(array_keys(EnumOperations::OPERATIONS))],
            'tax_crypto.*.calc_type'     => ['required_with:tax_crypto', Rule::in(array_keys(EnumCalcType::TYPE))],

            'tax_brl.*.coin_tax_type' => ['required_with:tax_brl', Rule::in(array_keys(EnumTaxType::OPERATIONS))],
            'tax_brl.*.value'         => 'required_with:tax_brl|numeric',
            'tax_brl.*.operation'     => ['required_with:tax_brl', Rule::in(array_keys(EnumOperations::OPERATIONS))],
            'tax_brl.*.calc_type'     => ['required_with:tax_brl', Rule::in(array_keys(EnumCalcType::TYPE))],

            'tax_usd.*.coin_tax_type' => ['required_with:tax_usd', Rule::in(array_keys(EnumTaxType::OPERATIONS))],
            'tax_usd.*.value'         => 'required_with:tax_usd|numeric',
            'tax_usd.*.operation'     => ['required_with:tax_usd', Rule::in(array_keys(EnumOperations::OPERATIONS))],
            'tax_usd.*.calc_type'     => ['required_with:tax_usd', Rule::in(array_keys(EnumCalcType::TYPE))],
        ];
    }

    public function messages()
    {
        return [
            'tax_crypto.*.coin_tax_type.required_with'=> 'O tipo de taxa crypto é obrigatório',
            'tax_crypto.*.coin_tax_type.in'           => 'O tipo de taxa crypto selecionado é inválido',
            'tax_crypto.*.value.required_with'        => 'O valor da taxa crypto é obrigatório',
            'tax_crypto.*.value.numeric'              => 'O valor da taxa crypto deve ser um número válido',
            'tax_crypto.*.operation.required_with'    => 'O tipo de operação da taxa crypto é obrigatório',
            'tax_crypto.*.operation.in'               => 'O tipo de operação da taxa crypto selecionado é inválido',
            'tax_crypto.*.calc_type.required_with'    => 'O tipo de cálculo da taxa crypto é obrigatório',
            'tax_crypto.*.calc_type.in'               => 'O tipo de cálculo da taxa crypto selecionado é inválido',

            'tax_brl.*.coin_tax_type.required_with'=> 'O tipo de taxa brl é obrigatório',
            'tax_brl.*.coin_tax_type.in'           => 'O tipo de taxa brl selecionado é inválido',
            'tax_brl.*.value.required_with'        => 'O valor da taxa brl é obrigatório',
            'tax_brl.*.value.numeric'              => 'O valor da taxa brl deve ser um número válido',
            'tax_brl.*.operation.required_with'    => 'O tipo de operação da taxa brl é obrigatório',
            'tax_brl.*.operation.in'               => 'O tipo de operação da taxa brl selecionado é inválido',
            'tax_brl.*.calc_type.required_with'    => 'O tipo de cálculo da taxa brl é obrigatório',
            'tax_brl.*.calc_type.in'               => 'O tipo de cálculo da taxa brl selecionado é inválido',

            'tax_usd.*.coin_tax_type.required_with'=> 'O tipo de taxa usd é obrigatório',
            'tax_usd.*.coin_tax_type.in'           => 'O tipo de taxa usd selecionado é inválido',
            'tax_usd.*.value.required_with'        => 'O valor da taxa usd é obrigatório',
            'tax_usd.*.value.numeric'              => 'O valor da taxa usd deve ser um número válido',
            'tax_usd.*.operation.required_with'    => 'O tipo de operação da taxa usd é obrigatório',
            'tax_usd.*.operation.in'               => 'O tipo de operação da taxa usd selecionado é inválido',
            'tax_usd.*.calc_type.required_with'    => 'O tipo de cálculo da taxa usd é obrigatório',
            'tax_usd.*.calc_type.in'               => 'O tipo de cálculo da taxa usd selecionado é inválido',

            'name.required' => 'O nome do nível é obrigatório.',
            'name.unique' => 'O nome do nível já está em uso.',

            'limit_btc_diary.required' => 'O limite diário para envios de BTC deve ser informado.',
            'limit_btc_diary.numeric' => 'O limite diário para envios de BTC deve ser um valor númerico válido.',

            'limit_brl_diary.required' => 'O limite diário para saques de BRL deve ser informado.',
            'limit_brl_diary.numeric' => 'O limite diário para saques de BRL deve ser um valor númerico válido.',

            'limit_usd_diary.required' => 'O limite diário para saques de USD deve ser informado.',
            'limit_usd_diary.numeric' => 'O limite diário para saques de USD deve ser um valor númerico válido.',

            'limit_transaction_auto.required' => 'O limite automático para envio de BTC deve ser informado.',
            'limit_transaction_auto.numeric' => 'O limite automático para envio de BTC deve ser um valor númerico válido.',

            'brokerage_fee.required' => 'A taxa de corretagem deve ser informada.',
            'brokerage_fee.numeric' => 'A taxa de corretagem deve ser um valor númerico válido.',

            'referral_profit.required' => 'A porcentagem de lucro por afiliado deve ser informada.',
            'referral_profit.numeric' => 'A porcentagem de lucro por afiliado deve ser um valor númerico válido.',

            'gateway_tax.required' => 'A taxa de uso do Gateway deve ser informada.',
            'gateway_tax.numeric' => 'A taxa de uso do Gateway deve ser um valor númerico válido.',

            'is_referrable.required' => 'A permissão de afiliados deve ser informada.',
            'is_referrable.boolean' => 'A permissão de afiliados deve ser sim ou não.',

            'is_active.required' => 'A situação do nível deve ser informada.',
            'is_active.boolean' => 'A situação do nível deve ser ativo ou inativo.',

            'is_allowed_buy_with_fiat.required' => 'A Habilidade de compra do nível deve ser informada.',
            'is_allowed_buy_with_fiat.boolean' => 'A Habilidade de compra do nível deve ser ativo ou inativo.',

            'is_allowed_sell_by_fiat.required' => 'A Habilidade de compra do nível deve ser informada.',
            'is_allowed_sell_by_fiat.boolean' => 'A Habilidade de compra do nível deve ser ativo ou inativo.'
        ];
    }
}
