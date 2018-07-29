<?php

namespace web\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use dal\repositories\RiftTypeRepository;
use dal\SqlFilter;
use dal\SqlParam;

/**
 * Description of RiftController
 *
 * @author chris
 */
class RiftTypeController
{
    /**
     * @var RiftTypeRepository
     */
    private $repository;

    public function __construct(RiftTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function get(Request $request, Application $app)
    {
        $results = $this->repository->GetAllRiftType();
        return $app->json($results);
    }

}
