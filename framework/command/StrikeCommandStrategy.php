<?php

namespace framework\command;

use framework\slack\ISlackApi;
use dal\managers\CoreRepository;
use framework\conquest\ConquestManager;
use framework\command\StatusCommandStrategy;
use StateEnum;
use framework\conquest\SetupResultEnum;

/**
 * Description of StrikeCommandStrategy
 *
 * @author chris
 */
class StrikeCommandStrategy implements ICommandStrategy
{
    const Regex = '/(setup|start|set up) (zone)/i';

    private $zoneRegex = '/(?:zone) (\d{1,2})/i';
    private $holdRegex = '/(?:hold)(?: on)?(?: node)? (\d{1,2})/i';
    private $coreRepository;
    private $conquestManager;
    private $slackApi;
    private $statusCommandStrategy;
    private $response;
    private $eventData;
    private $reactions;

    public function __construct(CoreRepository $coreRepository,
                                ConquestManager $conquestManager,
                                ISlackApi $slackApi,
                                StatusCommandStrategy $statusCommandStrategy)
    {
        $this->slackApi = $slackApi;

        $this->coreRepository = $coreRepository;
        $this->conquestManager = $conquestManager;
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

        $coreState = $this->coreRepository->GetState();
        $isTraining = $coreState->state == StateEnum::Training ? 1 : 0;

        $result = null;
        switch ($coreState->state)
        {
            case StateEnum::Coordinating:
            case StateEnum::Training:
                $result = $this->conquestManager->SetupZone($zone, $hold);
                $this->handleSetupZone($result, $zone, $isTraining);
                break;
            case StateEnum::Consensus:
                $result = $this->conquestManager->SetupConsensus($zone);
                $this->handleSetupConsensus($result, $zone);
                break;
            default:
                $this->response = "Could not setup zone due to unhandled state";
                return;
        }
    }

    private function handleSetupConsensus($result, $zone)
    {
        if ($result == SetupResultEnum::Unchanged)
        {
            $this->response = "Zone *$zone* is already up for vote";
        }
        else
        {
            $this->response = "`Zone $zone Vote`";
            $this->reactions = ["thumbsup", "thumbsdown"];
        }
    }

    private function handleSetupZone($result, $zone, $isTraining)
    {
        if ($result == SetupResultEnum::Error)
        {
            $this->response = "Zone *$zone* has not yet been completed/removed.  Please mark it as done or lost before trying again.\n" .
                    "Hint: zone # (done|lost)";
        }
        else
        {
            $this->response = $isTraining ? "Training zone " . $zone . " has been setup"
                        : "Strike map has been setup for zone " . $zone;
        }
    }

    public function SendResponse()
    {
        $response = $this->slackApi->SendMessage($this->response, null, $this->eventData['channel']);

        if (sizeof($this->reactions) > 0)
        {
            foreach ($this->reactions as $reaction)
            {
                $this->slackApi->AddReaction($response->body->ts, $response->body->channel, $reaction);
            }
        }
        else
        {
            $this->statusCommandStrategy->Process($this->eventData);
            $this->statusCommandStrategy->SendResponse();
        }
        unset($this->response);
        unset($this->eventData);
        unset($this->reactions);
    }

}
