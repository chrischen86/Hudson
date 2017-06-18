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
class SlackApi {
    private $PostMessageApiUri = 'https://slack.com/api/chat.postMessage';
    private $UpdateMessageApiUri = 'https://slack.com/api/chat.update';
    private $GroupHistoryApiUri = 'https://slack.com/api/channels.history';
    
    public function SendMessage($message, $attachments=null, $channel='test2')
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
    
    public function UpdateMessage($ts, $channel, $message, $attachments=[])
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
    
    public function GetMessagesSince($ts, $channel)
    {
        
    }
    
    public function DeleteMessage()
    {
        
    }
}
