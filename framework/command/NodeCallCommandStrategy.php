<?php

namespace framework\command;

use framework\slack\ISlackApi;
use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\StrikeRepository;
use dal\managers\UserRepository;
use framework\command\StatusCommandStrategy;

/**
 * Description of NodeCallCommandStrategy
 *
 * @author chris
 */
class NodeCallCommandStrategy implements ICommandStrategy
{
    const Regex = '/^(\d{1,2})(?:\.|-)(\d{1,2})$|^(\d{1,2})(?:\.|-)(\d{1,2})((?:\s<@)([A-Z0-9]+)(?:>))$/i';
    
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $userRepository;
    private $slackApi;
    private $response;
    private $statusCommand;
    private $eventData;
    
    public function __construct(ConquestRepository $conquestRepository,
            ZoneRepository $zonesRepository, NodeRepository $nodeRepository,
            StrikeRepository $strikeRepository, UserRepository $userRepository,
            ISlackApi $slackApi, StatusCommandStrategy $statusCommand)
    {
        $this->slackApi = $slackApi;

        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zonesRepository;
        $this->nodeRepository = $nodeRepository;
        $this->strikeRepository = $strikeRepository;
        $this->userRepository = $userRepository;
        $this->statusCommand = $statusCommand;
    }
    
    public function IsSupportedRequest($text)
    {
        return preg_match(StatusCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        $matches = [];
        if (!preg_match(NodeCallCommandStrategy::Regex, $data, $matches))
        {
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

        $zoneValue = $matches[1 + $offset];
        $nodeValue = $matches[2 + $offset];
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
            $this->slackApi->SendMessage($this->response, null,
                    $this->eventData['channel']);
        }
        else
        {
            $this->statusCommand->Process($this->eventData);
            $this->statusCommand->SendResponse();
        }
    }

    public function IsJarvisCommand()
    {
        return false;
    }

}
