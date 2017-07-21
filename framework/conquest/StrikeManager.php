<?php

namespace framework\conquest;

use dal\managers\ConquestRepository;
use dal\managers\ZoneRepository;
use dal\managers\NodeRepository;
use dal\managers\StrikeRepository;
use framework\command\StatusCommandStrategy;
use \Exception;

/**
 * Description of StrikeManager
 *
 * @author chris
 */
class StrikeManager
{
    private $conquestRepository;
    private $zoneRepository;
    private $nodeRepository;
    private $strikeRepository;
    private $statusCommandRepository;

    public function __construct(ConquestRepository $conquestRepository,
                                ZoneRepository $zoneRepository,
                                NodeRepository $nodeRepository,
                                StrikeRepository $strikeRepository,
                                StatusCommandStrategy $statusCommandStrategy)
    {
        $this->conquestRepository = $conquestRepository;
        $this->zoneRepository = $zoneRepository;
        $this->nodeRepository = $nodeRepository;
        $this->strikeRepository = $strikeRepository;
        $this->statusCommandRepository = $statusCommandStrategy;
    }

    public function ClaimNode($zoneValue, $nodeValue, $userValue)
    {
        $conquest = $this->conquestRepository->GetCurrentConquest();
        $zone = $this->zoneRepository->GetZone($conquest, $zoneValue);
        if ($zone == null || $zone->is_owned)
        {
            throw new Exception("zone_not_active");
        }

        $node = $this->nodeRepository->GetNode($zone, $nodeValue);
        $currentStrike = $this->strikeRepository->GetStrike($node);
        if ($currentStrike->user_id != null)
        {
            throw new Exception("node_claimed_already");
        }
        $user = new \dal\models\UserModel();
        $user->id = $userValue;
        $this->strikeRepository->UpdateStrike($node, $user);

        $strike = $this->strikeRepository->GetStrike($node);
        
        $payload = array('channel' => '');
        $this->statusCommandRepository->Process($payload);
        $this->statusCommandRepository->SendResponse();
        return $strike;
    }

}
