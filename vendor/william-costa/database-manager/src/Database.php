<?php

namespace WilliamCosta\DatabaseManager;

use \PDO;
use \PDOException;

class Database
{

  /**
   * Host de conexão com o banco de dados
   * @var string
   */
  private static $host;

  /**
   * Nome do banco de dados
   * @var string
   */
  private static $name;

  /**
   * Usuário do banco
   * @var string
   */
  private static $user;

  /**
   * Senha de acesso ao banco de dados
   * @var string
   */
  private static $pass;

  /**
   * Porta de acesso ao banco
   * @var integer
   */
  private static $port;

  /**
   * Nome da tabela a ser manipulada
   * @var string
   */
  private $table;

  /**
   * Instancia de conexão com o banco de dados
   * @var PDO
   */
  private $connection;

  /**
   * Método responsável por configurar a classe
   * @param  string  $host
   * @param  string  $name
   * @param  string  $user
   * @param  string  $pass
   * @param  integer $port
   */
  public static function config($host, $name, $user, $pass, $port = 3306)
  {
    self::$host = $host;
    self::$name = $name;
    self::$user = $user;
    self::$pass = $pass;
    self::$port = $port;
  }

  /**
   * Define a tabela e instancia e conexão
   * @param string $table
   */
  public function __construct($table = null)
  {
    $this->table = $table;
    $this->setConnection();
  }

  /**
   * Método responsável por criar uma conexão com o banco de dados
   */
  private function setConnection()
  {
    try {
      $this->connection = new PDO(
        'mysql:host=' . self::$host . ';dbname=' . self::$name . ';port=' . self::$port,
        self::$user,
        self::$pass
      );
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die('ERROR: ' . $e->getMessage());
    }
  }

  /**
   * Método responsável por executar queries dentro do banco de dados
   * @param  string $query
   * @param  array  $params
   * @return \PDOStatement
   */
  public function execute($query, $params = [])
  {
    try {
      $statement = $this->connection->prepare($query);
      $statement->execute($params);
      return $statement;
    } catch (PDOException $e) {
      die('ERROR: ' . $e->getMessage());
    }
  }

  /**
   * Método responsável por inserir dados no banco
   * @param  array $values [ field => value ]
   * @return integer ID inserido
   */
  public function insert($values)
  {
    // DADOS DA QUERY
    $fields = array_keys($values);
    $binds = array_pad([], count($fields), '?');

    // MONTA A QUERY
    $query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $binds) . ')';

    // EXECUTA O INSERT
    $this->execute($query, array_values($values));

    // RETORNA O ID INSERIDO
    return $this->connection->lastInsertId();
  }

  /**
   * Método responsável por executar uma consulta no banco
   * @param  string $where
   * @param  string $order
   * @param  string $limit
   * @param  string $fields
   * @param  string $group
   * @return PDOStatement
   */
  public function select($where = null, $order = null, $limit = null, $fields = '*', $group = null)
  {
    // DADOS DA QUERY
    $where = !empty($where) ? 'WHERE ' . $where : '';
    $order = !empty($order) ? 'ORDER BY ' . $order : '';
    $limit = !empty($limit) ? 'LIMIT ' . $limit : '';
    $group = !empty($group) ? 'GROUP BY ' . $group : '';

    // MONTA A QUERY
    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $group . ' ' . $order . ' ' . $limit;
    // EXECUTA A QUERY
    return $this->execute($query);
  }

  /**
   * Método responsável por executar atualizações no banco de dados
   * @param  string $where
   * @param  array $values [ field => value ]
   * @return boolean
   */
  public function update($where, $values)
  {
    // DADOS DA QUERY
    $fields = array_keys($values);

    // MONTA A QUERY
    $query = 'UPDATE ' . $this->table . ' SET ' . implode('=?,', $fields) . '=? WHERE ' . (!empty($where) ? $where : '1=1');

    // EXECUTAR A QUERY
    $this->execute($query, array_values($values));

    // RETORNA SUCESSO
    return true;
  }

  /**
   * Método responsável por excluir dados do banco
   * @param  string $where
   * @return boolean
   */
  public function delete($where)
  {
    // MONTA A QUERY
    $query = 'DELETE FROM ' . $this->table . ' WHERE ' . (!empty($where) ? $where : '1=1');

    // EXECUTA A QUERY
    $this->execute($query);

    // RETORNA SUCESSO
    return true;
  }

}