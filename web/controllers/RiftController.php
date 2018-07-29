<?php

namespace web\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use dal\repositories\RiftHistoryRepository;
use dal\specifications\RiftHistoryByUserIdSpecification;
use dal\SqlFilter;
use dal\SqlParam;

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
        $filter = $this->createFilterFromParameters($params);
        $specification = new \dal\specifications\RiftHistoryByFilterSpecification($filter);
        
        $results = $this->repository->query($specification);
        return $app->json($results);
    }

    private function createFilterFromParameters($params)
    {
        $filter = new SqlFilter();
        foreach ($params as $key => $val)
        {
            switch ($key)
            {
                case "owner_id":
                    $filter->addParam(new SqlParam($key, $val, SqlParam::$TEXT));
                    break;
                case "type_id":
                    $filter->addParam(new SqlParam($key, $val, SqlParam::$NUMBER));
                    break;
                default:
                    break;
            }
        }

        return $filter;
    }

}
