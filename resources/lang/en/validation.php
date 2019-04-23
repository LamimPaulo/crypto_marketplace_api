<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
    'exception' => [
        'message' => 'Os dados fornecidos são inválidos.'
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */
    'token' => [
        'code_required' => 'You must send the token to take action.',
        'action_required' => 'You must inform the action to be verified.',
        'action_type' => 'Action does not match.'
    ],
    'pin' => [
        'required' => 'You must complete your PIN to take action.',
        'min'       => 'Your PIN must contain 4 characters.',
        'max'       => 'Your PIN must contain 4 characters.',
        'confirmed' => 'The confirmation must match the PIN.'
    ],
    'convert' => [
        'amount_required'   => 'The amount is required.',
        'amount_numeric'    => 'The quantity must be a valid number (use only points and numbers).',
        'base_required'     => 'Base currency is required.',
        'quote_required'    => 'The quote currency is required.',
        'base_different'    => 'Base currency should be different from quotation.',
        'quote_different'   => 'The quote currency must be different from the base.',
    ],
    'deposit' => [
        'amount_required'               => 'Value is required.',
        'system_account_id_required'    => 'It is mandatory to choose an account for submission.',
        'system_account_id_exists'      => 'The selected account is invalid.',
        'file_mimes'                    => 'You must send the proof in jpeg, png or pdf format.',
        'file_max'                      => 'O tamanho do arquivo não pode ser maior que 1MB.',
        'file_required'                 => 'The proof is required.',
    ],
    'documents' => [
        'file_mimes'                => 'You must provide documents in jpeg, png or pdf format.',
        'file_max'                  => 'The file size can not be larger than 1MB.',
        'document_type_id_required' => 'Document type is required.',
        'document_type_id_exists'   => 'The document type is invalid.',
        'reject' => [
            'user_email_required' => 'The User ID is required.',
            'user_email_exists'   => 'The selected user not exists.',
            'reason_required'     => 'You must enter the reason for disapproval.',
        ]
    ],
    'draft' => [
        'amount_required'           => 'Value is required.',
        'user_account_id_required'  => 'You must enter the destination account.',
        'user_account_id_exists'    => 'You must enter a valid destination account.',
        'cancel' => [
            'transaction_required' => 'Transaction identifier is required.',
            'transaction_exists'   => 'Transaction does not exist.',
        ]
    ],
    'fund' => [
        'quotes_required'   => 'Quotas are required.',
        'quotes_integer'    => 'The quotas amount must be an integer.',
        'fund_id_exists'    => 'The informed fund is invalid.',
        'fund_id_required'  => 'The fund must be informed.',
    ],
    'gateway_api' => [
        'payment_coin_required' => 'The payment type must be selected.',
        'payment_coin_in'       => 'The payment type selected is invalid.',
        'ip_ip'                 => 'The access IP must be valid.',
    ],
    'nanotech' => [
        'operation_type_required' => 'The type of operation is required.',
        'operation_type_in'       => 'The type of operation selected is invalid.',
        'type_required'           => 'The type of investment is required.',
        'type_exists'             => 'The investment type selected is invalid.',
        'amount_required'         => 'The desired quantity is required.',
        'amount_numeric'          => 'The quantity desired should be a valid value. (use only points and numbers)',
    ],
    'mining_buy_ths' => [
        'payment_required'      => 'The payment type must be selected.',
        'payment_in'            => 'The payment type selected is invalid.',
        'ths_quantity_required' => 'The desired amount should be reported.',
        'ths_quantity_int'      => 'The desired amount must be an integer value.',
    ],
    'order' => [
        'symbol_required'   => 'Specifying the currency pair is required.',
        'symbol_exists'     => 'The requested pair is not available or does not exist.',
        'side_required'     => 'It is mandatory to choose the order side (BUY / SELL).',
        'side_in'           => 'The order side can only be BUY or SELL.',
    ],
    'send_crypto' => [
        'address_required'  => 'The source wallet must be informed.',
        'address_exists'    => 'Informed source wallet is invalid.',
        'priority_required' => 'The priority of the transaction must be informed.',
        'toAddress_required'=> 'The destination address must be informed.',
        'amount_required'   => 'The value to send must be informed.',
        'amount_numeric'    => 'The value you send must be a valid number (use only points and numbers).',
    ],
    'transfer' => [
        'amount_required'   => 'Value is required.',
        'amount_numeric'    => 'The value must be valid. (use only points and numbers)',
        'email_required'    => 'Email is required.',
        'email_email'       => 'Email must be valid.',
        'email_exists'      => 'Email must be from an existing user.',
    ],
    'user_account' => [
        'bank_id_exists'    => 'The chosen bank is invalid.',
        'nickname_required' => 'Account nickname is required.',
        'email_email'       => 'Email is required for the type of account chosen.',
        'type_required'     => 'The account type must be selected.',
    ],
    'fav_account' => [
        'email_required'=> 'The email delivery is mandatory.',
        'email_exists'  => 'Email does not belong to any platform users.',
    ],
    'user_phone' => [
        'phone_required'=> 'The number is required.',
        'phone_min'     => 'The number must be a minimum of 11 digits (include ddd).',
        'phone_max'     => 'The number must have a maximum of 11 digits (include ddd).',
    ],
    'user' => [
        'document_required' => 'The ID number is required.',
        'document_unique'   => 'The number of ID is already in use.',
    ],
];
