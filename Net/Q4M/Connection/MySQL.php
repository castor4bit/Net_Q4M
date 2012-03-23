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
require_once 'Net/Q4M/Exception.php';

class Net_Q4M_Connection_MySQL extends Net_Q4M_Connection
{
  /**
   * Connect to MySQL server
   *
   * @access  public
   * @param   mixed $dsn    DSN string or parsed DSN array
   * @return  Net_Q4M_Connection_MySQL
   * @throws  Net_Q4M_Exception
   */
  public function connect($dsn)
  {
    if (!is_array($dsn)) {
      $dsn = Net_Q4M_Connection::parseDSN($dsn);
    }

    $_host = $dsn['host'] .':'. $dsn['port'];
    $this->_dbh = mysql_connect($_host, $dsn['username'], $dsn['password']);
    if ($this->_dbh === false) {
      throw new Net_Q4M_Exception('Connect failed: '. mysql_error(), mysql_errno());
    }
    if (!mysql_select_db($dsn['database'], $this->_dbh)) {
      throw new Net_Q4M_Exception('Connect failed: '. mysql_error(), mysql_errno());
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
    return mysql_close($this->_dbh);
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
    $result = mysql_query($sql, $this->_dbh);
    if ($result === false) {
      throw new Net_Q4M_Exception('query() error: '. mysql_error(), mysql_errno());
    } else if ($result === true) {
      return $result;
    }

    $data = array();
    while ($_data = mysql_fetch_array($result, MYSQL_BOTH)) {
      $data[] = $_data;
    }
    mysql_free_result($result);

    $resultSet = new Net_Q4M_Connection_ResultSet();
    $resultSet->setData($data);
    return $resultSet;
  }

  /**
   * Build sql string (binds a vale to a parameter)
   *
   * @access protected
   * @param  string $sql
   * @param  array $param
   * @return string
   */
  protected function buildSqlString($sql, $param = array())
  {
    while (($pos = strpos($sql, '?')) !== false) {
       $_value = array_shift($param);
      if ($_value === null) break;

      $_value = $this->escape($_value);
      $_left = substr($sql, 0, $pos);
      $_right = ($pos < strlen($sql))? substr($sql, $pos+1) : '';

      $sql = $_left. $_value .$_right;
    }

    $this->debug('buildQueryString(): '. $sql);
    return $sql;
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
    return "'". mysql_real_escape_string($str, $this->_dbh) ."'";
  }
}

?>
