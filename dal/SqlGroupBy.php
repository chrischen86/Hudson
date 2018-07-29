<?php

namespace dal;

/**
 * Description of SqlGroupBy
 *
 * @author chris
 */
class SqlGroupBy
{
    public static $COUNT = "COUNT";
    public static $SUM = "SUM";
    private $aggregrate;
    private $groupBy;
    private $aggregrateProperty;

    public function __construct($groupBy, $aggregrate = "COUNT")
    {
        $this->groupBy = $groupBy;
        $this->aggregrate = $aggregrate;
    }

    public function getGroupBy()
    {
        return $this->groupBy;
    }

    public function getAggregrate()
    {
        return $this->aggregrate;
    }

    public function setAggregrateProperty($value)
    {
        $this->aggregrateProperty = $value;
    }

    public function getAggregrateProperty()
    {
        return $this->aggregrateProperty != null ? $this->aggregrateProperty : $this->groupBy;
    }

}
