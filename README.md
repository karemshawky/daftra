

**Project Installation Guide**
=====================================

**System Requirements**
-----------------------

* PHP 8.2
* MySQL
* Laravel 12
* Composer installed on your system

**Installation Steps**
----------------------

### Step 1: Clone the Repository

 Clone the project repository using the following command:

```bash
git clone https://github.com/karemshawky/daftra.git
```

### Step 2: Install Dependencies

 Navigate to the project directory and run the following command to install dependencies:

```bash
composer install
```

### Step 3: Set Environment Variables

 Create a copy of the `.env.example` file and rename it to `.env`.

```bash
cp .env.example .env
```

### Step 4: Generate Application Key

 Run the following command to generate the application key:

```bash
php artisan key:generate
```

### Step 5: Change some values of `.env` file:

```bash
APP_URL=<Base_URL>
```
### And for Database

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<DATABASE-NAME>
DB_USERNAME=<DATABASE-USER>
DB_PASSWORD=<DATABASE-PASSWORD>
```

### And for Cache
```bash
CACHE_STORE=array
```


### Step 6: Run Migrations

 Run the following command to execute the database migrations and seed:

```bash
php artisan migrate --seed
```

### Step 7: Start the Development Server

 Start the development server using the following command:

```bash
php artisan serve
```

### Step 8: Run unit tests

```bash
php artisan test
```

### Or run tests in Parallel

```bash
php artisan test --parallel
```

### Step 9: Run Static Analysis with Larastan

```bash
./vendor/bin/phpstan analyse
```

### Finally, to hit documentation page change the `<Base_URL>` by yours and visit:

```bash
<Base_URL>/docs/api/
```

### Import API Documentation and Environment in Postman

## Alternative
1. Import `Daftra.documentation.json` file to your postman application.
2. Import the `Daftra.environment.json` file into Postman, then edit the environment information from the sidebar's environment tab as needed.

## Credentials
```bash
email:admin@daftra.com
password:password@123
```