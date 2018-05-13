<?php

namespace dal\managers;

use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;

class RiftTypeRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function GetRiftType($type)
    {
        $sql = 'SELECT id AS rift_type_id, name, thumbnail FROM rift_type ' .
                "WHERE name like '%$type%'";
        $result = $this->adapter->query_single($sql);
        $riftType = ModelBuildingHelper::BuildRiftTypeModel($result);
        return $riftType;
    }
}