<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\slack;

/**
 * Description of DebugSlackApi
 *
 * @author chris
 */
class NullSlackApi implements ISlackApi
{
    private $PostMessageApiUri = 'https://slack.com/api/chat.postMessage';
    private $UpdateMessageApiUri = 'https://slack.com/api/chat.update';
    private $GroupHistoryApiUri = 'https://slack.com/api/channels.history';
    private $TopicApiUri = 'https://slack.com/api/channels.setTopic';
    private $CheckPresenceUri = 'https://slack.com/api/users.getPresence';
    private $DeleteMessageApiUri = 'https://slack.com/api/chat.delete';

    public function SendMessage($message, $attachments = null,
                                $channel = 'test2')
    {
        $queryString = "token=" . \Config::$BotUserOAuthToken;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&as_user=" . "true";
        $queryString .= "&text=" . urlencode($message);
        if ($attachments != null)
        {
            $queryString .= "&attachments=" . urlencode(json_encode($attachments));
        }
        $uri = $this->PostMessageApiUri . "?" . $queryString;
        var_dump($uri);
        return null;
    }

    public function UpdateMessage($ts, $channel, $message, $attachments = [])
    {
        $queryString = "token=" . \Config::$BotUserOAuthToken;
        $queryString .= "&ts=" . $ts;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&as_user=" . "true";
        $queryString .= "&text=" . urlencode($message);
        $queryString .= "&attachments=" . urlencode(json_encode($attachments));
        $uri = $this->UpdateMessageApiUri . "?" . $queryString;
        var_dump($uri);
        return null;
    }

    public function GetGroupMessagesSince($ts, $channel)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&oldest=" . $ts;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&count=3";
        $uri = $this->GroupHistoryApiUri . "?" . $queryString;
        var_dump($uri);
        return null;
    }

    public function SetTopic($topic, $channel)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&channel=$channel";
        $queryString .= "&topic=" . urlencode($topic);
        $uri = $this->TopicApiUri . "?" . $queryString;
        var_dump($uri);
        return null;
    }

    public function CheckPresence($user)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&user=" . $user;
        $uri = $this->CheckPresenceUri . "?" . $queryString;
        var_dump($uri);
        return null;
    }

    public function DeleteMessage($timestamp, $channel)
    {
        $queryString = "token=" . \Config::$BotUserOAuthToken;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&ts=" . $timestamp;
        $uri = $this->DeleteMessageApiUri . "?" . $queryString;
        var_dump($uri);
        return null;
    }

    public function GetFileList($channel = null, $page = 1, $ts_from = 0,
                                $ts_to = 'now', $types = 'all', $count = 100,
                                $user = null)
    {
        return null;
    }

    public function DeleteFile($file)
    {
        return null;
    }

    public function AddReaction($ts, $channel, $reaction)
    {
        return null;
    }

}
