<?php

namespace dal\managers;

use dal\IDataAccessAdapter;
use dal\models\CoreModel;
use StateEnum;

/**
 * Description of StateManager
 *
 * @author chris
 */
class CoreRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function GetMessageTimestamp()
    {
        $sql = 'SELECT message_ts FROM core';
        $result = $this->adapter->query_single($sql);
        return $result == null ? null : $result['message_ts'];
    }

    public function GetMessageChannel()
    {
        $sql = 'SELECT message_channel FROM core';
        $result = $this->adapter->query_single($sql);
        return $result == null ? null : $result['message_channel'];
    }

    public function SetMessageProperties($ts, $channel)
    {
        $sql = 'UPDATE core ' .
                "SET message_ts = '$ts', message_channel = '$channel'";
        $this->adapter->query($sql);
    }

    public function GetState()
    {
        $sql = 'SELECT state FROM core';
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            $this->initializeState();
        }
        $toReturn = new CoreModel();
        $toReturn->state = $result['state'];
        return $toReturn;
    }

    public function SetState($state)
    {
        $sql = 'UPDATE core SET state = ' . $state;
        $this->adapter->query($sql);
    }

    private function initializeState()
    {
        $sql = 'INSERT INTO core(id, state) VALUES(1,' . StateEnum::Sleeping . ')';
        $this->adapter->query($sql);
    }

}
