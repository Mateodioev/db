<?php 

namespace Mateodioev\Db;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Mateodioev\Utils\Files;
use PDO;
use PDOException;

use function realpath;

/**
 * @method static fromFile()
 * @method static fromEnv()
 * @method static createDSN()
 * @method static getInstance()
 * @method public addOptions()
 * @method public connect()
 */
class Connection
{
  public static ?PDO $connection = null;

  private string $dsn, $username, $password;

  private array $options = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  /**
   * Create connection to the database
   *
   * @param string $dsn The Data Source Name
   * @param string $user The user name for the DSN string.
   * @param string $pass The password for the DSN string.
   */
  public function __construct(string $dsn, string $user, string $pass) {
    $this->dsn = $dsn;
    $this->username = $user;
    $this->password = $pass;
  }

  /**
   * Create new connection from file
   *
   * @param string $file Path to the file containing the DSN string
   * @param string $user The user name for the DSN string.
   * @param string $pass The password for the DSN string.
   */
  public static function fromFile(string $file, string $user, string $pass): Connection
  {
    $realFile = realpath($file);

    if (!Files::isFile($file)) {
      throw new DbException(sprintf('File "%s" does not exist', $file));
    }

    $dsn = 'uri:file://' . $realFile;
    return new Connection($dsn, $user, $pass);
  }

  /**
   * Creat connection from .env file
   * @throws DbException
   */
  public static function fromEnv(string $dirEnv = __DIR__): Connection
  {
    try {
      if (!isset($_ENV['DB_HOST'])) Dotenv::createImmutable($dirEnv)->load();
    } catch (InvalidPathException $e) {
      throw new DbException('Invalid path specified', previous: $e);
    }

    if (isset($_ENV['DB_DSN_FILE'])) {
      return self::fromFile($_ENV['DB_DSN_FILE'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    } else {
      $port    = $_ENV['DB_PORT'] ?? 3306;
      $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
      $soquet  = $_ENV['DB_SOQUET'] ?? null;
      $driver  = $_ENV['DB_DRIVER'] ?? 'mysql';

      $dsn = self::createDSN($_ENV['DB_HOST'], $_ENV['DB_NAME'], $port, $charset, $soquet, $driver);
      return new Connection($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
    }
  }

  /**
   * Create _Data Source Name_ string
   * @throws DbException
   */
  public static function createDSN(string $host, string $dbName, int $port = 3306, string $charset = 'utf8mb4', ?string $unixSoquet = null, string $driver = 'mysql')
  {
    if (!in_array($driver, PDO::getAvailableDrivers())) {
      throw new DbException('Invalid driver specified');
    }

    $dsn = $driver . ':dbname=' . $dbName . ';charset=' . $charset;

    if ($unixSoquet) {
      $dsn .= ';unix_socket=' . $unixSoquet;
    } else {
      $dsn .= ';host=' . $host . ';port=' . $port;
    }

    return $dsn;
  }

  /**
   * Add atributes to the connection
   *
   * @param array $opt
   */
  public function addOptions(array $opt): Connection
  {
    $this->options = array_merge($this->options, $opt);
    return $this;
  }

  /**
   * Connect to the database
   */
  public function connect(): PDO
  {
    try {
      if (self::$connection === null) {
        self::$connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
      }
      return self::$connection;
    } catch (PDOException $e) {
      throw new DbException(sprintf('"Fail to connect to database: %s', $e->getMessage()), previous: $e);
    }
  }

  public function getQuery(): Query
  {
    return new Query($this->dsn, $this->username, $this->password);
  }

  /**
   * Return PDO connection
   * @throws DbException
   */
  public static function getInstance(): PDO
  {
    if (!static::$connection instanceof PDO) {
      throw new DbException('First start the connection with method "connect"');  
    }
    return static::$connection;
  }

  public static function setPDO(PDO $con): void
  {
    static::$connection = $con;
  }

  public function destroy()
  {
    self::$connection = null;
  }
}
