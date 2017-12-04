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
use dal\models\StrikeModel;
use dal\models\CoreModel;
use dal\models\ConsensusModel;
use DateTime;
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
        $toReturn->is_archived = $result['is_archived'];
        return $toReturn;
    }
    
    public static function BuildConquestModel($result)
    {
        $toReturn = new ConquestModel();
        $toReturn->id = $result['conquest_id'];
        $toReturn->date = new DateTime($result['date']);
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
        $toReturn->is_training = $result['is_training'];
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
    
    public static function BuildStrikeModel($result)
    {
        $toReturn = new StrikeModel();
        $toReturn->id = $result['strike_id'];
        $toReturn->node_id = $result['strike_node_id'];
        $node = ModelBuildingHelper::BuildNodeModel($result);
        $toReturn->node = $node;
        $toReturn->user_id = $result['strike_user_id'];
        $user = ModelBuildingHelper::BuildStrikeUserModel($result);
        $toReturn->user = $user->id == null ? null : $user;
        $toReturn->status = $result['status'];
        return $toReturn;
    }
    
    private static function BuildStrikeUserModel($result)
    {
        $toReturn = new UserModel();
        $toReturn->id = $result['a_user_id'];
        $toReturn->name = $result['a_name'];
        $toReturn->vip = $result['a_vip'];
        return $toReturn;
    }
    
    public static function BuildCoreModel($result)
    {
        $toReturn = new CoreModel();
        $toReturn->state = $result['state'];
        $toReturn->message_channel = $result['message_channel'];
        $toReturn->message_ts = $result['message_ts'];               
        return $toReturn;        
    }
    
    public static function BuildConsensusModel($result)
    {
        $toReturn = new ConsensusModel();
        $toReturn->id = $result['zone_id'];
        $toReturn->conquest_id = $result['conquest_id'];
        $conquest = ModelBuildingHelper::BuildConquestModel($result);
        $toReturn->conquest = $conquest;
        $toReturn->zone = $result['zone'];
        $toReturn->votes = $result['votes'];
        $toReturn->vetoes = $result['vetoes'];
        return $toReturn;
    }
}
