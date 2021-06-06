# 4rex 

## Table of Contents

- About 




## About

This is an exchange rate application which interacts with the fixer.io API to get the exchange rates and the convert of monetary between currencies.

## System requirement

- php 7.4 ^ / 8.0
- Laravel 8
- Required to get API key from Fixer.io
- Livewire
- Tailwind

## Setup

Git clone the project to you desired path

````php
git clone https://github.com/beevester/4rex24rex.git
````
Run the installation of the package via Composer:

```php
composer require beevester/4rex24rex
```
## Get API key

Go the fixer.io and you can get a free api key, but it has some limitation that you are not able to change the base currency and is default to EUR.

## Configuration

Add the necessary configs to the .env file.

```dotenv
DB_CONNECTION=Connection_type
DB_HOST={Host-name-for-DB}
DB_PORT={Port-for-the-DB-host}
DB_DATABASE={Your-chosen-database-here}
DB_USERNAME={Your-username-for-db}
DB_PASSWORD={Your-DB-password}

FIXER_API_KEY={Your-API-Key-Here}
FIXER_API_BASE_URL=http://data.fixer.io/api/
```




