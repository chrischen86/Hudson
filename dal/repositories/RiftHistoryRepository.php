<?php

namespace dal\repositories;

use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;
use dal\models\UserModel;
use dal\models\RiftHistoryModel;
use dal\ISqlSpecification;

class RiftHistoryRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    public function query(ISqlSpecification $specification)
    {
        $sql = $specification->toSqlQuery();
        $results = $this->adapter->query($sql);
        $toReturn = [];
        foreach ($results as $item)
        {
            $history = ModelBuildingHelper::BuildRiftHistoryModel($item);
            array_push($toReturn, $history);
        }
        return $toReturn;
    }

    public function GetRiftHistoryByUser(UserModel $user)
    {
        $sql = 'SELECT h.id AS rift_history_id, h.owner_id, h.type_id, h.scheduled_time, h.is_deleted, ' .
                'u.id AS user_id, u.name, u.vip, u.is_archived, ' .
                'r.id AS rift_type_id, r.name, r.thumbnail ' .
                'FROM rift_history h ' .
                'INNER JOIN users u ' .
                'INNER JOIN rift_type r ON r.id = h.type_id ' .
                "WHERE owner_id = '" . $user->id . "'" .
                'ORDER BY rift_history_id';
        $results = $this->adapter->query($sql);
        $toReturn = [];
        foreach ($results as $item)
        {
            $history = ModelBuildingHelper::BuildRiftHistoryModel($item);
            array_push($toReturn, $history);
        }
        return $toReturn;
    }

    public function CreateRiftHistory(RiftHistoryModel $history)
    {
        $owner = $history->owner_id;
        $type = empty($history->type_id) ? 'NULL' : $history->type_id;
        $time = $history->scheduled_time->format('Y-m-d H:i:s');
        $slack_message_id = empty($history->slack_message_id) ? 'NULL' : $history->slack_message_id;
        $sql = 'INSERT INTO rift_history(owner_id, type_id, scheduled_time, slack_message_id) ' .
                "VALUES('$owner', $type, '$time', $slack_message_id)";

        $id = $this->adapter->query($sql);
        return $id;
    }

    public function SetIsDeletedOnRiftHistory($id, $isDeleted)
    {
        $sql = "UPDATE rift_history " .
                "SET is_deleted = '$isDeleted' " .
                "WHERE id = '$id'";

        $this->adapter->query($sql);
        return $id;
    }

    public function GetCancellableRiftsByUser(UserModel $user)
    {
        //A rift is cancellable if is_deleted = false 
        //and the create time (scheduled_time) is within the last hour
        $sql = 'SELECT h.id AS rift_history_id, h.owner_id, h.type_id, h.scheduled_time, h.is_deleted, h.slack_message_id, ' .
                'u.id AS user_id, u.name, u.vip, u.is_archived, ' .
                'r.id AS rift_type_id, r.name, r.thumbnail, ' .
                's.id AS slack_message_history_id, s.ts AS slack_message_history_ts, s.channel as slack_message_history_channel ' .
                'FROM rift_history h ' .
                'INNER JOIN users u ON u.id = h.owner_id ' .
                'LEFT JOIN rift_type r ON r.id = h.type_id ' .
                'INNER JOIN slack_message_history s ON s.id = h.slack_message_id ' .
                "WHERE owner_id = '" . $user->id . "' " .
                "AND h.is_deleted = 0 " .
                "AND h.scheduled_time > DATE_ADD(NOW(), INTERVAL -1 hour) " .
                'ORDER BY h.id DESC';
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }
        foreach ($results as $item)
        {
            $history = ModelBuildingHelper::BuildRiftHistoryModel($item);
            array_push($toReturn, $history);
        }
        return $toReturn;
    }
}
