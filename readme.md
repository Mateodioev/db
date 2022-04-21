# Easy sql-connection
[![CodeFactor](https://www.codefactor.io/repository/github/mateodioev/db/badge)](https://www.codefactor.io/repository/github/mateodioev/db)

## Installation

Github:
```bash
git clone https://github.com/Mateodioev/db
cd db
composer install
```

Composer:
```bash
composer require mateodioev/db
```


## Usage

Set database data
```php
use Mateodioev\Db\Connection;

Connection::Prepare('DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS');
# or 
$dir = 'path/to/.env/file';
Connection::PrepareFromEnv($dir);
```

Execute querys
```php
use Mateodioev\Db\Query;
$db = new Query();

// Return one afect row
$db->Exec('SELECT * FROM users'); // Simple sql query
$db->Exec('SELECT * FROM users WHERE id = :id', [':id' => 'random_id']); // With params

// Return all afect rows
$db->GetAll('SELECT * FROM users'); // Simple sql query
$db->GetAll('SELECT * FROM users WHERE id = :id', [':id' => 'random_id']); // With params
```


## Exceptions

- `Connection::PrepareFromEnv($dir);` If dir not fund or not readable throw exception

- `$db->Exec($sql_query)` or `$db->GetAll($sql_query)` If sql query not valid or invalid credentials throw exception
