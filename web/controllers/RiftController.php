<?php

namespace web\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of RiftController
 *
 * @author chris
 */
class RiftController
{
    public function getRiftHistory(Request $request, Application $app)
    {
        $params = $request->query->all();
        foreach ($params as $key => $val)
        {
            error_log($key . ":" . $val);
        }

        return $app->json();
    }

}
