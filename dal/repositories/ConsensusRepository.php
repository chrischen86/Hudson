<?php

namespace dal\managers;

use dal\models\ConquestModel;
use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;
use dal\models\ConsensusModel;

/**
 * Description of ConsensusRepository
 *
 * @author chris
 */
class ConsensusRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function SetConsensusTimestamp(ConquestModel $conquest, $zone,
                                          $timestamp)
    {
        $sql = 'UPDATE conquest_consensus ' .
                'SET message_ts = ' . $timestamp . ' ' .
                'WHERE zone = ' . $zone . ' ' .
                'AND conquest_id = ' . $conquest->id;
        $this->adapter->query($sql);
    }

    public function UpdateConsensus(ConsensusModel $consensus)
    {
        $sql = 'UPDATE conquest_consensus ' .
                'SET votes = ' . $consensus->votes . ', vetoes = ' . $consensus->vetoes . ' ' .
                'WHERE zone = ' . $consensus->zone . ' ' .
                'AND conquest_id = ' . $consensus->conquest_id;
        $this->adapter->query($sql);
    }

    public function GetAllConsensusByConquest(ConquestModel $conquest)
    {
        $sql = 'SELECT z.id as zone_id, z.conquest_id, z.zone, z.votes, z.vetoes, z.message_ts, ' .
                'c.date, c.phase, c.commander_id ' .
                'FROM conquest_consensus z ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'WHERE conquest_id = ' . $conquest->id . ' ';
        $this->adapter->query($sql);
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }

        foreach ($results as $item)
        {
            $consensus = ModelBuildingHelper::BuildConsensusModel($item);
            array_push($toReturn, $consensus);
        }
        return $toReturn;
    }

    public function GetConsensus(ConquestModel $conquest, $zone)
    {
        $sql = 'SELECT z.id as zone_id, z.conquest_id, z.zone, z.votes, z.vetoes, z.message_ts ' .
                'FROM conquest_consensus z ' .
                'INNER JOIN conquest c ON c.id = z.conquest_id ' .
                'WHERE conquest_id = ' . $conquest->id . ' ' .
                'AND zone = ' . $zone . ' ';
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            return null;
        }
        $consensus = ModelBuildingHelper::BuildConsensusModel($result);
        return $consensus;
    }

    public function GetConsensusByTimestamp($timestamp)
    {
        $sql = 'SELECT z.id as zone_id, z.conquest_id, z.zone, z.votes, z.vetoes, z.message_ts ' .
                'FROM conquest_consensus z ' .
                "WHERE z.message_ts = '" . $timestamp . "'";
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            return null;
        }
        $consensus = ModelBuildingHelper::BuildConsensusModel($result);
        return $consensus;
    }

    public function CreateConsensus(ConquestModel $conquest, $zone)
    {
        $currentZone = $this->GetConsensus($conquest, $zone);
        if ($currentZone != null)
        {
            return null;
        }
        $sql = 'INSERT INTO conquest_consensus (conquest_id, zone, votes, vetoes) ' .
                'VALUES (' . $conquest->id . ', ' . $zone . ', 0, 0)';
        $this->adapter->query($sql);
        return 1;
    }

    public function DeleteConsensus(ConquestModel $conquest, $zone)
    {
        $sql = 'DELETE FROM conquest_consensus ' .
                'WHERE zone = ' . $zone . ' ' .
                'AND conquest_id = ' . $conquest->id . ' ' .
                'AND is_owned = 0';
        $this->adapter->query($sql);
    }

}
