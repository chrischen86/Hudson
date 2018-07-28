<?php

namespace web\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use dal\repositories\RiftHistoryRepository;
use dal\specifications\RiftHistoryByUserIdSpecification;
/**
 * Description of RiftController
 *
 * @author chris
 */
class RiftController
{
    /**
     * @var RiftHistoryRepository
     */
    private $repository;

    public function __construct(RiftHistoryRepository $repository)
    {       
        $this->repository = $repository;
    }
    
    public function getRiftHistory(Request $request, Application $app)
    {
        $params = $request->query->all();
        foreach ($params as $key => $val)
        {
            error_log($key . ":" . $val);
        }
        
        $specification = new RiftHistoryByUserIdSpecification('U0KJBUYDC');
        $results = $this->repository->query($specification);

        return $app->json($results);
    }

}
