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

class Net_Q4M_Connection_ResultSet
{
  private $_data;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_data = false;
  }

  /**
   * Set result records
   *
   * @access public
   * @param  array $data
   */
  public function setData($data)
  {
    if (is_array($data)) {
      $this->_data = $data;
    }
  }

  /**
   * Add result record
   *
   * @access public
   * @param  array $data
   */
  public function addData($data)
  {
    if (!is_array($this->_data)) {
      $this->_data = array();
    }
    $this->_data[] = $data;
  }

  /**
   * fetch a record from result data
   *
   * @access public
   * @return array
   */
  public function fetchRow()
  {
    if ($this->hasData()) {
      return array_shift($this->_data);
    }
    return false;
  }

  /**
   * Returns true if object has any data
   *
   * @access public
   * @return bool
   */
  public function hasData()
  {
    if ($this->_data) {
      return true;
    }
    return false;
  }
}

?>
