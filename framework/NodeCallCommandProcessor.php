<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;

use framework\slack\SlackApi;
use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\StrikeRepository;
use dal\managers\UserRepository;

/**
 * Description of NodeCallCommandProcessor
 *
 * @author chris
 */
class NodeCallCommandProcessor implements ICommandProcessor
{
    private $eventData;
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $userRepository;
    private $slackApi;
    private $response;
    private $NodeCallRegex = '/^(\d{1,2})(?:\.|-)(\d{1,2})$|^(\d{1,2})(?:\.|-)(\d{1,2})((?:\s<@)([A-Z0-9]+)(?:>))$/i';

    public function __construct($data)
    {
        $this->eventData = $data;
        $this->slackApi = new SlackApi();

        $this->conquestRepository = new ConquestRepository();
        $this->zoneRepository = new ZoneRepository();
        $this->nodeRepository = new NodeRepository();
        $this->strikeRepository = new StrikeRepository();
        $this->userRepository = new UserRepository();
    }

    public function Process()
    {
        $data = $this->eventData['text'];
        $matches = [];
        if (!preg_match($this->NodeCallRegex, $data, $matches))
        {
            $this->response = 'Check your syntax!  Hint: 4.5 or 4.5 @christopher';
            return;
        }
        
        $offset = 0;
        if (sizeof($matches) >= 6)
        {
            $offset = 2;
            $userValue = $matches[6];    
        }
        else 
        {
            $userValue = $this->eventData['user'];
        }
        
        $zoneValue = $matches[1+$offset];
        $nodeValue = $matches[2+$offset];        
        $user = $this->userRepository->GetUserById($userValue);
        if ($user == null)
        {
            $this->response = "Sorry, I couldn't find <@$userValue> registered with me";
            return;
        }
        
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zone = $this->zoneRepository->GetZone($conquest, $zoneValue);
        if ($zone == null || $zone->is_owned)
        {
            $this->response = "That zone (*$zoneValue*) is no longer active, please double check your call!";
            return;
        }

        $node = $this->nodeRepository->GetNode($zone, $nodeValue);       
        $currentStrike = $this->strikeRepository->GetStrike($node);
        if ($currentStrike->user_id != null)
        {
            $this->response = "<@" . $this->eventData['user'] . ">: " .
                    "$zoneValue.$nodeValue is already assigned to <@" .
                    $currentStrike->user->name . ">!" .
                    "  Please call another target!";
            return;
        }
        $this->strikeRepository->UpdateStrike($node, $user);
    }

    public function SendResponse()
    {
        if ($this->response != null)
        {
            $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
        } else
        {
            $statusCommandProcessor = new StatusCommandProcessor($this->eventData);
            $statusCommandProcessor->Process();
            $statusCommandProcessor->SendResponse();
        }
    }
}
