<?php
/*
 *  This file is part of OpenSearchServer PHP Client.
*
*  Copyright (C) 2008-2014 Emmanuel Keller / Jaeksoft
*
*  http://www.open-search-server.com
*
*  OpenSearchServer PHP Client is free software: you can redistribute it and/or modify
*  it under the terms of the GNU Lesser General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*
*  OpenSearchServer PHP Client is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU Lesser General Public License for more details.
*
*  You should have received a copy of the GNU Lesser General Public License
*  along with OpenSearchServer PHP Client.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * @file
 * Class to access OpenSearchServer API
 */

require_once(dirname(__FILE__).'/oss_abstract.class.php');
require_once(dirname(__FILE__).'/oss_search_abstract.class.php');


/**
 * @package OpenSearchServer
*/
class OssSearch extends OssSearchAbstract {

  protected $query;
  protected $start;
  protected $rows;
  protected $lang;
  protected $filter;
  protected $negativeFilter;
  protected $field;
  protected $sort;
  protected $operator;
  protected $collapse;
  protected $facet;
  protected $join;
  protected $joinFilter;
  protected $joinNegativeFilter;

  /**
   * @param $enginePath The URL to access the OSS Engine
   * @param $index The index name
   * @return OssSearch
   */
  public function __construct($enginePath, $index = NULL, $rows = NULL, $start = NULL, $login = NULL, $apiKey = NULL) {
    parent::__construct($enginePath, $index, $login, $apiKey);

    $this->rows($rows);
    $this->start($start);

    $this->field  = array();
    $this->filter  = array();
    $this->negativeFilter  = array();
    $this->sort    = array();
    $this->facet  = array();
    $this->join = array();
    $this->joinFilter  = array();
    $this->joinNegativeFilter  = array();
    $this->query = NULL;
    $this->lang = NULL;
    $this->operator = NULL;
    $this->collapse  = array('field' => NULL, 'max' => NULL, 'mode' => NULL, 'type' => NULL);
  }

  /**
   * Specify the query
   * @param $query string
   * @return OssSearch
   */
  public function query($query = NULL) {
    $this->query = $query;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function start($start = NULL) {
    $this->start = $start;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function rows($rows = NULL) {
    $this->rows = $rows;
    return $this;
  }

  /**
   * Set the default operation OR or AND
   * @param unknown_type $start
   * @return OssSearch
   */
  public function operator($operator = NULL) {
    $this->operator = $operator;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function filter($filter = NULL) {
    $this->filter[] = $filter;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function negativeFilter($negativeFilter = NULL) {
    $this->negativeFilter[] = $negativeFilter;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function lang($lang = NULL) {
    $this->lang = $lang;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function field($fields) {
    $this->field = array_unique(array_merge($this->field, (array)$fields));
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function sort($fields) {
    foreach ((array)$fields as $field)
      $this->sort[] = $field;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function collapseField($field) {
    $this->collapse['field'] = $field;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function collapseMode($mode) {
    $this->collapse['mode'] = $mode;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function collapseType($type) {
    $this->collapse['type'] = $type;
    return $this;
  }


  /**
   * @return OssSearch
   */
  public function collapseMax($max) {
    $this->collapse['max'] = $max;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function facet($field, $min = NULL, $multi = FALSE, $multi_collapse = FALSE) {
    $this->facet[$field] = array('min' => $min, 'multi' => $multi, 'multi_collapse' => $multi_collapse);
    return $this;
  }

  /**
   * @param string $filter
   * @return OssSearch
   */
  public function addJoin($key, $value) {
  	$this->join[$key] = $value;
  	return $this;
  }
  
  /**
   * @return OssSearch
   */
  public function join($position, $value) {
    $intpos = (int) $position;
    $this->join['jq'.$intpos] = $value;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function joinFilter($position, $filter = NULL) {
    $intpos = (int) $position;
    if (!array_key_exists($intpos, $this->joinFilter)) {
      $this->joinFilter[$intpos] = array();
    }
    $this->joinFilter[$intpos][] = $filter;
    return $this;
  }

  /**
   * @return OssSearch
   */
  public function joinNegativeFilter($position, $negativeFilter = NULL) {
    $intpos = (int) $position;
    if (!array_key_exists($intpos, $this->joinNegativeFilter)) {
      $this->joinFilter[$intpos] = array();
    }
    $this->joinNegativeFilter[$intpos][] = $negativeFilter;
    return $this;
  }

  protected function addParams($queryChunks = NULL) {

    $queryChunks = parent::addParams($queryChunks);
     
    $queryChunks[] = 'q=' . urlencode((empty($this->query) ? "*:*" : $this->query));

    if (!empty($this->lang)) {
      $queryChunks[] = 'lang=' . $this->lang;
    }

    if ($this->rows   !== NULL) {
      $queryChunks[] = 'rows='  . (int) $this->rows;
    }

    if ($this->start !== NULL) {
      $queryChunks[] = 'start=' . (int) $this->start;
    }

    if ($this->operator !== NULL) {
      $queryChunks[] = 'operator=' . $this->operator;
    }

    // Sorting
    foreach ((array) $this->sort as $sort) {
      if (empty($sort)) {
        continue;
      }
      $queryChunks[] = 'sort=' . urlencode($sort);
    }

    // Filters
    foreach ((array) $this->filter as $filter) {
      if (empty($filter)) {
        continue;
      }
      $queryChunks[] = 'fq=' . urlencode($filter);
    }

    // Negative Filters
    foreach ((array) $this->negativeFilter as $negativeFilter) {
      if (empty($negativeFilter)) {
        continue;
      }
      $queryChunks[] = 'fqn=' . urlencode($negativeFilter);
    }

    // Fields
    foreach ((array)$this->field as $field) {
      if (empty($field)) continue;
      $queryChunks[] = 'rf=' . $field;
    }

    // Facets
    foreach ((array)$this->facet as $field => $options) {
      if ($options['multi']) {
        $facet = 'facet.multi=';
      } else if ($options['multi_collapse']) {
        $facet = 'facet.multi.collapse=';
      } else {
        $facet = 'facet=';
      }
      $facet .= $field;
      if ($options['min'] !== NULL) {
        $facet .= '(' . $options['min'] . ')';
      }
      $queryChunks[] = $facet;
    }

    // Join query parameter
    foreach ((array)$this->join as $key => $value) {
      $queryChunks[] = $key.'='.urlencode($value);
    }

    // Join filters
    foreach ((array) $this->joinFilter as $position => $filters) {
      foreach ((array) $filters as $filter) {
        if (empty($filter)) {
          continue;
        }
        $queryChunks[] = 'jq'.$position.'.fq=' . urlencode($filter);
      }
    }

    // Join negative Filters
    foreach ((array) $this->joinNegativeFilter as $position => $negativeFilters) {
      foreach ((array) $negativeFilters as $negativeFilter) {
        if (empty($negativeFilter)) {
          continue;
        }
        $queryChunks[] = 'jq'.$position.'.fqn=' . urlencode($negativeFilter);
      }
    }

    // Collapsing
    if ($this->collapse['field']) {
      $queryChunks[] = 'collapse.field=' . $this->collapse['field'];
    }
    if ($this->collapse['type']) {
      $queryChunks[] = 'collapse.type=' . $this->collapse['type'];
    }
    if ($this->collapse['mode'] !== NULL) {
      $queryChunks[] = 'collapse.mode=' . $this->collapse['mode'];
    }
    if ($this->collapse['max'] !== NULL) {
      $queryChunks[] = 'collapse.max=' . (int)$this->collapse['max'];
    }

    return $queryChunks;
  }
}
?>