# navi-ico

### Build Setup

``` bash
# 1 - install dependencies
composer update
npm i

# 2 - Edit `.env` e configure a conex�o com o Banco de Dados 

# 3 - Migrate Tables 
php artisan migrate
php artisan db:seed

# 4 - Generate Key gen
php artisan key:gen

# Run application
php artisan serve
```

For a detailed explanation on how things work, check out the [laravel docs](https://laravel.com/docs/5.7/installation).