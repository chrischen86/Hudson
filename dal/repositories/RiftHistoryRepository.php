<?php

namespace dal\managers;

use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;
use dal\models\UserModel;
use dal\models\RiftHistoryModel;

class RiftHistoryRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function GetRiftHistoryByUser(UserModel $user)
    {
        $sql = 'SELECT h.id AS rift_history_id, h.owner_id, h.type_id, h.scheduled_time, ' .
                'u.id AS user_id, u.name, u.vip, u.is_archived ' .
                'r.id AS rift_type_id, r.name, r.thumbnail ' .
                'FROM rift_history h ' .
                'INNER JOIN users u ' .
                'INNER JOIN rift_type r ON r.id = h.type_id ' .
                "WHERE owner_id = '" . $user->id . "'";
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

}
