# Auth Cliente

---

- [Registro Cliente](#section-1)
- [Api Register](#section-2)

- [Login Cliente](#section-3)
- [Api Login](#section-4)

- [Logout Cliente](#section-5)
- [Api Logout](#section-6)

- [Confirmação de Conta](#section-7)

<a name="section-1"></a>
## Registro Cliente

O registro de cliente deve ser acessado pela rota `/register` ou `/signup`.

* Componente: `Register.vue`
* Caminho: `liquidex-front/src/components/auth/Register.vue`
* Dependências: `vue-recaptcha` `vue-phone-number-input`

![image](/images/docs/auth/register_cliente.png)

> {info} Deve ser sempre verificado se o icone do recaptcha está sendo apresentado no canto inferior direito (acima do botão de suporte)

___

<a name="section-2"></a>
## Api Register

O endpoint de registro de cliente deve ser chamado pelo componente `Register.vue`

### front-end
* Action: `register(context, credentials)`
* Endpoint: `API_ROOT/register`
* Local: `liquidex-front/src/store/api.js`

### back-end
* Autenticada: `Não`
* Local: `liquidex-api/routes/api.php`
* Método: `Route::post('/register', 'AuthController@register');`
* Repostas possíveis:

> {success} [200] Response success: 'Sua Conta foi criada com sucesso, enviamos um de confirmação para você.' 

___

> {danger} [422] Response error: 'Mensagem de erro variável' 

___

<a name="section-3"></a>
## Login Cliente

A autenticação é a primeira página apresentada quando acessada a rota raiz do front-end cliente.

### front-end
* Rotas: `/` `/login`
* Componente: `Login.vue`
* Caminho: `liquidex-front/src/components/auth/Login.vue`
* Dependências: `vue-recaptcha`

![image](/images/docs/auth/login_cliente.png)

> {info} Deve ser sempre verificado se o icone do recaptcha está sendo apresentado no canto inferior direito (acima do botão de suporte)

___

<a name="section-4"></a>
## Api Login

O endpoint de login do cliente deve ser chamado pelo componente `Login.vue`
### front-end
* Action: `retrieveToken(context, credentials)`
* Endpoint: `API_ROOT/login`
* Local: `liquidex-front/src/store/api.js`

### back-end
* Autenticada: `Não`
* Local: `liquidex-api/routes/api.php`
* Método: `Route::post('/login', 'AuthController@login')->middleware('throttle');`
* Repostas possíveis:

> {success} [200] Response success: é devolvido um token de autenticação que deve ser gravado na local storage do cliente e usado para futuras requisições 

___

> {danger} [400 ou 422] Response error: 'Mensagem de erro variável' 

___


<a name="section-5"></a>
## Logout Cliente

Quando o cliente deseja sair da plataforma ele deve ser redirecionado para a rota de logout e posteriormente para a rota de login

### front-end
* Rotas: `/logout`
* Componente: `Logout.vue`
* Caminho: `liquidex-front/src/components/auth/Logout.vue`

> {info} O token mantido na local storage deve ser excluído para segurança do cliente

___

<a name="section-6"></a>
## Api Logout

O endpoint de logout do cliente deve ser chamado pelo componente `Logout.vue`
### front-end
* Action: `destroyToken(context)`
* Endpoint: `API_ROOT/logout`
* Local: `liquidex-front/src/store/api.js`

### back-end
* Autenticada: `Bearer Token`
* Local: `liquidex-api/routes/api.php`
* Método: `Route::get('/logout', 'AuthController@logout')->middleware('auth:api');`
* Repostas possíveis:

> {success} [200] Response success: Você deslogou com sucesso. (os token de autenticação do usuários são removidos da tabela de autenticação do oauth) 

___

> {danger} [400 ou 422] Response error: 'Mensagem de erro variável' 

___

<a name="section-7"></a>
## Confirmação de Conta

Ao criar uma conta no sistema o usuário recebe um email de confirmação, sem esta confirmação o usuário não consegue acesso na plataforma, para tal ação são utilizados os recursos abaixo:

### back-end
* Endpoint: `API_ROOT/login`
* Autenticada: `Não`
* Local: `liquidex-api/routes/api.php`
* Método: `Route::post('/login', 'AuthController@login')->middleware('throttle');`
    * Função: `Mail::to($user->email)->send(new VerifyMail($user));`
    * Mailable Local: `liquidex-api/app/Mail/VerifyMail.php`
    * Mailable View Local: `liquidex-api/resources/views/emails/verifyUser.blade.php`
    
* Reposta:

![image](/images/docs/auth/confirmacao_cadastro.png)

O email acima contém o token de verificação do usuário. Os recursos abaixo são utilizados para confirmar a conta do cliente:

### back-end
* Endpoint: `APP_URL/verifyEmail/{token}`
* Autenticada: `Não`
* Local: `liquidex-api/routes/web.php`
* Método: `Route::get('/verifyEmail/{token}', 'AuthController@verifyUser');`

Após a confirmação dos dados nos recursos acima, o usuário é redirecionado para a página no front-end

### fornt-end
* Rota: `/register/verify`
* Componente: `VerifyEmail.vue`
* Caminho: `liquidex-front/src/components/auth/VerifyEmail.vue`
* Autenticada: `Não`
___