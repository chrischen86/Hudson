<?php

namespace framework\conquest;

use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\StrikeRepository;

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
            $zone->strikes = $this->strikeRepository->GetStrikesByZone($zone);
        }
        
        return $zones;
    }
}
