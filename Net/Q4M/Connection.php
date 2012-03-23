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

class Net_Q4M_Connection
{
  protected $_dbh;
  protected $_debug;

  /**
   * Construct database connection object
   *
   * @access  public
   * @param   string $dsn
   */
  public function __construct()
  {
    $this->_dbh = null;
    $this->_debug = true;
  }

  /**
   * Connect to database
   *
   * @access  public
   * @param   mixed $dsn    DSN string or parsed DSN array
   * @return  bool
   */
  public function connect($dsn)
  {
    return false;
  }

  /**
   * Disconnect from MySQL server
   *
   * @access  public
   * @return  bool
   */
  public function close()
  {
    return false;
  }

  /**
   * Return database connection status
   *
   * @access  public
   * @return  bool    true if connected, false if not connected
   */
  public function isConnected()
  {
    return ($this->_dbh)? true : false;
  }

  /**
   * Send a query
   *
   * @access  public
   * @param   string  $sql
   * @param   array   $param
   * @return  Net_Q4M_Connection_ResultSet
   */
  public function query($sql, $param = array())
  {
    return false;
  }

  /**
   * Switch debug mode
   *
   * @access  public
   * @param   bool  $debug
   */
  public function setDebug($debug)
  {
    $this->_debug = $debug;
  }

  /**
   * Output debug message
   *
   * @access  protected
   * @param   string $msg
   */
  protected function debug($msg)
  {
    if ($this->_debug) {
      error_log($msg);
    }
  }


  /**
   * Create Net_Q4M_Connection Object, and connect to database
   *
   * @access  public
   * @param   string $dsn    DSN string
   * @return  Net_Q4M_Connection
   */
  public static function getConnection($dsn)
  {
    $conn = null;
    $parsed_dsn = self::parseDSN($dsn);

    switch ($parsed_dsn['phptype']) {
    case 'mysql':
      require_once 'Net/Q4M/Connection/MySQL.php';
      $conn = new Net_Q4M_Connection_MySQL();
      break;
    case 'mysqli':
      require_once 'Net/Q4M/Connection/MySQLi.php';
      $conn = new Net_Q4M_Connection_MySQLi();
      break;
    case 'pdo_mysql':
      require_once 'Net/Q4M/Connection/PdoMySQL.php';
      $conn = new Net_Q4M_Connection_PdoMySQL();
      break;
    default:
      $conn = new Net_Q4M_Connection();
    }

    return $conn;
  }

  /**
   * Parse a DSN string (simplified implementation)
   *
   * The string format of DSN is only partly supported.
   * (not support dbsyntax, protocol and options)
   *
   * @todo fully support DSN format
   *
   * @access  private
   * @param   string $dsn
   * @return  array  parsed data source value
   */
  protected static function parseDSN($dsn)
  {
    $parsed_dsn = array(
      'phptype' => '',
      'username' => '',
      'password' => '',
      'host' => '',
      'port' => 3306,
      'database' => ''
    );

    $pattern = '!^([a-z0-9_]+)://([^:@]+):?([^@]*)@([^/:]+):?(\d*)/([^\?\./\\\]+)!';
    if (preg_match($pattern, $dsn, $matches)) {
      $parsed_dsn['phptype'] = $matches[1];
      $parsed_dsn['username'] = $matches[2];
      $parsed_dsn['password'] = ($matches[3])? $matches[3] : $parsed_dsn['password'];
      $parsed_dsn['host'] = $matches[4];
      $parsed_dsn['port'] = ($matches[5])? $matches[5] : $parsed_dsn['port'];
      $parsed_dsn['database'] = $matches[6];
    }

    return $parsed_dsn;
  }
}

?>
