<?php

namespace dal;

use dal\SqlOperator;

/**
 * Description of SqlFilter
 *
 * @author chris
 */
class SqlFilter implements ISqlFilter
{
    private $items = [];
    private $operator;

    public function __construct()
    {
        $this->operator = SqlOperator::$AND;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    public function toWhereClause()
    {
        $whereClause = "";
        foreach ($this->items as $val)
        {
            $filter = $val[0];
            $operator = $val[1];

            if ($whereClause == "")
            {
                $whereClause = sprintf("%s", $filter->toWhereClause());
            }
            else
            {
                $whereClause = sprintf("%s %s %s", $whereClause, $operator, $filter->toWhereClause());
            }
        }
        return sprintf("(%s)",$whereClause);
    }

    public function addParam(ISqlFilter $filter, $operator = "AND")
    {
        array_push($this->items, [$filter, $operator]);
    }

    public function any()
    {
        return sizeof($this->items) > 0;
    }

}
