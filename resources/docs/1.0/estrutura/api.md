# Projeto liquidex-api

---

- [Git](#section-1)
- [Instalação](#section-2)
- [Estrutura](#section-3)
- [Arquivos Importantes](#section-4)

<a name="section-1"></a>
## Git

O projeto deve ser clonado do seguinte caminho:

* Bitbcket: `https://bitbucket.org/navi-inf/liquidex-api/src/master/`

---
<a name="section-2"></a>
## Instalação

Após clonar o projeto é necessário realizar os passos abaixo para completar a instalação:

1. Ir ao diretório do projeto, normalmente: `cd liquidex-api` 
2. duplicar o arquivo `.env.example` e renomear para `.env`
3. preencher as variáveis de ambiente dentro do arquivo `.env` :
    * `APP_URL`: deve ser atribuído o endpoint raiz da aplicação (caso user o servidor embutido do laravel : `http://localhost:8000`)
    * `FRONT_URL` : deve ser atribuído o endereço raiz do front-end do cliente (caso use npm run dev : `http://localhost:8080`)
    ***
    * `DB_HOST`: deve ser atribuído o host do banco de dados (padrão `localhost`) 
    * `DB_PORT`: deve ser atribuída a porta do banco de dados (padrão `3306`)
    * `DB_DATABASE`: deve ser atribuído o nome do banco de dados (padrão `liquidex_api`)
    * `DB_USERNAME`: deve ser atribuído o usuario do banco de dados (padrão `homestead`)
    * `DB_PASSWORD`: deve ser atribuída a senha do usuario do banco de dados (padrão `secret`)
    ***
    * `MAIL_DRIVER`: deve atribuído o driver do provedor de emails (padrão `smtp`)
    * `MAIL_HOST`: deve atribuído o host do provedor de emails (padrão `smtp.mailtrap.io`)
    * `MAIL_PORT`: deve atribuída a porta do provedor de emails (padrão `2525`)
    * `MAIL_USERNAME`: deve atribuído o usuario do provedor de emails 
    * `MAIL_PASSWORD`: deve atribuída a senha do provedor de emails
    * `MAIL_ENCRYPTION`: deve atribuído do provedor de emails (padrão `tls`)
    ***
    `PASSPORT_LOGIN_ENDPOINT`: deve ser atribuído o endpointe de login do oauth (padrão: `http://liquidex.api/oauth/token`, substitua `http://liquidex.api` pelo seu host de desenvolvimento)
    
> {info} É recomendado criar uma conta de testes de email em: https://mailtrap.io/signin e preencher os dados `MAIL_USERNAME` e `MAIL_ENCRYPTION` conforme gerado na aplicação do mailtrap       
    
4. rodar comando de instalação das dependencias `composer i`, caso algum erro seja gerado, verifique os requerimentos de extensões do php conforme mostrado em seu terminal    
5. rodar comando para gerar uma chave de encryptação da aplicação: `php artisan key:generate`
6. rodar comando de migração: `php artisan migrate --seed`
7. rodar comando para gerar as chaves de segurança do passport: `php artisan passport:install`, serão mostradas chaves e identifcadores na tela, copie esses valores e cole nas variáveis do `.env`:
    * `PASSPORT_CLIENT_ID`: id gerado no comando acima
    * `PASSPORT_CLIENT_SECRET`: secret gerado no comando acima
7. rodar comando para gerar as chaves de segurança de login do admin do passport: `php artisan passport:client`, nomeie a chave como desejar, serão mostradas chaves e identifcadores na tela, copie esses valores e cole nas variáveis do `.env`:
    * `PASSPORT_ADMIN_ID`: id gerado no comando acima
    * `PASSPORT_ADMIN_SECRET`: secret gerado no comando acima
8. peça ao administrador da Api de Dados da Navi para gerar um acesso de testes e preencha esses valores nas variáveis do `.env`:
    * `NAVI_API_TOKEN`
    * `NAVI_API_CL`
    * `NAVI_API_URL`
9. rodar atualização do autoload: `composer dump`
10. (opcional) se optar por usar o servidor embutido do laravel, rode o comando: `php artisan serv`


---
<a name="section-3"></a>
## Estrutura

Estrutura de pastas do projeto

* Raíz do Projeto: `liquidex-api/`

![image](/images/docs/estrutura/api_src.png)

* Controllers: `liquidex-api/app/Http/Controllers`

![image](/images/docs/estrutura/api_controllers.png)

* Models: `liquidex-api/app/Models`

![image](/images/docs/estrutura/api_models.png)

* Services: `liquidex-api/app/Services`

![image](/images/docs/estrutura/api_services.png)

* database: `liquidex-api/database`

![image](/images/docs/estrutura/api_database.png)

* Routes: `liquidex-api/routes`

![image](/images/docs/estrutura/api_routes.png)

---
<a name="section-4"></a>
## Arquivos Importantes

Os arquivos listados abaixo tem extrema importancia para o funcionamento do projeto, é necessário ficar atento à suas configurações

* `.env` : fica na raíz do projeto e é essencial para o funcionamento do projeto e contém informações do ambiente de desenvolvimento
* `app/Console/Kernel.php` : neste arquivo são configurados os comandos cron da aplicação
* `app/Http/Kernel.php` : neste arquivo são configuradas as middlewares da aplicação
* `config/services.php` : neste arquivo são configuradas os servições externos acessados pela aplicação, as variáveis do .env devem ser capturadas aqui e atribuidas a novas chaves acessadas apartir deste arquivo pela aplicação
* `routes/api.php` : neste arquivo são todas as rotas de acesso da aplicação cliente
* `routes/admin.php` : neste arquivo são todas as rotas de acesso da aplicação admin

