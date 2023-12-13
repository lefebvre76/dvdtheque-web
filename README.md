# DVDthèque

Application web premettant de gérer une dvdthèque

## Installation
L'application utilise la version 10 de Laravel. Un version dockerisée à partir de [Sail](https://laravel.com/docs/10.x/sail) a été mise en place.

```
cp .env.example .env
composer install
./vendor/bin/sail artisan migrate:fresh --seed 
```
puis aller sur la page [http://localhost/](http://localhost/)
