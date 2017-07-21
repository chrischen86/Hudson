<?php

namespace framework\conquest;

use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\StrikeRepository;
use \Exception;

/**
 * Description of ZoneManager
 *
 * @author chris
 */
class ZoneManager
{
    private $conquestRepository;
    private $zoneRepository;
    private $strikeRepository;

    public function __construct(ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                StrikeRepository $strikeRepository)
    {
        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->strikeRepository = $strikeRepository;
    }

    public function GetStrikeTable()
    {
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zones = $this->zoneRepository->GetAllZones($conquest);
        
        foreach ($zones as $zone)
        {
            $zone->conquest = null; //Not important for the API
            $strikes = $this->strikeRepository->GetStrikesByZone($zone);
            foreach ($strikes as $strike)
            {
                $strike->node->zone = null;  //Clean up response for API
            }
            
            $zone->strikes = $strikes;
        }
        
        return $zones;
    }
}
