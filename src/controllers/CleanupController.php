<?php

namespace Controllers;

use Handlers\CleanupHandler;
use Helpers\ContainerHelper;
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
        parent::__construct($container);
        $this->cleanupHandler = ContainerHelper::get($container, 'cleanupHandler');
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
            $result = $this->cleanupHandler->cleanupImages($testRun);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $this->logQuery($result['query']);
        return $response->withJson([
            'Number of images moved' => count($result['images']),
            'Images moved' => $result['images'][0],
            'Number of thumb images moved' => count($result['thumbs']),
            'Thumb images moved' => $result['thumbs'],
        ], 200);
    }
}
