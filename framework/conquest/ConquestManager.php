<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\conquest;

use \DateTime;
use dal\Phases;
use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\StrikeRepository;
use dal\managers\ConsensusRepository;
use framework\conquest\SetupResultEnum;

/**
 * Description of ConquestManager
 *
 * @author chris
 */
class ConquestManager
{
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $consensusRepository;

    public function __construct(ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                NodeRepository $nodeRepository,
                                StrikeRepository $strikeRepository,
                                ConsensusRepository $consensusRepository)
    {
        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->nodeRepository = $nodeRepository;
        $this->strikeRepository = $strikeRepository;
        $this->consensusRepository = $consensusRepository;
    }

    public function SetupZone($zone, $holds, $isTraining = 0)
    {
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $check = $this->zoneRepository->GetZone($conquest, $zone);
        if ($check != null && !$check->is_owned)
        {
            return SetupResultEnum::Error;
        }

        $this->zoneRepository->CreateZone($conquest, $zone, $isTraining);
        $resultZone = $this->zoneRepository->GetZone($conquest, $zone);
        $this->CreateNodes($resultZone, $holds);
        $nodes = $this->nodeRepository->GetAllNodes($resultZone);
        $this->CreateStrikes($nodes);

        return $resultZone == null ? SetupResultEnum::Unchanged : SetupResultEnum::Success;
    }
    
    public function DeleteConsensus($timestamp)
    {
        $this->consensusRepository->DeleteConsensusByTimestamp($timestamp);
    }

    public function SetupConsensus($zone)
    {
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $result = $this->consensusRepository->CreateConsensus($conquest, $zone);
        return $result == null ? SetupResultEnum::Unchanged : SetupResultEnum::Success;
    }

    public function SetConsensusTimestamp($zone, $timestamp)
    {
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $this->consensusRepository->SetConsensusTimestamp($conquest, $zone, $timestamp);
    }

    public function ReactionAdded($timestamp, $reaction)
    {
        $consensus = $this->consensusRepository->GetConsensusByTimestamp($timestamp);
        if ($consensus == null)
        {
            return null;
        }
        switch ($reaction)
        {
            case "+1":
                $consensus->votes = $consensus->votes + 1;
                break;
            case "-1":
                $consensus->vetoes = $consensus->vetoes + 1;
                break;
            default:
                return;
        }
        $this->consensusRepository->UpdateConsensus($consensus);
        return $consensus;
    }

    public function ReactionRemoved($timestamp, $reaction)
    {
        $consensus = $this->consensusRepository->GetConsensusByTimestamp($timestamp);
        if ($consensus == null)
        {
            return null;
        }
        switch ($reaction)
        {
            case "+1":
                $consensus->votes = $consensus->votes == 0 ? 0 : $consensus->votes - 1;
                break;
            case "-1":
                $consensus->vetoes =$consensus->vetoes == 0 ? 0 : $consensus->vetoes - 1;
                break;
            default:
                return;
        }
        $this->consensusRepository->UpdateConsensus($consensus);
        return $consensus;
    }

    public function GetHistorySince(DateTime $sinceDate)
    {
        return GetHistory($sinceDate, new DateTime());
    }

    public function GetHistory(DateTime $sinceDate, DateTime $tillDate)
    {
        $firstStartDate = $this->GetLastStartDate($sinceDate);
        $currentDate = $firstStartDate;

        $arr = [];
        $count = 0;
        do
        {
            $date = new DateTime($currentDate->format('Y-m-d'));
            $date->setTime(Phases::Phase1, 0);
            $arr[$count++] = $this->GetSummaryStatsByDate($date);
            $endDate = $date->modify('+7 day');
            $currentDate = new DateTime($endDate->format('Y-m-d'));
        } while ($tillDate->diff($currentDate)->format('%a') > 7);
        return $arr;
    }

    public function GetSummaryStatsByDate($date)
    {
        $lastStartDate = $this->GetLastStartDate($date);
        $endDate = new DateTime($lastStartDate->format('Y-m-d'));
        $lastEndDate = $endDate->modify('+5 day');

        $conquests = $this->conquestRepository->GetConquests($lastStartDate, $lastEndDate);

        $zones = [];
        $nodes = [];
        $strikes = [];
        foreach ($conquests as $conquest)
        {
            $zones = array_merge($zones, $this->zoneRepository->GetAllZonesByConquest($conquest));
            $nodes = array_merge($nodes, $this->nodeRepository->GetAllNodesByConquest($conquest));
            $strikes = array_merge($strikes, $this->strikeRepository->GetStrikesByConquest($conquest));
        }

        $toReturn = new StatsDto();
        $toReturn->forDate = $lastStartDate;
        $toReturn->endDate = $lastEndDate;
        $toReturn->conquests = $conquests;
        $toReturn->zones = $zones;
        $toReturn->nodes = $nodes;
        $toReturn->strikes = $strikes;

        return $toReturn;
    }

    public function GetSummaryStats()
    {
        $now = new DateTime();
        return $this->GetSummaryStatsByDate($now);
    }

    public function GetLastPhaseStats()
    {
        $now = new DateTime();
//echo 'Now: ' . $now->format('Y-m-d h:i:s') . ' <br/>';
        $lastPhaseDate = $this->GetLastPhaseDate($now);
        $conquest = $this->conquestRepository->GetConquestByDate($lastPhaseDate);
//echo $lastPhaseDate->format('Y-m-d H:i:s');
        if ($conquest == null || $conquest->id == null)
        {
            return null;
        }

        $zones = $this->zoneRepository->GetAllZonesByConquest($conquest);
        $nodes = $this->nodeRepository->GetAllNodesByConquest($conquest);
        $strikes = $this->strikeRepository->GetStrikesByConquest($conquest);

        $toReturn = new StatsDto();
        $toReturn->forDate = $lastPhaseDate;
        $toReturn->endDate = null;
        $toReturn->conquests = array($conquest);
        $toReturn->zones = $zones;
        $toReturn->nodes = $nodes;
        $toReturn->strikes = $strikes;

        return $toReturn;
    }
    
    public function GetPersonalStats($date, $user)
    {
        $lastStartDate = $this->GetLastStartDate($date);
        $endDate = new DateTime($lastStartDate->format('Y-m-d'));
        $lastEndDate = $endDate->modify('+5 day');

        $conquests = $this->conquestRepository->GetConquests($lastStartDate, $lastEndDate);
        
        $count = 0;
        foreach ($conquests as $conquest)
        {
            $count += sizeof($this->strikeRepository->GetStrikesByUserConquest($conquest, $user));
        }
        
        return $count;
    }

    private function GetLastStartDate(DateTime $dateTime)
    {
        $dayOfWeek = $dateTime->format('l');
        $hour = $dateTime->format('H');
        if (!date('I'))
        {
            $hour++;
        }
        $date = new DateTime($dateTime->format('m/d/Y'));
        switch ($dayOfWeek)
        {
            case 'Tuesday':
                $date->modify('-4 day');
                break;
            case 'Wednesday':
                $date->modify('-5 day');
                break;
            case 'Thursday':
                $date->modify('-6 day');
                break;
            case 'Friday':
                if ($hour < Phases::Phase1)
                {
                    $date->modify('-7 day');
                }
                break;
            case 'Saturday':
                $date->modify('-1 day');
                break;
            case 'Sunday':
                $date->modify('-2 day');
                break;
            case 'Monday':
                $date->modify('-3 day');
                break;
            default:
                break;
        }
        return $date;
    }

    private function GetLastPhaseDate(DateTime $dateTime)
    {
        $dayOfWeek = $dateTime->format('l');
        $hour = $dateTime->format('H');
        if (!date('I'))
        {
            $hour++;
        }

        $date = new DateTime($dateTime->format('m/d/Y'));
        switch ($dayOfWeek)
        {
            case 'Tuesday':
                if ($hour < Phases::Phase3 + Phases::PhaseLength)
                {
                    $date->modify('-1 day');
                    $date->setTime(Phases::Phase2, 0, 0);
                }
                else
                {
                    $date->setTime(Phases::Phase3, 0, 0);
                }
                break;
            case 'Wednesday':
                $date->modify('-1 day');
                $date->setTime(Phases::Phase3, 0, 0);
                break;
            case 'Thursday':
                $date->modify('-2 day');
                $date->setTime(Phases::Phase3, 0, 0);
                break;
            case 'Friday':
                if ($hour < Phases::Phase1 + Phases::PhaseLength)
                {
                    $date->modify('-3 day');
                    $date->setTime(Phases::Phase3, 0, 0);
                }
                else if ($hour < Phases::Phase2 + Phases::PhaseLength)
                {
                    $date->setTime(Phases::Phase1, 0, 0);
                }
                else
                {
                    $date->setTime(Phases::Phase2, 0, 0);
                }
            case 'Saturday':
            case 'Sunday':
            case 'Monday':
                if ($hour < Phases::Phase3 + Phases::PhaseLength)
                {
                    $date->modify('-1 day');
                    $date->setTime(Phases::Phase2, 0, 0);
                }
                else if ($hour < Phases::Phase1 + Phases::PhaseLength)
                {
                    $date->setTime(Phases::Phase3, 0, 0);
                }
                else if ($hour < Phases::Phase2 + Phases::PhaseLength)
                {
                    $date->setTime(Phases::Phase1, 0, 0);
                }
                else
                {
                    $date->setTime(Phases::Phase2, 0, 0);
                }
                break;
            default:
                break;
        }

        return $date;
    }

    private function CreateNodes($zone, $hold)
    {
        for ($i = 1; $i <= 10; $i++)
        {
            $this->nodeRepository->CreateNode($zone, $i, $hold == $i ? 1 : 0);
        }
    }

    private function CreateStrikes($nodes)
    {
        foreach ($nodes as $node)
        {
            $this->strikeRepository->CreateStrike($node);
        }
    }

}
