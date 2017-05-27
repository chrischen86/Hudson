<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace dal;
use dal\models\UserModel;
use dal\models\ConquestModel;
use dal\models\ZoneModel;
use dal\models\NodeModel;
/**
 * Description of ModelBuildingHelper
 *
 * @author chris
 */
class ModelBuildingHelper {
    
    public static function BuildUserModel($result)
    {
        $toReturn = new UserModel();
        $toReturn->id = $result['user_id'];
        $toReturn->name = $result['name'];
        $toReturn->vip = $result['vip'];
        return $toReturn;
    }
    
    public static function BuildConquestModel($result)
    {
        $toReturn = new ConquestModel();
        $toReturn->id = $result['conquest_id'];
        $toReturn->date = $result['date'];
        $toReturn->phase = $result['phase'];
        $toReturn->commander_id = $result['commander_id'];
        $commander = ModelBuildingHelper::BuildUserModel($result);
        $toReturn->commander = $commander->id == null ? null : $commander;
        return $toReturn;
    }
    
    public static function BuildZoneModel($result)
    {
        $toReturn = new ZoneModel();
        $toReturn->id = $result['zone_id'];
        $toReturn->conquest_id = $result['conquest_id'];
        $conquest = ModelBuildingHelper::BuildConquestModel($result);
        $toReturn->conquest = $conquest;
        $toReturn->zone = $result['zone'];
        $toReturn->battle_count = $result['battle_count'];
        $toReturn->is_owned = $result['is_owned'];
        return $toReturn;
    }
    
    public static function BuildNodeModel($result)
    {
        $toReturn = new NodeModel();
        $toReturn->id = $result['node_id'];
        $toReturn->zone_id = $result['zone_id'];
        $zone = ModelBuildingHelper::BuildZoneModel($result);
        $toReturn->zone = $zone;
        $toReturn->node = $result['node'];
        $toReturn->is_reserved = $result['is_reserved'];
        return $toReturn;
    }
}
