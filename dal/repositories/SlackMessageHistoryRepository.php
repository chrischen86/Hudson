<?php

namespace dal\managers;

use dal\IDataAccessAdapter;
use dal\models\SlackMessageModel;

class SlackMessageHistoryRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function CreateSlackMessageHistory(SlackMessageModel $message)
    {
        $messageType = $message->message_type;
        $userId = $message->user_id;
        $text = $message->text;
        $botId = $message->bot_id;
        $attachmentJson = $message->attachments;
        $ts = $message->ts;
        $channel = $message->channel;
        $sql = 'INSERT INTO slack_message_history(message_type, user_id, text, bot_id, attachments_json, ts, channel) ' .
                "VALUES('$messageType', '$userId', '$text', '$botId', '$attachmentJson', '$ts', '$channel')";

        $id = $this->adapter->query($sql);
        return $id;
    }

}
