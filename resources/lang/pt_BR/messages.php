<?php

return [
    '2fa' => [
        'activated' => 'Google 2FA Ativada.',
        'already_activated' => 'Google 2FA já ativa.',
        'deactivated' => 'Google 2FA Desativada.',
        'invalid_code' => 'Código inválido ou expirado.',
        'invalid_secret' => 'O secret informado é inválido.',
        'not_activated' => 'Google 2FA não está ativada.',
    ],
    'auth' => [
        'access_denied' => 'Acesso Negado.',
        'already_have_active_key' => 'Você já possui uma chave ativa.',
        'email_not_found' => 'Email não encontrado em nosso banco de dados.',
        'email_sent' => 'Email enviado com sucesso, verifique sua caixa de entrada.',
        'id_could_not_be_verified' => 'Não foi possível verificar o cpf informado.',
        'invalid_client' => 'Cliente Inválido.',
        'invalid_code' => 'Código Inválido. Tente Novamente.',
        'invalid_key' => 'Chave Inválida',
        'valid_key' => 'Chave Válida',
        'invalid_pin' => 'Pin Inválido.',
        'invalid_token' => 'Token Inválido.',
        'password_change_success' => 'Senha atualizada com sucesso.',
        'pin_updated' => 'PIN atualizado com sucesso.',
        'telephone_number_verified' => 'Telefone Verificado com sucesso. (:number)',
        'telephone_verified' => 'Telefone Verificado com sucesso!',
        'telephone_update_error' => 'Erro ao atualizar número!',
        'token_email_invalid' => 'Token ou Email incorreto.',
        'unauthorized_ip' => 'IP não liberado.',
        'you_could_not_updated_your_id' => 'Você não pode atualizar seu CPF novamente.',
        'is_canceled' => 'Conta Cancelada.',
    ],
    'account' => [
        'created' => 'Conta criada com sucesso.',
        'deleted' => 'Conta excluída com sucesso.',
        'updated' => 'Conta atualizada com sucesso.',
        'not_found' => 'Nenhuma Conta encontrada.',

        'beneficiary_not_found' => 'Beneficiário não encontrado.',
        'beneficiary_already_registered' => 'O Beneficiário já encontra-se cadastrado.',
        'email_not_exists' => 'O email informado não existe.',
        'must_register_email_recepient_first' => 'Você deve cadastrar o email como beneficiário antes de efetuar a transação.',
        'must_confirm_account_to_require_transferences' => 'Você deve confirmar sua conta para solicitar transferências entre contas.',
        'you_could_not_be_recipient' => 'Você não pode se cadastrar como beneficiário.',
    ],
    'coin' => [
        'inactive' => 'A moeda solicitada encontra-se inativa.',
        'incompatible' => 'A moeda solicitada não corresponde com seu perfil.',
        'must_be_distinct' => 'As moedas devem ser diferentes.',
        'can_not_be_converted' => 'As moedas solicitadas não podem ser convertidas.',
        'not_compatible_with_investment' => 'O tipo de moeda é incompatível com o tipo de investimento.',
    ],
    'deposit' => [
        'sent' => 'Depósito enviado com sucesso.',
        'done' => 'Depósito confirmado.',
        'rejected' => 'Depósito rejeitado - :reason.',
        'already_pending' => 'Não é possível realizar a solicitação, já existe um depósito pendente.',
        'value_not_reached_min' => 'O valor do depósito não atingiu o mínimo.',
    ],
    'documents' => [
        'accept' => 'Documentação Aprovada.',
        'pending' => 'Documentos não enviados ou pendentes de aprovação.',
        'reject' => 'Documentos rejeitados - :reason',
        'sent_error' => 'Erro ao enviar documento!',
        'file_no_longer_available' => 'O arquivo não está disponível para este usuário.',
    ],
    'gateway' => [
        'address_generated' => 'Endereço gerado com sucesso.',
        'must_create_api_key' => 'Para solicitar pagamentos é necessária a criação de uma Api Key.',
        'not_elegible' => 'Você não possui permissão para utilização do Gateway de Pagamentos.',
        'payment_could_not_be_updated' => 'O pagamento não pode ser modificado.',
        'payment_expired' => 'O pagamento já foi processado ou expirou, não é possível completar a operação.',
        'payment_not_found' => 'Pagamento não encontrado. (TX não existe)',
        'payment_time_expired' => 'O pagamento não pode ser gerado, o tempo esgotou.',
        'submission_could_not_be_processed' => 'O Envio não pode ser processado, o pagamento do gateway e a moeda enviada não são compatíveis.',
    ],
    'general' => [
        'invalid_data' => 'Dados Inválidos.',
        'invalid_id' => 'Cpf inválido.',
        'invalid_operation_type' => 'Tipo de operação incorreto.',
        'level_up' => 'Subiu de Keycode!',
        'status_updated' => 'Status Atualizado.',
        'success' => 'Sucesso!',
    ],
    'products' => [
        'arbitrage' => 'Nanotech',
        'crypto_assets' => 'Crypto Ativos',
        'error_creating_investment' => 'Erro ao criar investimento.',
        'fund_not_acquired' => 'Você não possui cotas do Fundo de Investmento selecionado.',
        'hiring_success' => 'Contratação efetuada com sucesso.',
        'index_fund' => 'Fundo de Investmentos',
        'index_fund_hiring_success' => 'Fundo de Investmento adquirido com Sucesso!',
        'index_fund_sold_success' => 'Fundo de Investmento vendido com Sucesso!',
        'insuficient_investment_balance' => 'Saldo de investimento insuficiente para realizar saque.',
        'insuficient_profit' => 'Lucro insuficiente. O valor de investimento é maior que seu lucro disponível.',
        'insuficient_quotes' => 'Cotas insuficientes para realizar a operação.',
        'invalid_contract_method' => 'Não é possível realizar a contratação na forma escolhida.',
        'investment_success' => 'Investimento realizado com sucesso.',
        'minimum_purchase_value_not_reached' => 'O valor mínimo de compra não foi atingido. (:amount :abbr)',
        'mining' => 'Mineração',
        'not_allowed_sell_by_fiat' => 'Seu perfil não permite a venda de produtos por Moeda Fiat.',
        'not_allowed_buy_with_fiat' => 'Seu perfil não permite a compra de produtos com Moeda Fiat.',
        'ths_sold_out' => 'Não é possível contratar a quantidade requisitada. No momento possuímos apenas :remaining Th/s disponíveis. Tente contratar um número igual ou menor a este.',
    ],
    'transaction' => [
        'amount_must_be_grater_than_zero' => 'A quantidade deve ser maior que 0.',
        'conversion_success' => 'Conversão realizada com sucesso!',
        'success' => 'Transação aprovada com Sucesso!',
        'canceled' => 'Transação cancelada!',
        'crypto_sent' => 'Crypto Enviada.',
        'crypto_received' => 'Crypto Recebida.',
        'invalid' => 'Transação inválida.',
        'invalid_value_sent' => 'O valor enviado é inválido.',
        'invalid_value_request' => 'O valor solicitado é inválido.',
        'not_found' => 'Transação não encontrada.',
        'order_sent' => 'Ordem enviada com sucesso!',
        'reversed' => 'Transação estornada - :reason',
        'sent_blockchain' => 'Transação enviada para a blockchain.',
        'sent_success' => 'Envio realizado com sucesso!',
        'value_below_the_minimum' => 'Valor abaixo do minimo. (:amount)',
        'value_exceeds_balance' => 'Valor da transação acima do valor em carteira.',
        'value_exceeds_day_limits' => 'Valor da transação excede os limites diários.',
        'value_exceeds_level_limits' => 'Valor da transação excede o limite diário de acordo com seu keycode.',
        'value_must_be_greater_than_zero' => 'O valor deve ser maior que 0.',
    ],
    'wallet' => [
        'inactive' => 'A carteira solicitada encontra-se inativa.',
        'invalid_for_coin' => 'Não existe Carteira válida para a moeda requisitada.',
        'invalid' => 'Não há carteira válida para a requisição.',
        'insuficient_balance' => 'Saldo Insuficiente.',
        'insuficient_balances' => 'Saldos Insuficientes.',
    ],
    'withdrawal' => [
        'canceled_by_user' => 'Saque Cancelado pelo Usuário.',
        'canceled' => 'Saque Cancelado com sucesso.',
        'done' => 'Saque efetuado.',
        'day_off' => 'Não é possível solicitar saques no momento, fora de horário ou dia não permitido.',
        'processing' => 'Saque em processamento.',
        'success' => 'Saque solicitado com sucesso.',
        'requested' => 'Saque solicitado.',
        'reversed' => 'Saque estornado - :reason.',
        'already_pending' => 'Não é possível realizar a solicitação, já existe um pedido de saque pendente.',
        'must_confirm_account_to_require_draft' => 'Você deve confirmar sua conta para solicitar saques.',
    ],
];