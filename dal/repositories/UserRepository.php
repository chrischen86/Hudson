<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace dal\managers;
use dal\ModelBuildingHelper;
use dal\DataAccessAdapter;

/**
 * Description of UserRepository
 *
 * @author chris
 */
class UserRepository {
    private $adapter;

    public function __construct() {
        $this->adapter = new DataAccessAdapter();
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
