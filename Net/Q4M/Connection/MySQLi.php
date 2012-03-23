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

require_once 'Net/Q4M/Connection/MySQL.php';

class Net_Q4M_Connection_MySQLi extends Net_Q4M_Connection_MySQL
{
  /**
   * Connect to MySQL server
   *
   * @access  public
   * @param   mixed $dsn    DSN string or parsed DSN array
   * @return  Net_Q4M_Connection_MySQLi
   * @throws  Net_Q4M_Exception
   */
  public function connect($dsn)
  {
    if (!is_array($dsn)) {
      $dsn = Net_Q4M_Connection::parseDSN($dsn);
    }

    $this->_dbh = new mysqli($dsn['host'], $dsn['username'], $dsn['password'],
      $dsn['database'], $dsn['port']);

    if (mysqli_connect_errno()) {
      throw new Net_Q4M_Exception('Connect failed: '. mysqli_connect_error(), mysqli_connect_errno());
    }

    return $this->_dbh;
  }

  /**
   * Disconnect from MySQL server
   *
   * @access  public
   * @return  bool
   */
  public function close()
  {
    return $this->_dbh->close();
  }

  /**
   * Send a query
   *
   * @access  public
   * @param   string  $sql
   * @param   array   $param
   * @return  Net_Q4M_Connection_ResultSet
   * throws   Net_Q4M_Exception
   */
  public function query($sql, $param = array())
  {
    $sql = $this->buildSqlString($sql, $param);
    $result = $this->_dbh->query($sql);
    if ($result === false) {
      throw new Net_Q4M_Exception('query() error: '. $this->_dbh->error, $this->_dbh->errno);
    } else if ($result === true) {
      return $result;
    }

    $data = array();
    while ($_data = $result->fetch_array(MYSQL_BOTH)) {
      $data[] = $_data;
    }
    $result->close();

    $resultSet = new Net_Q4M_Connection_ResultSet();
    $resultSet->setData($data);
    return $resultSet;
  }

  /**
   * Escapes special characters in a string for use in a SQL statement
   *
   * @access  protected
   * @param   string $str
   * @return  string      Returns the escaped string
   */
  protected function escape($str)
  {
    if (is_numeric($str)) {
      return $str;
    }
    return "'". $this->_dbh->real_escape_string($str) ."'";
  }
}

?>
