<?php

namespace web\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use dal\DataService;
use dal\SqlFilter;
use dal\SqlParam;
use dal\SqlPredicate;
use dal\SqlGroupBy;

/**
 * Description of RiftController
 *
 * @author chris
 */
class RiftController
{
    /**
     * @var DataService
     */
    private $service;

    public function __construct(DataService $service)
    {
        $this->service = $service;
    }

    public function getRiftHistory(Request $request, Application $app)
    {
        $params = $request->query->all();
        $predicate = $this->createPredicateFromParameters($params);
        $results = $this->service->query($predicate);
        return $app->json($results);
    }

    private function createPredicateFromParameters($params)
    {
        $predicate = new SqlPredicate("rift_history");
        $filter = new SqlFilter();
        foreach ($params as $key => $val)
        {
            switch ($key)
            {
                case '$groupBy':
                    $groupBy = new SqlGroupBy($val);
                    $groupBy->setAggregrateProperty("1");
                    $predicate->setGroupBy($groupBy);
                    break;
                case '$select':
                    $select = explode(',', $val);
                    $predicate->setSelect($select);
                    break;
                case "owner_id":
                    $filter->addParam(new SqlParam($key, $val, SqlParam::$TEXT));
                    break;
                case "type_id":
                    $filter->addParam(new SqlParam($key, $val, $val == "null" ? SqlParam::$NULL
                                        : SqlParam::$NUMBER));
                    break;
                case "is_deleted":
                    $filter->addParam(new SqlParam($key, $val, SqlParam::$NUMBER));
                    break;
                default:
                    break;
            }
        }

        $predicate->setFilter($filter);
        return $predicate;
    }

}
