<?php

namespace framework\system;
use Httpful\Response;
use dal\models\SlackMessageModel;
/**
 * Description of SlackMessageHistoryHelper
 *
 * @author chris
 */
class SlackMessageHistoryHelper
{
    public static function ParseResponse(Response $response)
    {
        $toReturn = new SlackMessageModel();
        $body = $response->body;
        if (!isset($body->message))
        {
            return $toReturn;
        }
        
        $toReturn->message_type = $body->message->type;
        $toReturn->channel = $body->channel;
        $toReturn->ts = $body->ts;
        $toReturn->user_id = $body->message->user;
        $toReturn->bot_id = $body->message->bot_id;
        $toReturn->text = $body->message->text;
        $toReturn->attachments = json_encode($body->message->attachments);
        
        return $toReturn;
    }
}
