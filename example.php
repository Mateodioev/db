<?php require __DIR__ . '/vendor/autoload.php';

use Mateodioev\Db\Connection;
use Mateodioev\Db\Query;

// use Mateodioev\Db\{Query, Query}; # PHP 8

try {
  Connection::PrepareFromEnv(__DIR__);
  $db = new Query();
  $res = $db->Exec('SELECT * FROM users');
  print_r($res);

} catch (\Exception $e) {
  
  die($e->getMessage());
}


try {
  Connection::PrepareFromEnv(__DIR__);
  $db = new Query();
  $res = $db->GetAll('SELECT * FROM users WHERE role = :role', ['role' => 'admin']);
  print_r($res);

} catch (\Exception $e) {
  
  die($e->getMessage());
}