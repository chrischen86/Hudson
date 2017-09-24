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
    const Regex = '/(stats)/i';

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
        $this->BuildAchievementsSummary($stats, $this->attachments);
    }

    public function SendResponse()
    {
        $this->slackApi->SendMessage($this->response, $this->attachments, $this->channel);
        
        unset($this->response);
        unset($this->attachments);
        unset($this->channel);
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

        array_push($fields, array(
            'title' => 'Zones',
            'value' => "I have tracked a total of *$uniqueCount* unique zones.\nThe most highly contested region(s) include zones *" .
            implode(', ', $mostContested) . "* that were fought over for a total of *$mostContestedCount* time(s)!"
        ));

        array_push($attachments, array(
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
                $index = "<@" . $strike->user->name . ">";
                if (array_key_exists($index, $attackDictionary))
                {
                    $attackDictionary[$index] ++;
                }
                else
                {
                    $attackDictionary[$index] = 1;
                }
            }
        }

        arsort($attackDictionary);
        array_push($fields, array(
            'title' => 'Members Summary',
            'value' => 'A total of *' . sizeof($attackDictionary) . "* members have participated in this phase!\n" .
            implode(', ', array_keys($attackDictionary)) . "\n\nWe could not have done it without you!"
        ));

        array_push($attachments, array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $fields,
            'mrkdwn_in' => ["fields"]
        ));
    }

    private function BuildAchievementsSummary(StatsDto $stats, &$attachments)
    {
        $attackDictionary = array();
        foreach ($stats->strikes as $strike)
        {
            if ($strike->user_id != null)
            {
                $index = "<@" . $strike->user->name . ">";
                if (array_key_exists($index, $attackDictionary))
                {
                    $attackDictionary[$index] ++;
                }
                else
                {
                    $attackDictionary[$index] = 1;
                }
            }
        }

        if (sizeof($attackDictionary) <= 0)
        {
            return;
        }
        arsort($attackDictionary);
        $achievementMessage = ['Smashing!', 'Amazing!', 'Spectacular!'];
        $hitsArray = [0, 0, 0];
        $hitsUsers = [[], [], []];

        $currentAchievement = 0;
        foreach ($attackDictionary as $key => $value)
        {
            if ($value < $hitsArray[$currentAchievement])
            {
                $currentAchievement++;
            }
            if ($currentAchievement >= 3)
            {
                continue;
            }

            if ($value >= $hitsArray[$currentAchievement])
            {
                $hitsArray[$currentAchievement] = $value;
                array_push($hitsUsers[$currentAchievement], $key);
            }
        }

        $message = "";
        for ($i = 0; $i < 3; $i++)
        {
            if ($hitsArray[$i] <= 0)
            {
                continue;
            }
            $message .= implode(', ', $hitsUsers[$i]) . ': ' . $hitsArray[$i] . " hits!  " . $achievementMessage[$i] . "\n";
        }

        $achievements = array();
        array_push($achievements, array(
            'title' => 'Achievements',
            'value' => $message
        ));

        array_push($attachments, array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $achievements,
            'mrkdwn_in' => ["fields"]
        ));
    }

}
