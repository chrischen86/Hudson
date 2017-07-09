<?php

namespace dal\managers;

use dal\ModelBuildingHelper;
use dal\IDataAccessAdapter;

/**
 * Description of UserRepository
 *
 * @author chris
 */
class UserRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function GetUserByName($name)
    {
        $sql = 'SELECT id as user_id, name, vip ' .
                'FROM users ' .
                "WHERE name = '$name'";
        $result = $this->adapter->query_single($sql);
        return ModelBuildingHelper::BuildUserModel($result);
    }

    public function GetUserById($id)
    {
        $sql = 'SELECT id as user_id, name, vip ' .
                'FROM users ' .
                "WHERE id = '$id'";
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            return null;
        }
        return ModelBuildingHelper::BuildUserModel($result);
    }

}
