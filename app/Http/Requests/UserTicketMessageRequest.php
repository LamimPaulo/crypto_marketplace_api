<?php

namespace App\Http\Requests;

use App\Enum\EnumUserTicketsStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserTicketMessageRequest extends FormRequest
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
            'user_ticket_id' => 'required|exists:user_tickets,id',
            'message' => 'required',
            'file' => 'nullable|file|max:1024|mimes:jpeg,png,pdf',
            'status' => [
                'nullable',
                Rule::in(array_keys(EnumUserTicketsStatus::STATUS))
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_ticket_id.required' => "É necessário informar o ticket da mensagem.",
            'user_ticket_id.exists' => "O ticket informado não existe.",
            'status.in' => "A situação informado é inválida.",
            'message.required' => "É necessário enviar uma mensagem.",
            'file.mimes' => trans('validation.deposit.file.mimes'),
            'file.max' => trans('validation.deposit.file.max'),
        ];
    }
}
