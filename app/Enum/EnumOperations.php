<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of EnumOperations
 *
 * @author vagner
 */
abstract class EnumOperations {
    
    const CRYPTO_BUY = 1; // Compra de crypto
    const CRYPTO_SELL = 2; // Venda de crypto
    const CRYPTO_SEND = 3; // Enviar bitcoins para uma carteira externa a plataforma
    const CRYPTO_REQUEST = 4; // Receber bitcoins externos
    const FIAT_WITHDRAW = 5; // Saque REAL
    const FIAT_REQUEST = 6; // Deposito REAL externos
    const GATEWAY_WITHDRAW = 7; // Pedidos de saque de um client para a plataforma
    const GATEWAY_PAID = 8; // Pedidos de saque de um client para a plataforma

    
    const OPERATIONS = [
        self::CRYPTO_BUY => 'Compra',
        self::CRYPTO_SELL => 'Venda',
        self::CRYPTO_SEND => 'Envio',
        self::CRYPTO_REQUEST => 'Recebida',
        self::FIAT_WITHDRAW => 'Saque',
        self::FIAT_REQUEST => 'Depósito',
        self::GATEWAY_WITHDRAW => 'Saque gateway',
        self::GATEWAY_PAID => 'Pagamento gateway',
    ];
}
