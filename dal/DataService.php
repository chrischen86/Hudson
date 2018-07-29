<?php

namespace dal;

use dal\IDataAccessAdapter;
use dal\SqlPredicate;

class DataService
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function query(SqlPredicate $predicate)
    {
        $sql = $predicate->toQuery();
        $results = $this->adapter->query($sql);
        $toReturn = [];
        foreach ($results as $item)
        {
            array_push($toReturn, $item);
        }
        return $toReturn;
    }

}
