<?php

namespace framework\command;

use framework\conquest\ConquestManager;
use framework\slack\ISlackApi;
use framework\google\ImageChartApi;
use DateTime;
use framework\conquest\StatsDto;

/**
 * Description of SummaryHistoryCommandStrategy
 *
 * @author chris
 */
class SummaryHistoryCommandStrategy implements ICommandStrategy
{
    const Regex = '/(summary )(((since )(\d{4}\/\d{2}\/\d{2}))|((between )(\d{4}\/\d{2}\/\d{2})( and )(\d{4}\/\d{2}\/\d{2})))/i';

    private $channel;
    private $slackApi;
    private $imageChartApi;
    private $conquestManager;
    private $response;
    private $attachments;

    public function __construct(ConquestManager $conquestManager,
                                ImageChartApi $imageChartApi,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
        $this->conquestManager = $conquestManager;
        $this->imageChartApi = $imageChartApi;
        $this->attachments = array();
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(SummaryHistoryCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->channel = $payload['channel'];
        $data = $payload['text'];
        $matches = [];
        if (!preg_match(SummaryHistoryCommandStrategy::Regex, $data, $matches))
        {
            return;
        }
        $sinceDate = new DateTime();
        $endDate = new DateTime();
        if (sizeof($matches) > 6)
        {
            $sinceDate = DateTime::createFromFormat('Y/m/d', $matches[8]);
            $endDate = DateTime::createFromFormat('Y/m/d', $matches[10]);
        }
        else
        {
            $sinceDate = DateTime::createFromFormat('Y/m/d', $matches[5]);
        }

        $stats = $this->conquestManager->GetHistory($sinceDate, $endDate);
        $dataArray = [];
        foreach ($stats as $stat)
        {
            $dataArray[$stat->forDate->format('Y/m/d')] = $this->BuildDataPoint($stat);
        }
        $chart = $this->imageChartApi->CreateLineChart($dataArray);
        $this->response = $chart;
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, $this->attachments, $this->channel);
    }

    private function BuildDataPoint(StatsDto $stats)
    {
        $attackDictionary = array();
        foreach ($stats->strikes as $strike)
        {
            if ($strike->user_id != null)
            {
                $attackDictionary[$strike->user_id] ++;
            }
        }
        return sizeof($attackDictionary);
    }

}
