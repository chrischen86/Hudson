<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;
use framework\conquest\ConquestManager;
use Config;
/**
 * Description of CommandStrategyFactory
 *
 * @author chris
 */
class ReactionProcessor
{
    private $conquestManager;
    
    public function __construct(ConquestManager $conquestManager)
    {
        $this->conquestManager = $conquestManager;
    }
    
    public function Process($data)
    {
        $botRegex = '/(' . Config::$BotId . '|' . Config::$BotName . ')/i';
        $type = $data['type'];
        if (!($type == 'reaction_added' || $type == 'reaction_removed') || $data['item_user'] != Config::$BotId)
        {
            return null;
        }
        
        if (preg_match($botRegex, $data['user']))
        {
            return;
        }
        
        $item = $data['item'];
        if ($type == 'reaction_added')
        {
            $consensus = $this->conquestManager->ReactionAdded($item['ts'], $data['reaction']);
            if ($consensus != null && $consensus->votes >= 1)
            {
                $this->conquestManager->SetupZone($consensus->zone, null);
            }
        }
        else 
        {
            $this->conquestManager->ReactionRemoved($item['ts'], $data['reaction']);
        }
    }
}
