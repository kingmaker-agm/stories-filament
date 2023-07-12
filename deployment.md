Deployment
==========

This document describes how to deploy the application to a production environment.
The Deployment is usually done through **Git** mechanism.

Steps
-----
1. Pull the latest changes from the **master** branch.
    ```bash
   git pull
   ```
2. Run the following command to install the dependencies:
    ```bash
    composer install --no-dev --optimize-autoloader
    ```
3. Run the following command to update the database schema:
    ```bash
   php artisan migrate --force
    ```
   
Server Requirements
-------------------

### Software Components

1. PHP >= 8.1
2. PHP Extension Modules
    - BCMath PHP Extension
    - BZip2 PHP Extension
    - Calendar PHP Extension
    - Ctype PHP Extension
    - Curl PHP Extension
    - Date PHP Extension
    - DOM PHP Extension
    - Exif PHP Extension
    - FFI PHP Extension
    - Fileinfo PHP Extension
    - Filter PHP Extension
    - FTP PHP Extension
    - GD PHP Extension
    - GMP PHP Extension
    - Hash PHP Extension
    - Iconv PHP Extension
    - Intl PHP Extension
    - JSON PHP Extension
    - LibXML PHP Extension
    - Mbstring PHP Extension
    - MySQLi PHP Extension
    - OpenSSL PHP Extension
    - PDO PHP Extension
    - PDO_PgSQL PHP Extension
    - PDO_MySQL PHP Extension
    - PDO_SQLite PHP Extension
    - Phar PHP Extension
    - Phar PHP Extension
    - Readline PHP Extension
    - Redis PHP Extension
    - Reflection PHP Extension
    - Session PHP Extension
    - SimpleXML PHP Extension
    - Soap PHP Extension
    - Socket PHP Extension
    - Sodium PHP Extension
    - SPL PHP Extension
    - Tokenizer PHP Extension
    - XML PHP Extension
    - XMLReader PHP Extension
    - XMLWriter PHP Extension
    - XSL PHP Extension
    - Zip PHP Extension
    - Zlib PHP Extension
3. MySQL >= 5.7
4. Apache >= 2.4
5. Cron
   ```text
    * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
    ```
6. Git


### Configuration Requirements

1. SMTP Relay Server
2. S3 Compatible bucket for Backups
