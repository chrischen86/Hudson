<?php

namespace dal\specifications;
use dal\ISqlSpecification;

/**
 * Description of RiftHistoryByUserIdSpecification
 *
 * @author chris
 */
class RiftHistoryByUserIdSpecification implements ISqlSpecification
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }
    
    public function toSqlQuery()
    {
        return "SELECT h.id AS rift_history_id, h.owner_id, h.type_id, h.scheduled_time, h.is_deleted " .
                "FROM rift_history h " .
                "WHERE owner_id = '$this->userId' AND is_deleted=0";
    }

}
