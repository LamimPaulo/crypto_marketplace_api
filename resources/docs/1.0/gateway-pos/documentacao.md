# Documentação Gateway POS

---

- [URL Base](#section-1)
- [Requisitando Pagamento](#section-2)
- [Verificando Situação do Pagamento](#section-3)
- [Situações do Pagamento](#section-4)
- [Verificando validade da chave de integração](#section-5)

<a name="section-1"></a>
## URL Base

Todas as requisições devem utilizar a url base
* Caminho: `https://api.liquidex.com.br/api/payments`

___

<a name="section-2"></a>
## Requisitando Pagamento

Para requisitar um novo pagamento no gateway POS deve ser feita uma requisição para o endpoint:

* Endpoint: `/new`
* Método: `POST`
* Content-Type: `multipart/form-data` ou `application/json`
* Response: `application/json`

### Parametros necessários para geração do pagamento:

| Paramentro    | Descrição                                      | Tipo      | Local  | Obrigatório
| :             | :-                                             | :         | :      | :
| Authorization | secret gerado no sistema                       | string    | header | X
| api_key       | Api Key gerada no sistema                      | string    | body   | X
| amount        | valor do pagamento (em R$) (exemplo: "20.55")  | String    | body   | X
| abbr          | crypto do pagamento                            | string    | body   | X

### Response:

> {success} [200] Response success

```{
{
  "status": "success",
  "payment": "36V4i61Yoh8nTqxNWpSjD2B6fWnnr41nBG",
  "amount": "0.00057007",
  "coin": "BTC",
  "fiat_amount": 20
}
```

___

> {danger} [400 ou 422] Response error: 'Mensagem de erro variável'


___
___


<a name="section-3"></a>
## Verificando Situação do Pagamento

Para verificar a situação de um pagament no gateway POS deve ser feita uma requisição para o endpoint:

* Endpoint: `/status/{address}`
* Método: `POST`
* Content-Type: `multipart/form-data` ou `application/json`
* Response: `application/json`

### Parametros necessários para recuperação do pagamento:

| Paramentro    | Descrição                                       | Tipo     | Local  | Obrigatório
| :             | :-                                              | :        | :      | :
| Authorization | secret gerado no sistema                        | string   | header | X
| api_key       | Api Key gerada no sistema                       | string   | body   | X
| address       | `payment` devolvido ao gerar um novo pagamento  | string   | url   | X

### Response:

> {success} [200] Response success

```{
{
  "status": "success",
  "payment": {
    "status_name": "EXPIRADO",
    "status": 6,
    "amount": "0.00057007",
    "address": "36V4i61Yoh8nTqxNWpSjD2B6fWnnr41nBG",
    "tx": "ad3552b1-41b7-4bca-9539-8bcc42f0ed5e",
    "created": "2019-06-01T19:15:28.000000Z",
    "confirmations": 0
  }
}
```

___

> {danger} [400 ou 422] Response error: 'Mensagem de erro variável'

___

> {info} O pagamento deve ser confirmado no terminal POS quando o payment.status obtido na recuperação do pagamento for igual a 1 (PAGO)

___
___


<a name="section-4"></a>
## Situações de Pagamento

Ao recuperar um pagamento no endpoint acima, varias situações podem ocorrer, abaixo estão listadas cada uma delas:

| Enum              | Identificador    | Descrição             | Ação  
| :                 | :-               | :                     | :
| PAID              | 1                | PAGO                  | Confirmar Pagamento no POS e imprimir comprovante da transação
| SEEN              | 2                | VISTO                 | 
| CONFIRMED         | 3                | CONFIRMADO            | 
| OVERPAID          | 4                | PAGO ACIMA            | 
| UNDERPAIDEXPIRED  | 5                | PAGO ABAIXO EXPIRADO  |           
| EXPIRED           | 6                | EXPIRADO              | Mostrar Informação e cancelar operação no POS
| DONE              | 7                | FEITO                 | Confirmar Pagamento no POS e imprimir comprovante da transação
| INIT              | 8                | INIT                  | 
| NEWW              | 9                | PENDENTE              | 
| ACTIVE            | 10               | ACTIVE                |    
| RECENT            | 11               | RECENT                |  
| UNDERPAID         | 12               | PAGO ABAIXO           |  

___

> {success} O pagamento deve ser confirmado no terminal POS quando o payment.status obtido na recuperação do pagamento for igual a 1 (PAGO)

___

> {info} Estes status podem ser verificados também na classe EnumGatewayStatus


___

<a name="section-5"></a>
## Verificando validade da chave de integração

Para verificar se a chave do gateway POS é válida deve ser feita uma requisição para o endpoint:

* Endpoint: `/check-key`
* Método: `POST`
* Content-Type: `multipart/form-data` ou `application/json`
* Response: `application/json`

### Parametros necessários para verificação:

| Paramentro    | Descrição                   | Tipo     | Local  | Obrigatório
| :             | :-                          | :        | :      | :
| api_key       | Api Key gerada no sistema   | string   | body   | X

### Response:

> {success} [200] Response success

```{
{
  "status": "success",
  "message": "Chave Válida"
}
```

___

> {danger} [400 ou 422] Response error


```{
{
  "status": "error",
  "message": "Chave Inválida"
}
```
