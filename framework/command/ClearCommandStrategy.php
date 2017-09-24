<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\command;

use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\ConquestRepository;
use dal\managers\StrikeRepository;
use framework\slack\ISlackApi;
use framework\command\StatusCommandStrategy;

/**
 * Description of ClearCommandStrategy
 *
 * @author chris
 */
class ClearCommandStrategy implements ICommandStrategy
{
    const Regex = '/(clear) (\d{1,2})(\.|-)(\d{1,2})/i';

    private $ClearRegex = '/(?:clear) (\d{1,2})(\.|-)(\d{1,2})/i';
    private $eventData;
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $slackApi;
    private $response;
    private $statusCommandStrategy;

    public function __construct(ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                NodeRepository $nodeRepository,
                                StrikeRepository $strikeRepository,
                                ISlackApi $slackApi,
                                StatusCommandStrategy $statusCommandStrategy)
    {
        $this->slackApi = $slackApi;

        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->nodeRepository = $nodeRepository;
        $this->strikeRepository = $strikeRepository;
        
        $this->statusCommandStrategy = $statusCommandStrategy;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(ClearCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        $matches = [];
        if (!preg_match($this->ClearRegex, $data, $matches))
        {
            $this->response = 'Check your syntax!  Hint: clear 1.7';
            return;
        }
        $zoneValue = $matches[1];
        $nodeValue = $matches[3];
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zone = $this->zoneRepository->GetZone($conquest, $zoneValue);
        $node = $this->nodeRepository->GetNode($zone, $nodeValue);
        $strike = $this->strikeRepository->GetStrike($node);
        $this->strikeRepository->ClearStrike($strike);
    }   

    public function SendResponse()
    {
        $this->statusCommandStrategy->Process($this->eventData);
        $this->statusCommandStrategy->SendResponse();
        
        unset($this->response);
        unset($this->eventData);
    }

}
