
# Symfony API with JWT Authentication

This is a RESTful API built with Symfony framework that implements JWT (JSON Web Token) authentication.

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Symfony CLI (optional but recommended)

## Installation

1. Clone the repository:

git clone <repository-url>
cd <project-directory>

2. Install dependencies:

composer install

3. Configure your database in `.env`:

DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

4. Create database and run command for creating base user:

## Using make

make restore-database

## Alternative

php bin/console d:d:d --if-exists --force
php bin/console d:d:c
php bin/console d:s:c
php bin/console d:s:v
php bin/console app:create:user

5. Generate the JWT keys:

php bin/console lexik:jwt:generate-keypair
