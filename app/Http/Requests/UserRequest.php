<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed cpf
 * @property mixed document
 */
class UserRequest extends FormRequest
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
        $this->request->set('document', preg_replace("/[^0-9]/", "", $this->request->get('document')));

        return [
            'document' => 'required|unique:users|min:11',
        ];
    }

    public function messages()
    {
        return [
            'document.required' => trans('validation.user.document_required'),
            'document.unique'   => trans('validation.user.document_unique'),
        ];
    }

}
