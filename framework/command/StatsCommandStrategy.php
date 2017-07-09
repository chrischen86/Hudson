<?php

namespace framework\command;

use framework\slack\ISlackApi;
use framework\conquest\ConquestManager;
use framework\conquest\StatsDto;

/**
 * Description of StatsCommandStrategy
 *
 * @author chris
 */
class StatsCommandStrategy implements ICommandStrategy
{
    const Regex = '/^(stats)/i';

    private $channel;
    private $slackApi;
    private $conquestManager;
    private $response;
    private $attachments;

    public function __construct(ConquestManager $conquestManager,
                                ISlackApi $slackApi)
    {
        $this->slackApi = $slackApi;

        $this->conquestManager = $conquestManager;
        $this->attachments = array();
    }

    public function IsSupportedRequest($text)
    {
        return preg_match(StatsCommandStrategy::Regex, $text);
    }

    public function IsJarvisCommand()
    {
        return true;
    }

    public function Process($payload)
    {
        $this->channel = $payload['channel'];
        $stats = $this->conquestManager->GetLastPhaseStats();

        $this->BuildDateSummary($stats);
        $this->BuildZoneSummary($stats, $this->attachments);
        $this->BuildStrikeSummary($stats, $this->attachments);
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, $this->attachments,
                $this->channel);
    }

    private function BuildDateSummary(StatsDto $stats)
    {
        if ($stats->endDate == null)
        {
            $conquest = $stats->conquests[0];
            $this->response = 'Here is the summary for the conquest on *' .
                    $conquest->date->format('Y-m-d') . '* ' .
                    'phase *' . $conquest->phase . '*: ';
        }
        else
        {
            $this->response = 'Here is the summary for the conquest between ' .
                    $stats->forDate->format('Y-m-d H:i:s') . 'and ' .
                    $stats->endDate->format('Y-m-d H:i:s');
        }
    }

    private function BuildZoneSummary(StatsDto $stats, &$attachments)
    {
        $fields = array();

        $uniqueCount = 0;
        $mostContested = array();
        $mostContestedCount = 0;
        foreach ($stats->zones as $zone)
        {
            if ($zone->battle_count == 1)
            {
                $uniqueCount++;
            }

            if ($zone->battle_count > $mostContestedCount)
            {
                $mostContestedCount = $zone->battle_count;
                $mostContested = array($zone->zone);
            }
            else if ($zone->battle_count == $mostContestedCount)
            {
                array_push($mostContested, $zone->zone);
            }
        }

        array_push($fields,
                array(
            'title' => 'Zones',
            'value' => "I have tracked a total of *$uniqueCount* unique zones.\nThe most highly contested region(s) include zones *" .
            implode(', ', $mostContested) . "* that were fought over for a total of *$mostContestedCount* time(s)!"
        ));

        array_push($attachments,
                array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $fields,
            'mrkdwn_in' => ["fields"]
        ));
    }

    private function BuildStrikeSummary(StatsDto $stats, &$attachments)
    {
        $fields = array();

        $attackDictionary = array();
        foreach ($stats->strikes as $strike)
        {
            if ($strike->user_id != null)
            {
                $attackDictionary["<@" . $strike->user->name . ">"] ++;
            }
        }

        arsort($attackDictionary);
        array_push($fields,
                array(
            'title' => 'Members Summary',
            'value' => 'A total of *' . sizeof($attackDictionary) . "* members have participated in this phase!\n" .
            implode(', ', array_keys($attackDictionary)) . "\n\nWe could not have done it without you!"
        ));

        array_push($attachments,
                array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $fields,
            'mrkdwn_in' => ["fields"]
        ));

        $first = $this->GetTopAttackerByLimit($attackDictionary);
        if ($first[0] <= 0)
        {
            return;
        }
        if (sizeof($first[1]) > 0)
        {
            $message = implode(', ', $first[1]) . ': ' . $first[0] . " hits!  Smashing!\n";
            $second = $this->GetTopAttackerByLimit($attackDictionary, $first[0]);
        }
        if (sizeof($second[1]) > 0)
        {
            $message .= implode(', ', $second[1]) . ': ' . $second[0] . " hits!  Amazing!\n";
            $third = $this->GetTopAttackerByLimit($attackDictionary, $second[0]);
        }
        if (sizeof($third[1]) > 0)
        {
            $message .= implode(', ', $third[1]) . ': ' . $third[0] . ' hits!  Spectacular!';
        }

        $achievements = array();
        array_push($achievements,
                array(
            'title' => 'Achievements',
            'value' => $message
        ));

        array_push($attachments,
                array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $achievements,
            'mrkdwn_in' => ["fields"]
        ));
    }

    private function GetTopAttackerByLimit($attackDictionary, $limit = 0)
    {
        $topAttackCount = 0;
        $topAttackers = array();
        foreach ($attackDictionary as $attacker => $attackCount)
        {
            if ($attackCount >= $topAttackCount && ($limit == 0 || $attackCount < $limit))
            {
                $topAttackCount = $attackCount;
                array_push($topAttackers, $attacker);
            }
        }
        return array($topAttackCount, $topAttackers);
    }

}
