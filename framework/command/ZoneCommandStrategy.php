<?php

namespace framework\command;

use dal\repositories\ZoneRepository;
use dal\repositories\ConquestRepository;
use framework\slack\ISlackApi;

/**
 * Description of ZoneCommandStrategy
 *
 * @author chris
 */
class ZoneCommandStrategy implements ICommandStrategy
{
    const Regex = '/(zone) (\d{1,2}) (completed|is ours|finished|done|lost)/i';

    private $ZoneRegex = '/(\d{1,2})/i';
    private $WinRegex = '/(completed|is ours|finished|done)/i';
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

    public function IsJarvisCommand()
    {
        return false;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(ZoneCommandStrategy::Regex, $text);
    }

    public function Process($payload)
    {
        $this->eventData = $payload;
        $data = $payload['text'];
        $matches = [];
        if (!preg_match($this->ZoneRegex, $data, $matches))
        {
            return;
        }
        $zone = $matches[1];
        $conquest = $this->conquestRepository->GetCurrentConquest();

        $trackedZone = $this->zoneRepository->GetZone($conquest, $zone);
        if ($trackedZone == null)
        {
            return;
        }

        $this->zoneRepository->UpdateZone($conquest, $zone, true);
        $this->response = preg_match($this->WinRegex, $data) ? "Amazing work!  I'll go ahead and remove that zone from the list."
                    : "No worries, better luck next time!";
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, null,
                $this->eventData['channel']);

        $this->statusCommandStrategy->Process($this->eventData);
        $this->statusCommandStrategy->SendResponse();
        
        unset($this->response);
        unset($this->eventData);
    }

}
