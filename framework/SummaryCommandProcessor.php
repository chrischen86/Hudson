<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework;
use framework\slack\SlackApi;
use framework\conquest\ConquestManager;
use framework\conquest\StatsDto;
use DateTime;
use dal\Phases;
/**
 * Description of SummaryCommandProcessor
 *
 * @author chris
 */
class SummaryCommandProcessor implements ICommandProcessor {
    private $eventData;
    private $slackApi;
    private $conquestManager;
    
    private $response;
    private $attachments;
    
    public function __construct($data)
    {
        $this->eventData = $data;        
        $this->slackApi = new SlackApi();
        
        $this->conquestManager = new ConquestManager();
        $this->attachments = array();
    }

    public function Process()
    {
        $stats = $this->conquestManager->GetSummaryStats();
        
        $this->BuildDateSummary($stats);
        $this->BuildZoneSummary($stats, $this->attachments);
        $this->BuildStrikeSummary($stats, $this->attachments);
    }

    public function SendResponse() 
    {    
        $this->slackApi->SendMessage($this->response, $this->attachments, $this->eventData['channel']);
    }
    
    private function BuildDateSummary(StatsDto $stats)
    {
        if ($this->IsConquestOver())
        {
            $this->response = 'Here is the summary for the conquest between *' .
                $stats->forDate->format('Y-m-d') . '* and *' . $stats->endDate->format('Y-m-d') . '*:';
        }
        else
        {
            $this->response = 'The conquest is not yet over, but here is the data thus far:';
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
        $attackDictionary = array();
        $totalNonemptyAttacks = 0;
        foreach ($stats->strikes as $strike)
        {
            if ($strike->user_id != null)
            {
                $attackDictionary["<@" . $strike->user->name . ">"]++;
                $totalNonemptyAttacks++;
            }
        }
        arsort($attackDictionary);
        
        $this->BuildParticipationSummary($attachments, $attackDictionary);       
        if ($this->IsConquestOver())
        {
            $this->BuildAttackSummary($attachments, $attackDictionary, $totalNonemptyAttacks);
        }
    }
    
    private function BuildParticipationSummary(&$attachments, $attackDictionary)
    {
        $fields = array();
        $message = 'A total of *' . sizeof($attackDictionary) . "* members have participated in this conquest!\n" . 
                'This means we had a participation rate of *' . number_format(sizeof($attackDictionary) / 40 * 100, 2) . "%*!\n";
        $message .= $this->IsConquestOver() ? implode(', ', array_keys($attackDictionary)) . "\n\nWe could not have done it without you!"
                : '';
        array_push($fields, array(
            'title' => 'Participation Summary',
            'value' => $message,                
        ));
        
        array_push($attachments, array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $fields,
            'mrkdwn_in' => ["fields"]
        )); 
    }
    
    private function BuildAttackSummary(&$attachments, $attackDictionary, $totalNonemptyAttacks)
    {
        $message = '';
        foreach ($attackDictionary as $attacker => $attackCount)
        {
            $message .= $attacker . ' - ' . $attackCount . ' hits! ' .
                    '(*' . number_format($attackCount / $totalNonemptyAttacks * 100, 2) . "%*)\n";
        }
        
        $achievements = array();
        array_push($achievements, array(
            'title' => 'Attack Summary',
            'value' => $message
        ));
        
        array_push($attachments, array(
            'color' => "#FDC528",
            'text' => '',
            'fields' => $achievements,
            'mrkdwn_in' => ["fields"]
        ));
    }
    
    private function IsConquestOver()
    {
        $now = new DateTime();
        $dayOfWeek = $now->format('l');
        $hour = $now->format('H');
        return (($dayOfWeek == 'Tuesday' && $hour >= Phases::Phase3 + Phases::PhaseLength)
                || $dayOfWeek == 'Wednesday'
                || $dayOfWeek == 'Thursday'
                || ($dayOfWeek == 'Friday' && $hour <= Phases::Phase1));
    }
}
