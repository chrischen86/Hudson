<?php

namespace framework\command;

use framework\slack\ISlackApi;
use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\StrikeRepository;

/**
 * Description of StrikeCommandStrategy
 *
 * @author chris
 */
class StrikeCommandStrategy implements ICommandStrategy
{
    const Regex = '/(?:zone) (\d{1,2})/i';
    const HoldRegex = '/(?:hold)(?: on)?(?: node)? (\d{1,2})/i';

    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $slackApi;
    private $response;
    private $channel;

    public function __construct(ConquestRepository $conquestRepository,
            ZoneRepository $zoneRepository, NodeRepository $nodeRepository,
            StrikeRepository $strikeRepository, ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;

        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->nodeRepository = $nodeRepository;
        $this->strikeRepository = $strikeRepository;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(StrikeCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->channel = $payload['channel'];
        $data = $payload['text'];

        $matches = [];
        if (preg_match(StrikeCommandStrategy::Regex, $data, $matches))
        {
            $zone = $matches[1];
        }
        else
        {
            $this->response = "I can only setup a strike map if you tell me what zone!  Hint: zone {number}";
            return;
        }
        $hold = null;
        if (preg_match(StrikeCommandStrategy::HoldRegex, $data, $matches))
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
        $this->zoneRepository->CreateZone($conquest, $zone);
        $zone = $this->zoneRepository->GetZone($conquest, $zone);
        $this->CreateNodes($zone, $hold);
        $nodes = $this->nodeRepository->GetAllNodes($zone);
        $this->CreateStrikes($nodes);
        $this->response = "Strike map has been setup for zone " . $zone->zone;
    }

    public function CreateNodes($zone, $hold)
    {
        for ($i = 1; $i <= 10; $i++)
        {
            $this->nodeRepository->CreateNode($zone, $i, $hold == $i ? 1 : 0);
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
        $this->slackApi->SendMessage($this->response, null, $this->channel);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

}
