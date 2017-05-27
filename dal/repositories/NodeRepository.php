<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace dal\managers;
use dal\models\ZoneModel;
use dal\DataAccessAdapter;
use dal\ModelBuildingHelper;
/**
 * Description of NodeRepository
 *
 * @author chris
 */
class NodeRepository {
    private $adapter;
    
    public function __construct() {
        $this->adapter = new DataAccessAdapter();
    }
    
    public function GetNode(ZoneModel $zone, $nodeNumber)
    {
        $sql = 'SELECT n.id as node_id, n.zone_id, n.node, n.is_reserved, ' .
                    'z.conquest_id, z.zone, z.battle_count, z.is_owned, ' .
                    'c.commander_id, c.date, c.phase, ' .
                    'u.id as user_id, u.name, u.vip ' .
                'FROM conquest_nodes n ' . 
                'INNER JOIN conquest_zones z ON z.id = n.zone_id ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                'WHERE n.zone_id = ' . $zone->id . ' ' .
                'AND n.node = ' . $nodeNumber;
        $result = $this->adapter->query_single($sql);
        $node = ModelBuildingHelper::BuildNodeModel($result);
        
        echo '<pre>' . print_r($node, 1) . '</pre>';
        return $node;
    }
    
    public function GetAllNodes(ZoneModel $zone)
    {
        
    }
    
    public function CreateNode(ZoneModel $zone, $nodeNumber)
    {
        $sql = 'INSERT INTO conquest_nodes (zone_id, node) ' .
                'VALUES (' . $zone->id . ', ' . $nodeNumber . ')';
        $this->adapter->query($sql);
    }
}
