<?php require __DIR__ . '/vendor/autoload.php';

use Mateodioev\Db\Connection;

$dsn = Connection::createDSN('localhost', 'myDbName');
$con = new Connection($dsn, 'root', '');

$query = $con->getQuery();

$res = $query->exec('SELECT :number', ['number' => 1]);
var_dump($res);