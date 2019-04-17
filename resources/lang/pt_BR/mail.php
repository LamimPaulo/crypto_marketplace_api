<?php

return [
    'deposit' => [
        'reject' => [
            'subject' => 'Confirmação de Depósito',
            'main_message' => 'Ocorreu o seguinte erro na verificação do seu depósito:',
            'message' => 'Por favor acesse a plataforma e realize o processo de depósito novamente, será necessário reenviar o comprovante.',
        ]
    ],
    'documents' => [
        'reject' => [
            'subject' => 'Validação de Documentos',
            'main_message' => 'Ocorreu o seguinte erro na verificação de seus documentos:',
            'message' => 'Por favor acesse a plataforma e realize o processo de envio novamente caso necessário.',
        ]
    ],
    'draft' => [
        'reject' => [
            'subject' => 'Confirmação de Saque',
            'main_message' => 'Ocorreu o seguinte erro na verificação do seu saque:',
            'message' => 'Por favor acesse a plataforma e realize o processo de saque novamente, caso necessário.',
        ]
    ],
    'transaction' => [
        'reject' => [
            'subject' => 'Confirmação de Transação',
            'main_message' => 'Ocorreu o seguinte erro na verificação da sua transação:',
            'message' => 'Por favor acesse a plataforma e realize a transação novamente.',
        ]
    ],
    'mail_under_analysis' => [
        'subject' => 'Conta Sob Análise',
        'info' => 'Sua conta encontra-se sob análise da nossa equipe. Você será notificado(a) no termino da verificação.<br><br>Caso deseje acelerar o processo, favor entrar em contato com nossa equipe.<br>',
    ],
    'mail_verify' => [
        'subject' => 'Confirmação de Cadastro',
        'title' => 'Verificação de Email',
        'button' => 'Clique para confirmar seu cadastro',
        'info' => 'Seu email registrado é: <strong>:email</strong> , ele também poderá ser usado para logar na sua conta. Além disso seu número de <strong>telefone e username</strong> cadastrados também servirão como método de login.<br><br>Por favor clique no botão abaixo para confirmar sua identidade e obter acesso à plataforma.<br>',
        'info_2' => 'Cadastro gerado em',
        'info_3' => 'Você está recebendo este email para garantir que a solicitação de cadastro é verdadeira.'
    ],
    'notify_login' => [
        'subject' => 'Novo Login Efetuado',
        'title' => 'Alerta de Segurança',
        'info' => 'O sistema detectou um acesso à sua conta:',
        'access' => 'Acesso em',
        'source' => 'Origem',
        'info_2' => 'Você está recebendo este e-mail para garantir que foi você.',
        'info_3' => '- Verifique sempre se o e-mail foi mesmo enviado através de um endereço válido da Liquidex. Nós nunca iremos enviar um e-mail pedindo a sua senha ou qualquer informação pessoal.',
        'info_4' => '- Não acesse sites suspeitos. Se você acessar a sua conta através de uma rede Wi-Fi pública, é altamente recomendável o uso de uma VPN.',
        'info_5' => 'Está é uma mensagem automática, por favor não responda. Se você não fez esta solicitação ou não é nosso cliente, entre em contato imediatamente com a Liquidex.',
    ],
    'password_change' => [
        'subject' => 'Solicitação de Troca de Senha',
        'title' => 'Troca de Senha',
        'info' => 'Você requisitou uma troca de senha. Clique no botão abaixo para continuar, se você não requisitou essa troca, apenas ignore este email.',
        'button' => 'Mudar minha senha',
        'check' => 'Você está recebendo este email para garantir que a solicitação é verdadeira.'
    ],
    'hello' => 'Olá',
    'auto_message' => 'Está é uma mensagem automática, por favor não responda.',
    'rights' => 'Direitos Reservados.',


];