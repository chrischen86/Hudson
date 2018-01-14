<?php

namespace framework\command;

use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\ConquestRepository;
use framework\slack\ISlackApi;
use framework\command\StatusCommandStrategy;

/**
 * Description of HoldCommandStrategy
 *
 * @author chris
 */
class HoldCommandStrategy implements ICommandStrategy
{
    const Regex = '/(?:hold) (\d{1,2})(\.|-)(\d{1,2})( off)?/i';

    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $slackApi;
    private $response;
    private $statusCommandStrategy;
    private $eventData;

    public function __construct(ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                NodeRepository $nodeRepository,
                                ISlackApi $slackApi,
                                StatusCommandStrategy $statusCommandStrategy)
    {
        $this->slackApi = $slackApi;

        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->nodeRepository = $nodeRepository;

        $this->statusCommandStrategy = $statusCommandStrategy;
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(HoldCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        $matches = [];
        if (!preg_match(HoldCommandStrategy::Regex, $data, $matches))
        {
            $this->response = 'Check your syntax!  Hint: hold 1.7';
            return;
        }
        $zoneValue = $matches[1];
        $nodeValue = $matches[3];
        
        $isReserved = sizeof($matches) <= 4;
        
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zone = $this->zoneRepository->GetZone($conquest, $zoneValue);
        $node = $this->nodeRepository->GetNode($zone, $nodeValue);
        $node->is_reserved = $isReserved;
        $this->nodeRepository->UpdateNode($node);
    }

    public function SendResponse()
    {
        if ($this->response != null)
        {
            $this->slackApi->SendMessage($this->response, null,
                    $this->eventData['channel']);
        }
        else
        {
            $this->statusCommandStrategy->Process($this->eventData);
            $this->statusCommandStrategy->SendResponse();
        }
        
        unset($this->response);
        unset($this->eventData);
    }

}
