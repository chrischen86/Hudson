<?php

namespace dal\managers;

use dal\models\ZoneModel;
use dal\models\NodeModel;
use dal\models\ConquestModel;
use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;

/**
 * Description of NodeRepository
 *
 * @author chris
 */
class NodeRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function UpdateNode(NodeModel $node)
    {
        $sql = 'UPDATE conquest_nodes ' .
                'SET is_reserved = ' . ($node->is_reserved ? 1 : 0) . ' ' .
                'WHERE id = ' . $node->id;
        $this->adapter->query($sql);
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
        return $node;
    }

    public function GetAllNodes(ZoneModel $zone)
    {
        $sql = 'SELECT n.id as node_id, n.zone_id, n.node, n.is_reserved, ' .
                'z.conquest_id, z.zone, z.battle_count, z.is_owned, ' .
                'c.commander_id, c.date, c.phase, ' .
                'u.id as user_id, u.name, u.vip ' .
                'FROM conquest_nodes n ' .
                'INNER JOIN conquest_zones z ON z.id = n.zone_id ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                'WHERE n.zone_id = ' . $zone->id;
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }
        foreach ($results as $item)
        {
            $node = ModelBuildingHelper::BuildNodeModel($item);
            array_push($toReturn, $node);
        }
        return $toReturn;
    }

    public function GetAllNodesByConquest(ConquestModel $conquest)
    {
        $sql = 'SELECT n.id as node_id, n.zone_id, n.node, n.is_reserved, ' .
                'z.conquest_id, z.zone, z.battle_count, z.is_owned, ' .
                'c.commander_id, c.date, c.phase, ' .
                'u.id as user_id, u.name, u.vip ' .
                'FROM conquest_nodes n ' .
                'INNER JOIN conquest_zones z ON z.id = n.zone_id ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                'WHERE c.id = ' . $conquest->id . ' ' .
                'AND z.is_training = 0';
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }
        foreach ($results as $item)
        {
            $node = ModelBuildingHelper::BuildNodeModel($item);
            array_push($toReturn, $node);
        }
        return $toReturn;
    }

    public function CreateNode(ZoneModel $zone, $nodeNumber, $isReserved = 0)
    {
        $sql = 'INSERT INTO conquest_nodes (zone_id, node, is_reserved) ' .
                'VALUES (' . $zone->id . ', ' . $nodeNumber . ', ' . $isReserved . ')';
        $this->adapter->query($sql);
    }

}
