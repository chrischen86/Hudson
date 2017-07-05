<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\command;
use Symfony\Component\HttpFoundation\Request;
use Config;
/**
 * Description of CommandStrategyFactory
 *
 * @author chris
 */
class CommandStrategyFactory
{
    private $strategies;
    
    public function __construct($strategies)
    {
        $this->strategies = $strategies;
    }
    
    public function GetCommandStrategy(Request $request)
    {
        $botRegex = '/(' . Config::$BotId . '|' . Config::$BotName . ')/i';
        $data = json_decode($request->getContent(), true);
        $event = $data['event'];
        if ($event['type'] != 'message' || $event['subtype'] == 'message_changed')
        {
            return null;
        }
        
        if (preg_match($botRegex, $event['user']))
        {
            return;
        }
        
        $text = $event['text'];
        $isJarvisCommand = preg_match($botRegex, $text);
        foreach ($this->strategies as $strategy)
        {
            if ($strategy->IsSupportedRequest($text))
            {
                if ($strategy->IsJarvisCommand() && !$isJarvisCommand)
                {
                    return;
                }
                return $strategy;
            }
        }
    }
}
