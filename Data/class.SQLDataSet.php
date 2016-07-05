<?php
namespace Data;

class SQLDataSet extends DataSet
{
    protected $pdo;

    public function __construct($params)
    {
        if(isset($params['user']))
        {
            $this->pdo = new \PDO($params['dsn'], $params['user'], $params['pass']);
        }
        else
        {
            $this->pdo = new \PDO($params['dsn']);
        }
    }

    /**
     * Get the number of rows affected by the query
     *
     * @param string $sql The SQL string
     *
     * @return integer The number of rows affected by the query
     */
    private function _get_row_count_for_query($sql)
    {
        $stmt = $this->pdo->query($sql);
        if($stmt === false)
        {
            return 0;
        }
        $count = $stmt->rowCount();
        if($count === 0)
        {
            $array = $stmt->fetchAll();
            $count = count($array);
        }
        return $count;
    }

    function _tableExistsNoPrefix($name)
    {
        if($this->_get_row_count_for_query('SHOW TABLES LIKE '.$this->pdo->quote($name)) > 0)
        {
            return true;
        }
        else if($this->_get_row_count_for_query('SELECT * FROM sqlite_master WHERE name LIKE '.$this->pdo->quote($name)) > 0)
        {
            return true;
        }
        return false;
    }

    function _tableExists($name)
    {
        if($this->_get_row_count_for_query('SHOW TABLES LIKE '.$this->pdo->quote('tbl'.$name)) > 0)
        {
            return true;
        }
        else if($this->_get_row_count_for_query('SELECT * FROM sqlite_master WHERE name LIKE '.$this->pdo->quote('tbl'.$name)) > 0)
        {
            return true;
        }
        return false;
    }

    function _viewExists($name)
    {
        if($this->_get_row_count_for_query('SHOW TABLES LIKE '.$this->pdo->quote('v'.$name)) > 0)
        {
            return true;
        }
        else if($this->_get_row_count_for_query('SELECT * FROM sqlite_master WHERE name LIKE '.$this->pdo->quote('v'.$name)) > 0)
        {
            return true;
        }
        return false;
    }

    function tableExists($name)
    {
        if($this->_tableExists($name))
        {
            return true;
        }
        if($this->_tableExistsNoPrefix($name))
        {
            return true;
        }
        if($this->_viewExists($name))
        {
            return true;
        }
        return false;
    }

    function getTable($name)
    {
        if($this->_tableExists($name))
        {
            return new SQLDataTable($this, 'tbl'.$name);
        }
        if($this->_viewExists($name))
        {
            return new SQLDataTable($this, 'v'.$name);
        }
        if($this->_tableExistsNoPrefix($name))
        {
            return new SQLDataTable($this, $name);
        }
        throw new \Exception('No such table '.$name);
    }

    /**
     * @param array $sort The array to sort by or false to not sort
     */
    private function getOrderByClause($sort)
    {
        if(empty($sort))
        {
            return false;
        }
        $sql = ' ORDER BY ';
        $tmp = array();
        foreach($sort as $sort_col=>$dir)
        {
            array_push($tmp, $sort_col.' '.($dir === 1 ? 'ASC' : 'DESC'));
        }
        $sql .= implode($tmp, ',');
        return $sql;
    }

    private function getLimitClause($count, $skip)
    {
        if($count === false)
        {
            return false;
        }
        $count = intval($count);
        if($skip !== false)
        {
            $skip = intval($count);
            return " LIMIT $skip, $count";
        }
        return ' LIMIT '.$count;
    }

    /**
     * Read data from the specified SQL table
     *
     * @param string $tablename The name of the table to read from
     * @param string $where The where caluse of the SQL statement
     * @param string $select The colums to read
     * @param string $count The number of rows to read
     * @param string $skip The number of rows to skip over
     * @param array $sort The array to sort by or false to not sort
     *
     * @return array An array of all the returned records
     */
    public function read($tablename, $where = false, $select = '*', $count = false, $skip = false, $sort = false)
    {
        if($select === false)
        {
            $select = '*';
        }
        $sql = "SELECT $select FROM $tablename";
        if($where !== false)
        {
            $sql .= ' WHERE '.$where;
        }
        $tmp = $this->getLimitClause($count, $skip)
        if($tmp !== false)
        {
            $sql .= $tmp;
        }
        $tmp = $this->getOrderByClause($sort);
        if($tmp !== false)
        {
            $sql .= $tmp;
        }
        $stmt = $this->pdo->query($sql, \PDO::FETCH_ASSOC);
        if($stmt === false)
        {
            return false;
        }
        $ret = $stmt->fetchAll();
        if($ret === false || empty($ret))
        {
            return false;
        }
        return $ret;
    }

    function update($tablename, $where, $data)
    {
        $set = array();
        if(is_object($data))
        {
            $data = (array)$data;
        }
        $cols = array_keys($data);
        $count = count($cols);
        for($i = 0; $i < $count; $i++)
        {
            array_push($set, $cols[$i].'='.$this->pdo->quote($data[$cols[$i]]));
        }
        $set = implode(',', $set);
        $sql = "UPDATE $tablename SET $set WHERE $where";
        if($this->pdo->exec($sql) === false)
        {
            return false;
        }
        return true;
    }

    function create($tablename, $data)
    {
        $set = array();
        if(is_object($data))
        {
            $data = (array)$data;
        }
        $cols = array_keys($data);
        $count = count($cols);
        for($i = 0; $i < $count; $i++)
        {
            array_push($set, $this->pdo->quote($data[$cols[$i]]));
        }
        $cols = implode(',', $cols);
        $set = implode(',', $set);
        $sql = "INSERT INTO $tablename ($cols) VALUES ($set);";
        if($this->pdo->exec($sql) === false)
        {
            return false;
        }
        return true;
    }

    function delete($tablename, $where)
    {
        $sql = "DELETE FROM $tablename WHERE $where";
        if($this->pdo->exec($sql) === false)
        {
            return false;
        }
        return true;
    }

    function raw_query($sql)
    {
        $stmt = $this->pdo->query($sql, \PDO::FETCH_ASSOC);
        if($stmt === false)
        {
            return false;
        }
        $ret = $stmt->fetchAll();
        return $ret;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
