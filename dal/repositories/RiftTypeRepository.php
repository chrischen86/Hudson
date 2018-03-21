<?php

namespace dal\managers;

use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;
use StateEnum;

/**
 * Description of StateManager
 *
 * @author chris
 */
class RiftTypeRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    public function GetRiftType($type)
    {
        $sql = 'SELECT name, thumbnail FROM rift_type ' .
                "WHERE name like '%$type%'";
        $result = $this->adapter->query_single($sql);
        $riftType = ModelBuildingHelper::BuildRiftTypeModel($result);
        return $riftType;
    }
}
