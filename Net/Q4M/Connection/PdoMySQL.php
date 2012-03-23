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

class Net_Q4M_Connection_PdoMySQL extends Net_Q4M_Connection
{
  /**
   * Connect to MySQL server
   *
   * @access  public
   * @param   mixed $dsn    DSN string or parsed DSN array
   * @return  Net_Q4M_Connection_PdoMySQL
   * @throws  Net_Q4M_Exception
   */
  public function connect($dsn)
  {
    if (!is_array($dsn)) {
      $dsn = Net_Q4M_Connection::parseDSN($dsn);
    }
    $pdo_dsn = sprintf("mysql:host=%s;port=%d;dbname=%s",
      $dsn['host'], $dsn['port'], $dsn['database']);

    try {
      $this->_dbh = new PDO($pdo_dsn, $dsn['username'], $dsn['password']);
      $this->_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      throw new Net_Q4M_Exception('Connect failed: '. $e->getMessage());
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
    $this->_dbh = null;
    return true;
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
    try {
      $sth = $this->_dbh->prepare($sql);
      if ($param) {
        $sth->execute($param);
      } else {
        $sth->execute();
      }
      if (!$this->isSelectQuery($sql)) {
        return true;
      }
    } catch (Exception $e) {
      throw new Net_Q4M_Exception('query() error: '. $e->getMessage());
    }

    $data = $sth->fetchAll(PDO::FETCH_BOTH);
    $resultSet = new Net_Q4M_Connection_ResultSet();
    $resultSet->setData($data);
    return $resultSet;
  }

  /**
   * Returns true if SELECT, SHOW, EXPLAIN or DESCRIBE query string
   *
   * @access  protected
   * @param   string $sql
   * @return  bool
   */
  protected function isSelectQuery($sql)
  {
    if (preg_match('/^\s*(select|explain|show|describe)/i', $sql)) {
      return true;
    }
    return false;
  }
}

?>
