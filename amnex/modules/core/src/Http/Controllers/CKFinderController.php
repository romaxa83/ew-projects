<?php

namespace Wezom\Core\Http\Controllers;

use Illuminate\Http\Request;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class CKFinderController extends \CKSource\CKFinderBridge\Controller\CKFinderController
{
    public function requestAction(ContainerInterface $container, Request $request): Response
    {
        $response = parent::requestAction($container, $request);
        ob_start();

        return $response;
    }
}
