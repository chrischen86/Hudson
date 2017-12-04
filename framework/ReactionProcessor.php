<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;

use framework\conquest\ConquestManager;
use framework\command\StatusCommandStrategy;
use framework\slack\ISlackApi;
use Config;

/**
 * Description of CommandStrategyFactory
 *
 * @author chris
 */
class ReactionProcessor
{
    private $conquestManager;
    private $statusCommandStrategy;
    private $slackApi;

    public function __construct(ConquestManager $conquestManager,
                                StatusCommandStrategy $statusCommandStrategy,
                                ISlackApi $slackApi)
    {
        $this->conquestManager = $conquestManager;
        $this->statusCommandStrategy = $statusCommandStrategy;

        $this->slackApi = $slackApi;
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
                $payload = array('channel' => $item['channel']);

                $this->slackApi->SendMessage("Enough votes has been achieved, setting up zone *" . $consensus->zone . "*", null, $item['channel']);
                $this->slackApi->DeleteMessage($item['ts'], $item['channel']);
                $this->statusCommandStrategy->Process($payload);
                $this->statusCommandStrategy->SendResponse();
            }
        }
        else
        {
            $this->conquestManager->ReactionRemoved($item['ts'], $data['reaction']);
        }
    }

}
