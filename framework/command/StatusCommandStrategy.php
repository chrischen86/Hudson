<?php

namespace framework\command;

use framework\slack\ISlackApi;
use dal\repositories\ConquestRepository;
use dal\repositories\ZoneRepository;
use dal\repositories\StrikeRepository;
use dal\repositories\CoreRepository;

/**
 * Description of StatusCommandStrategy
 *
 * @author chris
 */
class StatusCommandStrategy implements ICommandStrategy
{
    const Regex = '/(status)/i';

    private $coreRepository;
    private $conquestRepository;
    private $zoneRepository;
    private $strikeRepository;
    private $slackApi;
    private $response;
    private $attachments;
    private $forceMessage;
    private $channel;

    public function __construct(CoreRepository $coreRepository,
                                ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                StrikeRepository $strikeRepository,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;

        $this->coreRepository = $coreRepository;
        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->strikeRepository = $strikeRepository;

        $this->forceMessage = false;
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(StatusCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->channel = $payload['channel'];
        $this->forceMessage = $this->IsSupportedRequest($payload['text']);

        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zones = $this->zoneRepository->GetAllZones($conquest);

        $attachments = array();
        foreach ($zones as $zone)
        {
            $strikes = $this->strikeRepository->GetStrikesByZone($zone);
            $response = '';
            foreach ($strikes as $strike)
            {
                $response .= $strike->node->zone->zone . '.' . $strike->node->node . '  - ';
                if ($strike->node->is_reserved)
                {
                    $response .= '(reserved) ';
                }
                if ($strike->user != null)
                {
                    $response .= "<@" . $strike->user->id . ">";
                }
                $response .= "\n";
            }
            $color = $zone->is_training ? "#BBD9B4" : "#FDC528"; 
            array_push($attachments, array(
                'color' => $color,
                'text' => '',
                'fields' => array(
                    array(
                        'title' => '',
                        'value' => $response
                    )
                )
            ));
        }
        $this->response = empty($zones) ? 'I am currently not tracking any zones :)'
                    : 'Here are the active zones I am tracking:';
        $this->attachments = $attachments;
    }

    public function SendResponse()
    {
        $channel = $this->coreRepository->GetMessageChannel();
        $ts = $this->coreRepository->GetMessageTimestamp();
        $shouldUpdate = false;
        if (!$this->forceMessage && $channel == $this->channel && $channel != null && $ts != null)
        {
            $response = $this->slackApi->GetGroupMessagesSince($ts, $channel);
            $shouldUpdate = !$response->body->has_more;
        }
        if ($shouldUpdate)
        {
            $this->slackApi->UpdateMessage($ts, $channel, $this->response, $this->attachments);
        }
        else
        {
            $response = $this->slackApi->SendMessage($this->response, $this->attachments, $this->channel);
            $this->coreRepository->SetMessageProperties($response->body->ts, $response->body->channel);
        }
        
        unset($this->response);
        unset($this->attachments);
        unset($this->channel);
        unset($this->forceMessage);
    }

}
