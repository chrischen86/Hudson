<?php
namespace framework\slack;

/**
 *
 * @author chris
 */
interface ISlackApi
{
    public function SendMessage($message, $attachments = null, $channel = 'test2');
    public function UpdateMessage($ts, $channel, $message, $attachments = []);
    public function GetGroupMessagesSince($ts, $channel);
    public function SetTopic($topic, $channel);
    public function CheckPresence($user);
}
