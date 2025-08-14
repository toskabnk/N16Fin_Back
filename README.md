## Requirements
- PHP 8.2
- Composer
- Apache2 o Nginx
- MongoDB

## Installation

Clona el repositorio en una carpeta con el nombre del proyecto:
```shell
git clone https://github.com/toskabnk/N16Fin_Back.git
```
O en la carpeta actual con:
```shell
git clone https://github.com/toskabnk/N16Fin_Back.git .
```

Rename the file `.env.example` to `.env` and modify the database user values as well as the name. Modify any other values you deem necessary

Install the dependencies with:
```shell
composer install
```

Once the dependencies are installed, generate a new application key by executing:
```shell
php artisan key:generate
```

Create migrations in the database:
```shell
php artisan migrate
```

## Run

Run the project in a local environment with:
```shell
php artisan serve
```