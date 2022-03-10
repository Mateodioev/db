<?php 

namespace Mateodioev\Db;

use \PDO;

class Connection
{
  
  private static $host = '';
  private static $user = '';
  private static $password = '';
  private static $hostFrom = '';

  /**
   * PDO connection
   */
  public static $dsn;

  /**
   * @param string $host DB_HOST - Db host
   * @param string $user DB_USER Db - username
   * @param string $pass DB_PASSWORD - Db password
   */
  private static function From(string $host, string $user, string $pass):void
  {
    self::$host = $host;
    self::$user = $user;
    self::$password = $pass;
  }

  /**
   * Prepare data to connect to database
   *
   * @param string $ip DB_HOST IP or hostname of the database server
   * @param string $port DB_PORT
   * @param string $dbname DB_NAME
   * @param string $user DB_USER
   * @param string $pass DB_PASS
   */
  public static function Prepare(string $ip, string $port, string $dbname, string $user, string $pass)
  {
    self::$hostFrom = 'mysql:host=' . $ip . ';port=' . $port . ';dbname=' . $dbname;
    self::From(self::$hostFrom, $user, $pass);
  }

  /**
   * Prepare data to connect to database from .env file
   * 
   * @param string $dir path to .env file
   */
  public static function PrepareFromEnv(string $dir = __DIR__):void
  {
    $dir = $dir ?? __DIR__;
    $dotenv = \Dotenv\Dotenv::createImmutable($dir);
    $dotenv->load();

    self::Prepare($_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
  }

  /**
   * Get PDO connection, die in case of fail to conect to db
   */
  public static function GetConnection(array $opt = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_PERSISTENT => true])
  {
    try {
        if (self::$dsn == null) {
          self::$dsn = new PDO(self::$host, self::$user, self::$password, $opt);
          self::$dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
      return self::$dsn;
    } catch (\PDOException $e) {
      throw new \Exception("Fail to connect to database: " . $e->getMessage());
    }
  }
}