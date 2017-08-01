<?php

namespace framework\command;

use framework\slack\ISlackApi;
use dal\managers\CoreRepository;
use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\StrikeRepository;
use framework\command\StatusCommandStrategy;
use StateEnum;

/**
 * Description of StrikeCommandStrategy
 *
 * @author chris
 */
class StrikeCommandStrategy implements ICommandStrategy
{
    const Regex = '/(setup|start) (zone)/i';

    private $zoneRegex = '/(?:zone) (\d{1,2})/i';
    private $holdRegex = '/(?:hold)(?: on)?(?: node)? (\d{1,2})/i';
    private $coreRepository;
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $slackApi;
    private $statusCommandStrategy;
    private $response;
    private $channel;
    private $eventData;

    public function __construct(CoreRepository $coreRepository,
                                ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                NodeRepository $nodeRepository,
                                StrikeRepository $strikeRepository,
                                ISlackApi $slackApi,
                                StatusCommandStrategy $statusCommandStrategy)
    {
        $this->slackApi = $slackApi;

        $this->coreRepository = $coreRepository;
        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->nodeRepository = $nodeRepository;
        $this->strikeRepository = $strikeRepository;

        $this->statusCommandStrategy = $statusCommandStrategy;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(StrikeCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $this->channel = $payload['channel'];
        $data = $payload['text'];

        $matches = [];
        if (preg_match($this->zoneRegex, $data, $matches))
        {
            $zone = $matches[1];
        }
        else
        {
            $this->response = "I can only setup a strike map if you tell me what zone!  Hint: zone {number}";
            return;
        }
        $hold = null;
        if (preg_match($this->holdRegex, $data, $matches))
        {
            $hold = $matches[1];
        }

        $conquest = $this->conquestRepository->GetCurrentConquest();
        $check = $this->zoneRepository->GetZone($conquest, $zone);
        if ($check != null && !$check->is_owned)
        {
            $this->response = "Zone *$zone* has not yet been completed/removed.  Please mark it as done or lost before trying again.\n" .
                    "Hint: zone # (done|lost)";
            return;
        }

        $state = $this->coreRepository->GetState();
        $isTraining = $state == StateEnum::Training;
        $this->zoneRepository->CreateZone($conquest, $zone, $isTraining);
        $zone = $this->zoneRepository->GetZone($conquest, $zone);
        $this->CreateNodes($zone, $hold);
        $nodes = $this->nodeRepository->GetAllNodes($zone);
        $this->CreateStrikes($nodes);
        $this->response = $isTraining ? "Training zone " . $zone->zone . " has been setup"
                    : "Strike map has been setup for zone " . $zone->zone;
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null, $this->channel);

        $this->statusCommandStrategy->Process($this->eventData);
        $this->statusCommandStrategy->SendResponse();
    }

    private function CreateNodes($zone, $hold)
    {
        for ($i = 1; $i <= 10; $i++)
        {
            $this->nodeRepository->CreateNode($zone, $i, $hold == $i ? 1 : 0);
        }
    }

    private function CreateStrikes($nodes)
    {
        foreach ($nodes as $node)
        {
            $this->strikeRepository->CreateStrike($node);
        }
    }

}
