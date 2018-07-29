<?php

namespace web\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use dal\repositories\UserRepository;
use dal\SqlFilter;
use dal\SqlParam;

/**
 * Description of RiftController
 *
 * @author chris
 */
class UserController
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function get(Request $request, Application $app)
    {
        $results = $this->repository->GetActiveUsers();
        return $app->json($results);
    }

}
