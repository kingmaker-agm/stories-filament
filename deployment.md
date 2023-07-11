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
   
