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

    'accepted'             => 'O campo :attribute deve ser aceito.',
    'active_url'           => 'O campo :attribute não é uma URL válida.',
    'after'                => 'O campo :attribute deve ser uma data posterior a :date.',
    'after_or_equal'       => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha'                => 'O campo :attribute só pode conter letras.',
    'alpha_dash'           => 'O campo :attribute só pode conter letras, números e traços.',
    'alpha_num'            => 'O campo :attribute só pode conter letras e números.',
    'array'                => 'O campo :attribute deve ser uma matriz.',
    'before'               => 'O campo :attribute deve ser uma data anterior :date.',
    'before_or_equal'      => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'between'              => [
        'numeric' => 'O campo :attribute deve ser entre :min e :max.',
        'file'    => 'O campo :attribute deve ser entre :min e :max kilobytes.',
        'string'  => 'O campo :attribute deve ser entre :min e :max caracteres.',
        'array'   => 'O campo :attribute deve ter entre :min e :max itens.',
    ],
    'boolean'              => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'O campo :attribute de confirmação não confere.',
    'date'                 => 'O campo :attribute não é uma data válida.',
    'date_equals'          => 'O campo :attribute deve ser igual ao campo :date.',
    'date_format'          => 'O campo :attribute não corresponde ao formato :format.',
    'different'            => 'Os campos :attribute e :other devem ser diferentes.',
    'digits'               => 'O campo :attribute deve ter :digits dígitos.',
    'digits_between'       => 'O campo :attribute deve ter entre :min e :max dígitos.',
    'dimensions'           => 'O campo :attribute tem dimensões de imagem inválidas.',
    'distinct'             => 'O campo :attribute campo tem um valor duplicado.',
    'email'                => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'exists'               => 'O campo :attribute selecionado é inválido.',
    'file'                 => 'O campo :attribute deve ser um arquivo.',
    'filled'               => 'O campo :attribute deve ter um valor.',
    'gt' => [
        'numeric' => 'O campo :attribute deve ser maior que :value.',
        'file' => 'O campo :attribute deve ser maior que :value kilobytes.',
        'string' => 'O campo :attribute deve ser maior que :value caracteres.',
        'array' => 'O campo :attribute deve conter mais que :value itens.',
    ],
    'gte' => [
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'file' => 'O campo :attribute deve ser maior ou igual a :value kilobytes.',
        'string' => 'O campo :attribute deve ser maior ou igual a :value caracteres.',
        'array' => 'O campo :attribute deve ter :value itens ou mais.',
    ],
    'image'                => 'O campo :attribute deve ser uma imagem.',
    'in'                   => 'O campo :attribute selecionado é inválido.',
    'in_array'             => 'O campo :attribute não existe em :other.',
    'integer'              => 'O campo :attribute deve ser um número inteiro.',
    'ip'                   => 'O campo :attribute deve ser um endereço de IP válido.',
    'ipv4'                 => 'O campo :attribute deve ser um endereço IPv4 válido.',
    'ipv6'                 => 'O campo :attribute deve ser um endereço IPv6 válido.',
    'json'                 => 'O campo :attribute deve ser uma string JSON válida.',
    'lt' => [
        'numeric'   => 'O campo :attribute deve ser menor que :value.',
        'file'      => 'O campo :attribute deve ser menor que :value kilobytes.',
        'string'    => 'O campo :attribute deve ter menos que :value caracteres.',
        'array'     => 'O campo :attribute deve menos que :value itens.',
    ],
    'lte' => [
        'numeric'   => 'O campo :attribute deve ser menor ou igual a :value.',
        'file'      => 'O campo :attribute deve ser menor ou igual a :value kilobytes.',
        'string'    => 'O campo :attribute deve ser menor ou igual a :value caracteres.',
        'array'     => 'O campo :attribute não pode ter mais que :value itens.',
    ],
    'max' => [
        'numeric' => 'O campo :attribute não pode ser superior a :max.',
        'file'    => 'O campo :attribute não pode ser superior a :max kilobytes.',
        'string'  => 'O campo :attribute não pode ser superior a :max caracteres.',
        'array'   => 'O campo :attribute não pode ter mais do que :max itens.',
    ],
    'mimes'         => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes'     => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'min'           => [
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'file'    => 'O campo :attribute deve ter pelo menos :min kilobytes.',
        'string'  => 'O campo :attribute deve ter pelo menos :min caracteres.',
        'array'   => 'O campo :attribute deve ter pelo menos :min itens.',
    ],
    'not_in'               => 'O campo :attribute selecionado é inválido.',
    'not_regex'            => 'O campo :attribute possui um formato inválido.',
    'numeric'              => 'O campo :attribute deve ser um número.',
    'present'              => 'O campo :attribute deve estar presente.',
    'regex'                => 'O campo :attribute tem um formato inválido.',
    'required'             => 'O campo :attribute é obrigatório.',
    'required_if'          => 'O campo :attribute é obrigatório quando :other for :value.',
    'required_unless'      => 'O campo :attribute é obrigatório exceto quando :other for :values.',
    'required_with'        => 'O campo :attribute é obrigatório quando :values está presente.',
    'required_with_all'    => 'O campo :attribute é obrigatório quando :values está presente.',
    'required_without'     => 'O campo :attribute é obrigatório quando :values não está presente.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum dos :values estão presentes.',
    'same'                 => 'Os campos :attribute e :other devem corresponder.',
    'size'                 => [
        'numeric' => 'O campo :attribute deve ser :size.',
        'file'    => 'O campo :attribute deve ser :size kilobytes.',
        'string'  => 'O campo :attribute deve ser :size caracteres.',
        'array'   => 'O campo :attribute deve conter :size itens.',
    ],
    'string'               => 'O campo :attribute deve ser uma string.',
    'timezone'             => 'O campo :attribute deve ser uma zona válida.',
    'unique'               => 'O campo :attribute já está sendo utilizado.',
    'uploaded'             => 'Ocorreu uma falha no upload do campo :attribute.',
    'url'                  => 'O campo :attribute tem um formato inválido.',
    'uuid' => 'O campor :attribute deve ser um UUID válido.',

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
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
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
        'code_required'     => 'Você deve preencher o código para efetuar a ação.',
        'action_required'   => 'Você deve informar a ação a ser verificada.',
        'action_type'       => 'Ação não corresponde.'
    ],
    'pin' => [
        'required'  => 'Você deve preencher seu PIN para efetuar a ação.',
        'min'       => 'Seu PIN deve conter 4 caracteres.',
        'max'       => 'Seu PIN deve conter 4 caracteres.',
        'confirmed' => 'A confirmação deve corresponder com o PIN.'
    ],
    'convert' => [
        'amount_required'   => 'A quantidade é obrigatória.',
        'amount_numeric'    => 'A quantidade deve ser um número válido (utilize apenas números e ponto).',
        'base_required'     => 'A moeda base é obrigatória.',
        'quote_required'    => 'A moeda de cotação é obrigatória.',
        'base_different'    => 'A moeda base deve ser diferente da de cotação.',
        'quote_different'   => 'A moeda de cotação deve ser diferente da base.',
    ],
    'deposit' => [
        'amount_required'               => 'O valor é obrigatório.',
        'system_account_id_required'    => 'É obrigatória a escolha de uma conta para envio.',
        'system_account_id_exists'      => 'A conta selecionada é inválida.',
        'file_mimes'                    => 'Você deve enviar o comprovante no formato jpeg, png ou pdf obrigatoriamente.',
        'file_max'                      => 'O tamanho do arquivo não pode ser maior que 1MB.',
        'file_required'                 => 'O comprovante é obrigatório.',
    ],
    'documents' => [
        'file_mimes'                => 'Você deve fornecer os documentos no formato jpeg, png ou pdf obrigatoriamente.',
        'file_max'                  => 'O tamanho do arquivo não pode ser maior que 1MB.',
        'document_type_id_required' => 'O tipo de documento é obrigatório.',
        'document_type_id_exists'   => 'O tipo de documento é inválido.',
        'reject' => [
            'user_email_required' => 'O identificador do usuário é obrigatório.',
            'user_email_exists'   => 'O usuário indicado não existe',
            'reason_required'     => 'Você deve informar o motivo da reprovação.',
        ]
    ],
    'draft' => [
        'amount_required'           => 'O valor é obrigatório.',
        'user_account_id_required'  => 'É necessário informar a conta de destino.',
        'user_account_id_exists'    => 'É necessário informar uma conta de destino válida.',
        'cancel' => [
            'transaction_required' => 'O identificador da transação é obrigatório.',
            'transaction_exists'   => 'A transação não existe',
        ]
    ],
    'fund' => [
        'quotes_required'   => 'A quantidade de cotas é obrigatória.',
        'quotes_integer'    => 'A quantidade de cotas deve ser um número inteiro.',
        'fund_id_exists'    => 'O fundo informado é inválido.',
        'fund_id_required'  => 'O fundo deve ser informado.',
    ],
    'gateway_api' => [
        'payment_coin_required' => 'O tipo de pagamento deve ser selecionado.',
        'payment_coin_in'       => 'O tipo de pagamento selecionado é inválido.',
        'ip_ip'                 => 'O IP de acesso deve ser válido.',
    ],
    'nanotech' => [
        'operation_type_required' => 'O tipo de operação é obrigatório.',
        'operation_type_in'       => 'O tipo de operação selecionado é inválido.',
        'type_required'           => 'O tipo de investimento é obrigatório.',
        'type_exists'             => 'O tipo de investimento selecionado é inválido.',
        'amount_required'         => 'O quantidade desejada é obrigatória.',
        'amount_numeric'          => 'A quantidade desejada deve ser um valor válido. Utilize somente números e ponto.',
    ],
    'mining_buy_ths' => [
        'payment_required'      => 'O tipo de pagamento deve ser selecionado.',
        'payment_in'            => 'O tipo de pagamento selecionado é inválido.',
        'ths_quantity_required' => 'A quantidade desejada deve ser informada.',
        'ths_quantity_int'      => 'O quantidade desejada deve ser um valor inteiro.',
    ],
    'order' => [
        'symbol_required'   => 'A especificação do par de moedas é obrigatória.',
        'symbol_exists'     => 'O par requisitado não está disponível ou não existe.',
        'side_required'     => 'É obrigatória a escolha do lado da ordem (BUY/SELL).',
        'side_in'           => 'O lado da ordem só pode ser BUY ou SELL.',
    ],
    'send_crypto' => [
        'address_required'  => 'A carteira de Origem deve ser informada.',
        'address_exists'    => 'A carteira de Origem informada é inválida.',
        'priority_required' => 'A prioridade da transação deve ser informada.',
        'toAddress_required'=> 'O endereço destino deve ser informado.',
        'amount_required'   => 'O valor a enviar deve ser informado.',
        'amount_numeric'    => 'O valor enviado deve ser um número válido (utilize somente pontos e números).',
    ],
    'transfer' => [
        'amount_required'   => 'O valor é obrigatório.',
        'amount_numeric'    => 'O valor deve ser válido.',
        'email_required'    => 'O Email é obrigatório.',
        'email_email'       => 'O Email deve ser válido.',
        'email_exists'      => 'O Email deve ser de um usuário existente.',
    ],
    'user_account' => [
        'bank_id_exists'    => 'O banco escolhido é inválido.',
        'nickname_required' => 'O apelido da conta é obrigatório.',
        'email_email'       => 'O email é obrigatório para o tipo de conta escolhido.',
        'type_required'     => 'O tipo de conta deve ser selecionado.',
    ],
    'fav_account' => [
        'email_required'=> 'É obrigatório o fornecimento do email.',
        'email_exists'  => 'O email não pertence a nenhum usuário da plataforma.',
    ],
    'user_phone' => [
        'phone_required'=> 'O número é obrigatório.',
        'phone_min'     => 'O número deve ter o mínimo de 11 dígitos (incluir ddd).',
        'phone_max'     => 'O número deve ter o máximo de 11 dígitos (incluir ddd).',
    ],
    'user' => [
        'document_required' => 'O número do cpf é obrigatório.',
        'document_unique'   => 'O número de cpf já está em uso.',
    ],
];
