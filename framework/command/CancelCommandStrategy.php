<?php

namespace framework\command;

use dal\managers\ZoneRepository;
use dal\managers\ConquestRepository;
use framework\slack\ISlackApi;
use framework\command\StatusCommandStrategy;

/**
 * Description of CancelCommandStrategy
 *
 * @author chris
 */
class CancelCommandStrategy implements ICommandStrategy
{
    const Regex = '/(?:cancel)(?: zone)? (\d{1,2})/i';

    private $eventData;
    private $conquestRepository;
    private $zoneRepository;
    private $slackApi;
    private $response;
    private $statusCommandStrategy;

    public function __construct(ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                ISlackApi $slackApi,
                                StatusCommandStrategy $statusCommandStrategy)
    {
        $this->slackApi = $slackApi;

        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;

        $this->statusCommandStrategy = $statusCommandStrategy;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(CancelCommandStrategy::Regex, $text);
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
        if (!preg_match(CancelCommandStrategy::Regex, $data, $matches))
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
        $this->slackApi->SendMessage($this->response, null,
                $this->eventData['channel']);
        $this->statusCommandStrategy->Process($this->eventData);
        $this->statusCommandStrategy->SendResponse();
    }

}
