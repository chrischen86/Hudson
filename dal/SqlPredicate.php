<?php

namespace dal;

use dal\ISqlFilter;
use dal\SqlGroupBy;

/**
 * Description of SqlPredicate
 *
 * @author chris
 */
class SqlPredicate
{
    private $table;
    private $select;

    /**
     * @var ISqlFilter
     */
    private $filter;
    private $orderBy;

    /**
     * @var SqlGroupBy
     */
    private $groupBy;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function setSelect(array $select)
    {
        $this->select = $select;
    }

    public function setFilter(SqlFilter $filter)
    {
        $this->filter = $filter;
    }

    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    public function setGroupBy(SqlGroupBy $groupBy)
    {
        $this->groupBy = $groupBy;
    }

    public function toQuery()
    {
        $sql = "SELECT";
        $sql = sprintf("%s %s", $sql, $this->buildSelect());
        if ($this->groupBy != null)
        {
            $groupBy = $this->groupBy->getGroupBy();
            $aggregrate = $this->groupBy->getAggregrate();
            $sql = sprintf("%s, %s(%s) AS group_%s", $sql, $aggregrate, $groupBy, $groupBy);
        }
        $sql = sprintf("%s FROM %s", $sql, $this->table);
        if ($this->filter != null && $this->filter->any())
        {
            $sql = sprintf("%s WHERE %s", $sql, $this->filter->toWhereClause());
        }
        if ($this->groupBy != null)
        {
            $sql = sprintf("%s GROUP BY %s", $sql, $this->groupBy->getGroupBy());
        }

        return $sql;
    }

    private function buildSelect()
    {
        if (sizeof($this->select) <= 0)
        {
            return "*";
        }
        return implode(", ", $this->select);
    }

    private function isNullOrEmpty($str)
    {
        return (!isset($str) || trim($str) === '');
    }

}
