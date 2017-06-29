<?php

namespace dal\managers;

use dal\models\ConquestModel;
use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;

/**
 * Description of ZonesRepository
 *
 * @author chris
 */
class ZoneRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function UpdateZone(ConquestModel $conquest, $zone, $isCompleted = 0)
    {
        $sql = 'UPDATE conquest_zones ' .
                'SET is_owned = ' . $isCompleted . ' ' .
                'WHERE zone = ' . $zone . ' ' .
                'AND conquest_id = ' . $conquest->id;
        $this->adapter->query($sql);
    }

    public function GetAllZones(ConquestModel $conquest)
    {
        $sql = 'SELECT z.id as zone_id, z.conquest_id, z.zone, z.battle_count, z.is_owned, ' .
                'c.date, c.phase, c.commander_id, ' .
                'u.id as user_id, u.name, u.vip ' .
                'FROM conquest_zones z ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                'WHERE conquest_id = ' . $conquest->id . ' ' .
                'AND is_owned = 0';
        $this->adapter->query($sql);
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }

        foreach ($results as $item)
        {
            $strike = ModelBuildingHelper::BuildZoneModel($item);
            array_push($toReturn, $strike);
        }
        return $toReturn;
    }

    public function GetAllZonesByConquest(ConquestModel $conquest)
    {
        $sql = 'SELECT z.id as zone_id, z.conquest_id, z.zone, z.battle_count, z.is_owned, ' .
                'c.date, c.phase, c.commander_id ' .
                'FROM conquest_zones z ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'WHERE conquest_id = ' . $conquest->id;
        $this->adapter->query($sql);
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }

        foreach ($results as $item)
        {
            $strike = ModelBuildingHelper::BuildZoneModel($item);
            array_push($toReturn, $strike);
        }
        return $toReturn;
    }

    public function GetZone(ConquestModel $conquest, $zone)
    {
        $sql = 'SELECT z.id as zone_id, z.conquest_id, z.zone, z.battle_count, z.is_owned, ' .
                'c.date, c.phase, c.commander_id, ' .
                'u.id as user_id, u.name, u.vip ' .
                'FROM conquest_zones z ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                'WHERE conquest_id = ' . $conquest->id . ' ' .
                'AND zone = ' . $zone . ' ' .
                'ORDER BY battle_count DESC';
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            return null;
        }
        $zone = ModelBuildingHelper::BuildZoneModel($result);
        return $zone;
    }

    public function CreateZone(ConquestModel $conquest, $zone)
    {
        $battleCount = 1;
        $currentZone = $this->GetZone($conquest, $zone);
        if ($currentZone != null)
        {
            $battleCount = $currentZone->battle_count + 1;
            $this->UpdateZone($conquest, $zone, 1);
        }
        $sql = 'INSERT INTO conquest_zones (conquest_id, zone, battle_count, is_owned) ' .
                'VALUES (' . $conquest->id . ', ' . $zone . ', ' . $battleCount . ', 0)';
        $this->adapter->query($sql);
    }

    public function DeleteZone(ConquestModel $conquest, $zone)
    {
        $sql = 'DELETE FROM conquest_zones ' .
                'WHERE zone = ' . $zone . ' ' .
                'AND conquest_id = ' . $conquest->id . ' ' .
                'AND is_owned = 0';
        $this->adapter->query($sql);
    }

}
