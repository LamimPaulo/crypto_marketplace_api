# Projeto liquidex-front

---

- [Git](#section-1)
- [Instalação](#section-2)
- [Estrutura](#section-3)
- [Arquivos Importantes](#section-4)


<a name="section-1"></a>
## Git

O projeto deve ser clonado do seguinte caminho:

* Bitbcket: `https://bitbucket.org/navi-inf/liquidex-front/src/master/`

---
<a name="section-2"></a>
## Instalação

Após clonar o projeto é necessário realizar os passos abaixo para completar a instalação:

* Ir ao diretório do projeto, normalmente: `cd liquidex-front` 
* criar um arquivo Env: `touch .env` ou `sudo nano .env`
* criar variaveis de ambiente dentro do arquivo `.env` :
    * `API_ROOT` : deve ser definido o endpoint principal da sua api local
    * `INVISIBLE_RECAPTCHA_KEY` : chave de integração de recaptcha do google
    * `NODE_ENV=local` : deve ser definido o ambiente de trabalho atual
* rodar comando de instalação das dependencias `npm i`    
* rodar comando para subir o server local de desenvolvimento `npm run dev`    

Se tudo estiver correto será apresentada a seguinte mensagem no terminal:

![image](/images/docs/estrutura/npm_run_dev.png)


---
<a name="section-3"></a>
## Estrutura

Estrutura de pastas do projeto

* Raíz do Projeto: `liquidex-front/`

![image](/images/docs/estrutura/front_root.png)

* Pasta src/components: `liquidex-front/src/components`

Esta é a pasta onde ficam todos os componentes do projeto, e é a pasta mais utilizada.

![image](/images/docs/estrutura/front_src.png)

---
<a name="section-4"></a>
## Arquivos Importantes

Os arquivos listados abaixo tem extrema importancia para o funcionamento do projeto, é necessário ficar atento à suas configurações

* `.env` : fica na raíz do projeto e é essencial para o funcionamento do projeto
* `index.html` : fica na raíz do projeto, aqui são carregados plugins externos ao projeto como o script que carrega o chat do suporte e o script do google recaptcha
* `src/App.vue` : componente default do projeto
* `src/main.js` : arquivo principal de configuração do projeto, onde todo o projeto vuejs é montado:
    * aqui componentes globais são importados
    * importadas definições de rotas
    * definidas metas de acesso
    * definidos filtros globais vuejs
    * definido tratamento de erros global         
* `src/routes.js` : arquivo contento todas as rotas de acesso aos componentes principais do front-end
* `src/store/api.js` : arquivo contendo o acesso a todos os endpoints da api, as funções definidas aqui ligam o front-end com a aplicação back-end por meio de api, utilizando axios
