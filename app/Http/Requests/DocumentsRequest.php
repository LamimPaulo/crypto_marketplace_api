<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed document_type_id
 * @property mixed file
 */
class DocumentsRequest extends FormRequest
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
            'document_type_id'  => 'required|exists:document_types,id',
            'file'              => 'file|max:1024|mimes:jpeg,png,pdf'
        ];
    }

    public function messages()
    {
        return [
            'file.mimes'                => trans('validation.documents.file_mimes'),
            'file.max'                  => trans('validation.documents.file_max'),
            'document_type_id.required' => trans('validation.documents.document_type_id_required'),
            'document_type_id.exists'   => trans('validation.documents.document_type_id_exists'),
        ];
    }
}
