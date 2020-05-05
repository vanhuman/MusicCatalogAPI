<?php

namespace Controllers;

use Handlers\CleanupHandler;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

class CleanupController extends BaseController
{
    /* @var CleanupHandler $cleanupHandler */
    protected $cleanupHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->initController($container);
        $this->cleanupHandler = new CleanupHandler($this->container->get('db'));
    }

    /**
     * @return Response
     */
    public function cleanupImages(Request $request, Response $response)
    {
        $testRun = (bool)$request->getParam('test_run');
        try {
            $this->login($request);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $movedImages = $this->cleanupHandler->cleanupImages($testRun);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        return $response->withJson([
            'Number of images moved' => count($movedImages[0]),
            'Images moved' => $movedImages[0],
            'Number of thumb images moved' => count($movedImages[1]),
            'Thumb images moved' => $movedImages[1],
        ], 200);
    }
}