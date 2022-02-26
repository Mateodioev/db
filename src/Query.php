<?php 

namespace Mateodioev\Db;

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
  private $datas;

  /**
   * Set data to use in the query
   * 
   * @param array $datas
   * @param string $query SQL query
   */
  private function SetQuery(string $query, $datas=''): void
  {
    $this->query = $query;
    $this->datas = $datas;
  }

  /**
   * Execute sql query
   */
  private function ExecuteQuery(): void
  {
    if ($this->db == null) $this->db = Connection::GetConnection();
    
    try {
      $this->instance = $this->db->prepare($this->query);

      if (empty($this->datas)) {
        $this->instance->execute();
      } else {
        $this->instance->execute($this->datas);
      }
      $this->afectRows = $this->instance->rowCount();

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
  public function Exec(string $query, $datas=''): array
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
  public function GetAll(string $query, $datas=''): array
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

}