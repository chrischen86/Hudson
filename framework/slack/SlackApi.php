<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\slack;

/**
 * Description of SlackApi
 *
 * @author chris
 */
class SlackApi implements ISlackApi
{
    private $PostMessageApiUri = 'https://slack.com/api/chat.postMessage';
    private $UpdateMessageApiUri = 'https://slack.com/api/chat.update';
    private $GroupHistoryApiUri = 'https://slack.com/api/channels.history';
    private $TopicApiUri = 'https://slack.com/api/channels.setTopic';
    private $CheckPresenceUri = 'https://slack.com/api/users.getPresence';
    private $DeleteMessageApiUri = 'https://slack.com/api/chat.delete';
    private $FileListApiUri = 'https://slack.com/api/files.list';
    private $FileDeleteApiUri = 'https://slack.com/api/files.delete';
    private $AddReactionsApiUri = 'https://slack.com/api/reactions.add';

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
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->body($message)
                ->send();

        return $response;
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
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->body($message)
                ->send();

        return $response;
    }

    public function GetGroupMessagesSince($ts, $channel)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&oldest=" . $ts;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&count=3";
        $uri = $this->GroupHistoryApiUri . "?" . $queryString;
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();

        return $response;
    }

    public function SetTopic($topic, $channel)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&channel=$channel";
        $queryString .= "&topic=" . urlencode($topic);
        $uri = $this->TopicApiUri . "?" . $queryString;
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();

        return $response;
    }

    public function CheckPresence($user)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&user=" . $user;
        $uri = $this->CheckPresenceUri . "?" . $queryString;
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();

        return $response;
    }

    public function GetMessagesSince($ts, $channel)
    {
        
    }

    public function DeleteMessage($timestamp, $channel)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&ts=" . $timestamp;
        $uri = $this->DeleteMessageApiUri . "?" . $queryString;
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();
        return $response;
    }

    public function GetFileList($channel = null, $page = 1, $ts_from = 0,
                                $ts_to = 'now', $types = 'all', $count = 100,
                                $user = null)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        if ($channel != null)
        {
            $queryString .= "&channel=" . $channel;
        }
        $queryString .= "&page=" . $page;
        $queryString .= "&ts_from=" . $ts_from;
        $queryString .= "&ts_to=" . $ts_to;
        $queryString .= "&types=" . $types;
        $queryString .= "&count=" . $count;
        if ($user != null)
        {
            $queryString .= "&user=" . $user;
        }

        $uri = $this->FileListApiUri . "?" . $queryString;
        $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();

        return $response;
    }

    public function DeleteFile($file)
    {
        $queryString = "token=" . \Config::$BotOAuthToken;
        $queryString .= "&file=" . $file;

        $uri = $this->FileDeleteApiUri . "?" . $queryString;
        $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();

        return $response;
    }

    public function AddReaction($ts, $channel, $reaction)
    {
        $queryString = "token=" . \Config::$BotUserOAuthToken;
        $queryString .= "&channel=" . $channel;
        $queryString .= "&timestamp=" . $ts;
        $queryString .= "&name=" . $reaction;
        $uri = $this->AddReactionsApiUri . "?" . $queryString;
        $response = \Httpful\Request::post($uri)
                ->addHeader('Content-Type', 'text/plain; charset=utf-8')
                ->send();
        return $response;
    }

}
