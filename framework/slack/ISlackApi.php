<?php

namespace framework\slack;
use framework\slack\SlackMessage;
/**
 *
 * @author chris
 */
interface ISlackApi
{    
    public function SendMessage($message, $attachments = null,
                                $channel = 'test2', $asUser = 'true');
    public function SendEphemeral($message, $user, $channel = 'general', $attachments = null);
    public function UpdateMessage($ts, $channel, $message, $attachments = []);
    public function GetGroupMessagesSince($ts, $channel);
    public function SetTopic($topic, $channel);
    public function CheckPresence($user);
    public function DeleteMessage($timestamp, $channel);
    public function GetFileList($channel = null, $page = 1, $ts_from = 0,
                                $ts_to = 'now', $types = 'all', $count=100,
                                $user = null);
    public function DeleteFile($file);
    public function AddReaction($ts, $channel, $reaction);
    
    public function OpenDMChannel($user);
    public function SendSlackMessage(SlackMessage $message);
}
