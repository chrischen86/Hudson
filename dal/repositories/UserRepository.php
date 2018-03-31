<?php

namespace dal\managers;

use dal\ModelBuildingHelper;
use dal\IDataAccessAdapter;
use dal\models\UserModel;

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
        $sql = 'SELECT id as user_id, name, vip, is_archived ' .
                'FROM users ' .
                "WHERE name = '$name'";
        $result = $this->adapter->query_single($sql);
        return ModelBuildingHelper::BuildUserModel($result);
    }

    public function GetUserById($id)
    {
        $sql = 'SELECT id as user_id, name, vip, is_archived ' .
                'FROM users ' .
                "WHERE id = '$id'";
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            return null;
        }
        return ModelBuildingHelper::BuildUserModel($result);
    }

    public function ArchiveUser($user)
    {
        $sql = 'UPDATE users ' .
                'SET is_archived = 1 ' .
                "WHERE id = '" . $user->id . "'";
        $this->adapter->query($sql);
    }

    public function RestoreUserById($user)
    {
        $sql = 'UPDATE users ' .
                'SET is_archived = 0 ' .
                "WHERE id = '" . $user->id . "'";
        $this->adapter->query($sql);
    }

    public function GetActiveUsers()
    {
        $sql = 'SELECT id as user_id, name, vip, is_archived ' .
                'FROM users ' .
                "WHERE is_archived = 0";
        $results = $this->adapter->query($sql);
        $toReturn = [];
        foreach ($results as $item)
        {
            $user = ModelBuildingHelper::BuildUserModel($item);
            array_push($toReturn, $user);
        }
        return $toReturn;
    }
    
    public function UpdateUser(UserModel $user)
    {
        $sql = 'UPDATE users ' .
                "SET name = '" . $user->name . "' " .
                "WHERE id = '" . $user->id . "'";
        $this->adapter->query($sql);
    }

}
