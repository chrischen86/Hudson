<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;
use dal\managers\ZoneRepository;
use dal\managers\ConquestRepository;
use framework\slack\SlackApi;


/**
 * Description of CancelCommandProcessor
 *
 * @author chris
 */
class CancelCommandProcessor implements ICommandProcessor {
    private $eventData;
    private $conquestRepository;
    private $zoneRepository;
    private $slackApi;
    
    private $response;
    private $CancelRegex = '/(?:cancel)(?: zone)? (\d{1,2})/i';
    
    public function __construct($data) {
        $this->eventData = $data;        
        $this->slackApi = new SlackApi();
        
        $this->conquestRepository = new ConquestRepository();
        $this->zoneRepository = new ZoneRepository();
    }
    
    public function Process() 
    {
        $data = $this->eventData['text'];
        $matches = [];
        if (!preg_match($this->CancelRegex, $data, $matches))
        {
            $this->response = 'Check your syntax!  Hint: cancel 1 or cancel zone 1';
            return;
        }
        $zoneValue = $matches[1];
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zone = $this->zoneRepository->GetZone($conquest, $zoneValue);
        
        if ($zone->is_owned)
        {
            $this->response = "You are attempting to remove zone *$zoneValue* that is already marked as completed!";
            return;
        }
        
        $this->zoneRepository->DeleteZone($conquest, $zoneValue);
        $this->response = "Zone *$zoneValue* and all related nodes and strikes have been removed!";
    }

    public function SendResponse() 
    {
        $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);
        $statusCommandProcessor = new StatusCommandProcessor($this->eventData);
        $statusCommandProcessor->Process();
        $statusCommandProcessor->SendResponse(); 
    }
}
