<?php

namespace dal\specifications;

use dal\ISqlSpecification;
use dal\ISqlFilter;

/**
 * Description of RiftHistoryByUserIdSpecification
 *
 * @author chris
 */
class RiftHistoryByFilterSpecification implements ISqlSpecification
{
    /**
     * @var ISqlFilter
     */
    private $filter;

    public function __construct(ISqlFilter $filter)
    {
        $this->filter = $filter;
    }

    public function toSqlQuery()
    {
        $sql = "SELECT h.id AS rift_history_id, h.owner_id, h.type_id, h.scheduled_time, h.is_deleted " .
                "FROM rift_history h ";
        if ($this->filter->any())
        {
            $sql = sprintf("WHERE %s", $this->filter->toWhereClause());
        }
        return $sql;
    }

}
