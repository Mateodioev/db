<?php 

namespace Mateodioev\Db;

use Exception;
use Mateodioev\Db\Connection;
use PDOException;
use PDO;

class Query 
{
  /** Actual query */
  public $instance;
  /** Afectt Rows in last query */
  public $afectRows;
  /** PDO connection */
  private $db;

  private $query;
  private $datas = [];
  private float $last_ping = 0.0; // Ping function

  /**
   * Returns the data type of a parameter
   */
  private function getDataType($data)
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

  /**
   * Set data to use in the query
   * 
   * @param array $datas
   * @param string $query SQL query
   */
  private function SetQuery(string $query, array $datas=null): void
  {
    $this->query = $query;
    $this->datas = $datas;
  }

  /**
   * Set params to instance
   */
  private function SetParams()
  {
    if ($this->datas == null || empty($this->datas)) {
      return;
    }
    foreach ($this->datas as $i => $item) {
      if (is_int($i)) {
        $this->instance->bindValue($i+1, $item, $this->getDataType($item));
      } else {
        $this->instance->bindValue($i, $item, $this->getDataType($item));
      }
    }
  }

  /**
   * Execute sql query
   */
  private function ExecuteQuery(): void
  {
    if ($this->db == null) $this->db = Connection::GetConnection();
    
    try {
      $this->instance = $this->db->prepare($this->query);
      $this->SetParams();
      $this->instance->execute();
      
      $this->afectRows = $this->instance->rowCount();

      $this->last_ping = microtime(true); // For ping
    } catch (PDOException $e) {
      throw new \Exception("SQL Error: " . $e->getMessage());
    }
  }

  /**
   * Execute sql query and return result
   * 
   * @param string $query SQL query
   * @param array $datas Params to use in the query
   */
  public function Exec(string $query, array $datas=null): array
  {
    $this->SetQuery($query, $datas);

    $this->ExecuteQuery();
    return [
      'ok'       => $this->afectRows > 0,
      'afectRow' => $this->afectRows,
      'data'     => $this->instance->fetch(PDO::FETCH_ASSOC),
      'obj'      => $this->instance
    ];
  }

  /**
   * Get info of all afect rows
   *
   * @param string $query SQL query
   * @param array $datas Params to use in the query
   */
  public function GetAll(string $query, array $datas=null): array
  {
    $this->SetQuery($query, $datas);
    $responses = [];

    $this->ExecuteQuery();
    $rows = $this->instance->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
      $responses[] = $row;
    }

    return [
      'ok' => $this->afectRows > 0,
      'afectRow' => $this->afectRows,
      'rows' => $responses
    ];
  }

  /**
   * Verify if the connection is still alive
   * @throws Exception
   */
  public function Ping($last = 2)
  {
    try {
      if ($this->instance == null) return false;
      if (microtime(true) - $this->last_ping > $last) {
        $this->Exec('SELECT 1');
        $this->last_ping = microtime(true);
        return $this->afectRows > 0;
      }
      return true;
    } catch (Exception $e) {
      throw new Exception("SQL error trying to ping the server: " . $e->getMessage());
    }
  }

  /**
   * Clear the query and result datas
   */
  public function Clear(): void
  {
    $this->last_ping = 0.0;
    $this->instance = null;
    $this->afectRows = null;
    $this->query = null;
    $this->datas = null;
  }

  /**
   * Close connection and clear datas
   */
  public function Close(): void
  {
    $this->Clear();
    $this->db = null;
    Connection::$dsn = null;
  }

  public function __destruct()
  {
    $this->Close();
  }
}
