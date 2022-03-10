<?php require __DIR__ . '/vendor/autoload.php';

use Mateodioev\Db\Connection;
use Mateodioev\Db\Query;

// use Mateodioev\Db\{Connection, Query}; # PHP 8

// Connection::Prepare('host', 'port', 'dbname', 'user', 'pass');
try {
  Connection::PrepareFromEnv(__DIR__); # Load from .env file
  $db = new Query;

  var_dump($db->Exec('SELECT 1+2+3'));
  var_dump($db->GetAll('SELECT :nums', ['nums' => '1+2+3']));
  var_dump($db->Ping()); // Verify if the connection is still alive

  var_dump($db);
  
} catch (Exception $e) {
  die($e->getMessage());
}
