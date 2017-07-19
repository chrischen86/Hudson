<?php

namespace dal\managers;

use \DateTime;
use dal\models\UserModel;
use dal\IDataAccessAdapter;
use dal\ModelBuildingHelper;
use dal\Phases;

/**
 * Description of ConquestRepository
 *
 * @author chris
 */
class ConquestRepository
{
    private $adapter;

    public function __construct(IDataAccessAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function SetCommander(UserModel $user)
    {
        $conquest = $this->GetCurrentConquestWithUser($user);
        $sql = 'UPDATE conquest ' .
                "SET commander_id = '" . $user->id . "' " .
                'WHERE id = ' . $conquest->id;
        $this->adapter->query($sql);
    }

    public function GetCurrentConquestWithUser(UserModel $user)
    {
        $today = new DateTime();
        $day = $this->GetClosestDay($today);
        $phase = $this->GetPhase($day);

        return $this->GetConquest($day, $phase, $user);
    }

    public function GetCurrentConquest()
    {
        $today = new DateTime();
        $day = $this->GetClosestDay($today);
        $phase = $this->GetPhase($day);
        return $this->GetConquest($day, $phase);
    }

    public function GetConquestByDate(DateTime $dateTime)
    {
        $day = $this->GetClosestDay($dateTime);
        $phase = $this->GetPhase($day);
        return $this->GetConquest($day, $phase);
    }

    public function GetConquests(DateTime $startDate, DateTime $endDate = null)
    {
        $sql = 'SELECT c.id as conquest_id, c.commander_id, c.date, c.phase, ' .
                'u.id as user_id, u.name, u.vip ' .
                'FROM conquest c ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                "WHERE c.date >= '" . $startDate->format('Y-m-d') . "' ";
        if ($endDate != null)
        {
            $sql .= "AND c.date <= '" . $endDate->format('Y-m-d') . "' ";
        }
        $results = $this->adapter->query($sql);
        $toReturn = [];
        if ($results == null)
        {
            return $toReturn;
        }
        foreach ($results as $item)
        {
            $node = ModelBuildingHelper::BuildConquestModel($item);
            array_push($toReturn, $node);
        }
        return $toReturn;
    }

    private function GetConquest(DateTime $dateTime, $phase,
            UserModel $user = null)
    {
        $sql = 'SELECT c.id as conquest_id, c.commander_id, c.date, c.phase, ' .
                'u.id as user_id, u.name, u.vip ' .
                'FROM conquest c ' .
                'LEFT JOIN users u ON u.id = c.commander_id ' .
                "WHERE c.date = '" . $dateTime->format('Y-m-d H:i:s') . "'";
        $result = $this->adapter->query_single($sql);
        if ($result == null)
        {
            $this->CreateConquest($dateTime, $phase, $user);
            $result = $this->adapter->query_single($sql);
        }
        $conquest = ModelBuildingHelper::BuildConquestModel($result);
        return $conquest;
    }

    private function CreateConquest(DateTime $dateTime, $phase,
            UserModel $user = null)
    {
        switch ($phase)
        {
            case Phases::Phase1:
                $phaseNumber = 1;
                break;
            case Phases::Phase2:
                $phaseNumber = 2;
                break;
            default:
                $phaseNumber = 3;
                break;
        }
        $userId = $user == null ? 'null' : "'" . $user->id . "'";
        $sql = 'INSERT INTO conquest (commander_id, date, phase) ' .
                'VALUES (' . $userId . ", '" . $dateTime->format('Y-m-d H:i:s') . "', " . $phaseNumber . ')';
        $this->adapter->query($sql);
    }

    private function GetPhase(DateTime $dateTime)
    {
        $hour = $dateTime->format('H');
        if ($hour == Phases::Phase1)
        {
            return Phases::Phase1;
        }
        else if ($hour == Phases::Phase2)
        {
            return Phases::Phase2;
        }
        else
        {
            return Phases::Phase3;
        }
    }

    private function GetClosestDay(DateTime $dateTime)
    {
        $dayOfWeek = $dateTime->format('l');
        $hour = $dateTime->format('H');

        $date = new DateTime($dateTime->format('m/d/Y'));
        switch ($dayOfWeek)
        {
            case 'Tuesday':
                if ($hour <= Phases::Phase3 + Phases::PhaseLength)
                {
                    $date->setTime(Phases::Phase3, 0, 0);
                }
                else
                {
                    $date->modify('+3 day');
                    $date->setTime(Phases::Phase1, 0, 0);
                }
                break;
            case 'Wednesday':
                $date->modify('+2 day');
                $date->setTime(Phases::Phase1, 0, 0);
                break;
            case 'Thursday':
                $date->modify('+1 day');
                $date->setTime(Phases::Phase1, 0, 0);
                break;
            case 'Friday':
                if ($hour <= Phases::Phase1 + Phases::PhaseLength)
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
                if ($hour <= Phases::Phase3 + Phases::PhaseLength)
                {
                    $date->setTime(Phases::Phase3, 0, 0);
                }
                else if ($hour <= Phases::Phase1 + Phases::PhaseLength)
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

}
