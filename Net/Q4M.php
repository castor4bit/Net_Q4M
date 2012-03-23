<?php
/**
 * Net_Q4M
 *
 * Q4M (Queue for MySQL)
 * @link http://q4m.31tools.com/
 *
 * @category  Net
 * @package   Net_Q4M
 * @version   0.1.0
 * @author    castor <castor.4bit@gmail.com>
 * @license   http://opensource.org/licenses/mit-license.html
 *
 * The MIT License
 * Copyright (c) 2008 castor <castor.4bit@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once 'Net/Q4M/Connection.php';
require_once 'Net/Q4M/Connection/ResultSet.php';

class Net_Q4M
{
  private $_conn;
  private $_dsn;

  /**
   * Construct a new Q4M object.
   *
   * @access  public
   * @param   string $dsn   PEAR::DB style DSN string (i.e.: mysql://user:pass@localhost:3306/database)
   */
  public function __construct($dsn)
  {
    $this->_dsn = $dsn;
    $this->_conn = Net_Q4M_Connection::getConnection($dsn);
  }

  /**
   * Establish connection to MySQL server
   *
   * @access public
   * @return bool
   * @throws Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  public function connect()
  {
    if (!$this->_conn->isConnected()) {
      $this->_conn->connect($this->_dsn);
    }
    return $this->_conn->isConnected();
  }

  /**
   * Disconnect from MySQL server
   *
   * @access public
   * @return bool
   */
  public function disconnect()
  {
    if ($this->_conn->isConnected()) {
      return $this->_conn->close();
    }
    return true;
  }

  /**
   * Returns an array contains the first queue
   *
   * <code>
   * $queue = new Net_Q4M();
   * $queue->connect('mysql://user:pass@localhost:3306/database');
   *
   * $row = $queue->dequeue('my_queue');
   * //$row = $queue->dequeue('my_queue', 5);   // set timeout to 5 seconds
   * //$row = $queue->dequeue('my_queue', 'my_queue2', 'my_queue3', 10);
   * if (process_row($row)) {
   *   $queue->abort();
   * }
   * $queue->end();
   * $queue->disconnect();
   * </code>
   *
   * @access  public
   * @param   string $table_name1[$table_name2, $table_name3, ...]
   * @param   int $timeout
   * @return  array     returns first queue row, or false if not available any data
   * @throws  Net_Q4M_Exception
   */
  public function dequeue()
  {
    $args = func_get_args();
    $tables = $args;

    if (func_num_args() == 0) {
      throw new Net_Q4M_Exception('dequeue($table_name[, $table_name...][, $timeout]): argument error');
    } else if (func_num_args() >= 2) {
      array_pop($tables);
    }

    $index = $this->getTableIndex($args);
    if (($index == 0) || ($index > count($tables))) {
      return false;
    }
    return $this->receiveData($tables[$index - 1]);
  }

  /**
   * Adds data to the queue
   *
   * @access  public
   * @param   string $table
   * @param   array $values
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  public function enqueue($table, $values)
  {
    $sql_columns = '';
    $sql_values = '';
    $param = array();

    foreach ($values as $key => $value) {
      $sql_columns .= ($sql_columns)? ",$key": $key;
      $sql_values .= ($sql_values)? ',?' : '?';
      $param[] = $value;
    }
    $sql = 'INSERT INTO '. $table .'('
      . $sql_columns
      . ') VALUES ('
      . $sql_values
      . ')';

    return $this->_conn->query($sql, $param);
  }

  /**
   * Count items in the queue
   *
   * @access  public
   * @param   string $table
   * @return  int
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  public function count($table)
  {
    $sql = 'SELECT count(*) FROM '. $table;

    $result = $this->_conn->query($sql);
    if ($result !== false) {
      $data = $result->fetchRow();
      if ($data !== false) {
        return intval($data[0]);
      }
    }
    return false;
  }

  /**
   * Return ithe index of the table.
   *
   * When any data becomes available, queue_wait function will
   * return the index of the table. The tables are prioritized
   * from left to right, i.e. if more than one table contains
   * any messages, the index of the leftmost table is returned.
   * If none of the table have any data available, 0 is returned.
   *
   * @access  protected
   * @param   array $args
   * @return  int   table index (return 0, if not available any data)
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  protected function getTableIndex($args)
  {
    $sql_cond = '';
    for ($i=0; $i<count($args); ++$i) {
      $sql_cond .= ($sql_cond)? ',?' : '?';
    }
    $sql = 'SELECT queue_wait('. $sql_cond .')';

    $result = $this->_conn->query($sql, $args);
    if ($data = $result->fetchRow()) {
      return $data[0];
    }
    return 0;
  }

  /**
   * Receive data
   *
   * only one row becomes ready at once
   *
   * @access  protcted
   * @param   string $table   table name
   * @return  array
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  protected function receiveData($table)
  {
    if (preg_match('/^([^:\s]+)/', trim($table), $matches)) {
      $table = $matches[1];
    } else {
      throw new Net_Q4M_Exception('Invalid table name: '. $table);
    }

    $sql = 'SELECT * FROM '. $table;
    $result = $this->_conn->query($sql);
    return $result->fetchRow();
  }

  /**
   * Remove owned-row from the table, and return to NON-OWNER mode
   *
   * @access  public
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  public function end()
  {
    // always return 1
    $this->_conn->query('SELECT queue_end()');
  }

  /**
   * Returns owned-row to the table, and return to NON-OWNER mode
   *
   * @access  public
   * @return  bool
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  public function abort()
  {
    // always return 1
    $this->_conn->query('SELECT queue_abort()');
  }

  /**
   * Returns queue status
   *
   * Returns parsed output of `SHOW ENGINE QUEUE STATUS`
   *
   * @access  public
   * @return  array   queue status
   * @throws  Net_Q4M_Exception Low-level errors will bubble up through this method.
   */
  public function status()
  {
    $sql = 'SHOW ENGINE QUEUE STATUS';
    $result = $this->_conn->query($sql);

    $status = array();
    $lines = $result->fetchRow();
    if ($lines) {
      $lines = explode("\n", $lines[2]);
      foreach ($lines as $line) {
        if (preg_match('/^([^\s]+)\s+(\d+)$/', $line, $matches)) {
          $status[$matches[1]] = $matches[2];
        }
      }
    }
    return $status;
  }
}
?>
