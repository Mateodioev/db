<?php 

namespace Mateodioev\Db;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

use function is_int, is_bool, is_null;

class Query extends Connection
{
  /*** Actual query */
  public ?PDOStatement $instance;
  public int $afectRows = 0;

  private string $query = '';
  private array $datas = [];
  private float $lastPing = 0.0;

  /**
   * Execute sql query and return result
   */
  public function exec(string $query, ?array $data = null): array
  {
    $this->setQuery($query, $data)->executeQuery();

    return [
      'ok'        => $this->afectRows > 0,
      'afectRows' => $this->afectRows,
      'data'      => $this->instance->fetch(PDO::FETCH_ASSOC),
      'obj'       => $this->instance
    ];
  }

  public function execAll(string $query, ?array $data = null): array
  {
    $this->setQuery($query, $data)->executeQuery();
    $res = $this->instance->fetchAll(PDO::FETCH_ASSOC);

    $rows = [];
    foreach ($res as $row) {
      $rows[] = $row;
    }

    return [
      'ok'        => $this->afectRows > 0,
      'afectRows' => $this->afectRows,
      'rows'      => $rows,
      'obj'       => $this->instance
    ];
  }

  private static function getDataType(mixed $data): int
  {
    if (is_int($data)) {
      return PDO::PARAM_INT;
    } elseif (is_bool($data)) {
      return PDO::PARAM_BOOL;
    } elseif (is_null($data)) {
      return PDO::PARAM_NULL;
    } else {
      return PDO::PARAM_STR;
    }
  }

  private function setQuery(string $query, ?array $data = null): Query
  {
    $this->query = $query;
    if ($data) $this->data = $data;

    return $this;
  }

  private function setParams(): Query
  {
    if (is_null($this->datas) || empty($this->datas)) {
      return $this;
    }

    foreach ($this->datas as $i => $val) {
      if (is_int($i)) {
        $this->instance->bindValue($i+1, $val, self::getDataType($val));
      } else {
        $this->instance->bindValue($i, $val, self::getDataType($val));
      }
    }
    return $this;
  }

  private function executeQuery()
  {
    $db = $this->connect();

    try {
      $this->instance = $db->prepare($this->query);
      $this->setParams()->instance->execute();

      $this->afectRows = $this->instance->rowCount();
      $this->lastPing = microtime(true);
    } catch (PDOException $e) {
      throw new DbException('SQL error: ' . $e->getMessage(), $e->getCode(), $e);
    }
  }

  public function ping($last = 2)
  {
    if ($this->instance == null) return false;
    try {
      if (microtime(true) - $this->lastPing > $last) {
        $this->exec('SELECT 1');
        $this->lastPing = microtime(true);
        return $this->afectRows > 0;
      }
      return true;
    } catch (Exception $e) {
      throw new DbException("SQL error trying to ping the server: " . $e->getMessage());
    }
  }

  public function __destruct()
  {
    $this->instance = null;
    $this->query    = '';
    $this->datas    = [];
  }
}
