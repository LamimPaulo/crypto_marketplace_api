# Models

---

- [Resumo](#section-0)
- [Users](#section-1)
- [Users](#section-1)

<a name="section-0"></a>
## Resumo
Abaixo especificações dos model utilizados para registro e autenticação na plataforma:

<a name="section-1"></a>
## Users


### users
* Descrição: tabela de usuários do sistema


| Coluna | Desc | Tipo | Param1 | Param2 | Required
| :      | :-   |  :   | :      | :      |
| id | ... | char	| 36	| 0	| True
| name | ... | varchar	| 191	| 0	| False
| username | ... | varchar	| 191	| 0	| True
| email | ... | varchar	| 191	| 0	| True
| email_verified_at | ... | timestamp	| 0	| 0	| False
| password | ... | varchar	| 191	| 0	| True
| is_admin | ... | tinyint	| 1	| 0	| True
| phone | ... | varchar	| 191	| 0	| False
| phone_verified_at | ... | timestamp	| 0	| 0	| False
| document | ... | varchar	| 50	| 0	| False
| document_verified | ... | tinyint	| 1	| 0	| True
| birthdate | ... | date	| 0	| 0	| False
| mothers_name | ... | text	| 0	| 0	| False
| gender | ... | varchar	| 1	| 0	| True
| remember_token | ... | varchar	| 100	| 0	| False
| created_at | ... | timestamp	| 0	| 0	| False
| updated_at | ... | timestamp	| 0	| 0	| False
| country_id | ... | int	| 10	| 0	| True
| user_level_id | ... | int	| 10	| 0	| True
| pin | ... | varchar	| 191	| 0	| False
| pin_filled | ... | tinyint	| 1	| 0	| True
| is_google2fa_active | ... | tinyint	| 1	| 0	| True
| google2fa_secret | ... | varchar	| 191	| 0	| False
| is_under_analysis | ... | tinyint	| 1	| 0	| True
| api_key | ... | char	| 36	| 0	| False
| zip_code | ... | varchar	| 191	| 0	| False
| country | ... | varchar	| 191	| 0	| False
| state | ... | varchar	| 191	| 0	| False
| city | ... | varchar	| 191	| 0	| False
| district | ... | varchar	| 191	| 0	| False
| address | ... | varchar	| 191	| 0	| False
| number | ... | varchar	| 191	| 0	| False
| complement | ... | text	| 0	| 0	| False
| is_dev | ... | tinyint	| 1	| 0	| True
| is_dev | ... | tinyint	| 1	| 0	| True
