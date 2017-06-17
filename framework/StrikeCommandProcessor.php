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

/**
 * Description of SetupStrikeCommandProcessor
 *
 * @author chris
 */
class StrikeCommandProcessor implements ICommandProcessor{
    private $eventData;
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $slackApi;
    
    private $response;
    
    private $ZoneRegex = '/(?:zone) (\d{1,2})/i';
    private $HoldRegex = '/(?:hold)(?: on)?(?: node)? (\d{1,2})/i';
    
    public function __construct($data) {
        $this->eventData = $data;        
        $this->slackApi = new SlackApi();
        
        $this->conquestRepository = new ConquestRepository();
        $this->zoneRepository = new ZoneRepository();
        $this->nodeRepository = new NodeRepository();
        $this->strikeRepository = new StrikeRepository();
    }
    
    public function Process()
    {
        $data = $this->eventData['text'];
        
        $matches = [];
        if (preg_match($this->ZoneRegex, $data, $matches))
        {
            $zone = $matches[1];
        }
        else
        {
            $this->response = "I can only setup a strike map if you tell me what zone!  Hint: zone {number}";
            return;
        }
        if (preg_match($this->HoldRegex, $data, $matches))
        {
            $hold = $matches[1];
        }
        
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $check = $this->zoneRepository->GetZone($conquest, $zone);
        if ($check != null && !$check->is_owned)
        {
            $this->response = "Zone *$zone* has not yet been completed/removed.  Please mark it as done or lost before trying again.\n" .
                    "Hint: zone # (done|lost)";
        }       
        $this->zoneRepository->CreateZone($conquest, $zone);
        $zone = $this->zoneRepository->GetZone($conquest, $zone);
        $this->CreateNodes($zone, $hold);
        $nodes = $this->nodeRepository->GetAllNodes($zone);
        $this->CreateStrikes($nodes);
        $this->response = "Strike map has been setup for zone " . $zone->zone;
    }
    
    public function CreateNodes($zone, $hold)
    {
        for ($i=1; $i<=10; $i++)
        {
            $this->nodeRepository->CreateNode($zone, $i, $hold == $i ? 1:0);
        }
    }
    
    public function CreateStrikes($nodes)
    {
        foreach ($nodes as $node)
        {
            $this->strikeRepository->CreateStrike($node);
        }        
    }
    
    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
    }
}
