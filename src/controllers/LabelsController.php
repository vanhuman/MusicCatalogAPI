<?php

namespace Controllers;

use Handlers\LabelsHandler;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Templates\LabelsTemplate;
use Templates\LabelTemplate;

class LabelsController extends Controller
{
    protected $container;
    protected $labelsHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->labelsHandler = new LabelsHandler($this->container->get('db'));
    }

    public function getLabel(Request $request, Response $response, $args)
    {
        $labelId = $args['labelId'];
        try {
            $label = $this->labelsHandler->getLabel($labelId);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $labelTemplate = new LabelTemplate($label);
        $response = $response->withJson($labelTemplate->getArray(), 200);
        return $response;
    }

    public function getLabels(Request $request, Response $response, $args)
    {
        $sortBy = $request->getParam('sortBy');
        $sortDirection = $request->getParam('sortDirection');
        try {
            $labels = $this->labelsHandler->getLabels($sortBy, $sortDirection);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $labelsTemplate = new LabelsTemplate($labels);
        $response = $response->withJson($labelsTemplate->getArray(), 200);
        return $response;
    }

    public function postLabel(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $label = $this->labelsHandler->insertLabel($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $labelTemplate = new LabelTemplate($label);
        $response = $response->withJson($labelTemplate->getArray(), 200);
        return $response;
    }

    public function putLabel(Request $request, Response $response, $args)
    {
        $labelId = $args['labelId'];
        $body = $request->getParsedBody();
        try {
            $label = $this->labelsHandler->updateLabel($labelId, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $labelTemplate = new LabelTemplate($label);
        $response = $response->withJson($labelTemplate->getArray(), 200);
        return $response;
    }

    public function deleteLabel(Request $request, Response $response, $args)
    {
        $labelId = $args['labelId'];
        try {
            $result = $this->labelsHandler->deleteRecord('label', $labelId);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $result = 'Label with id ' . $labelId . ' deleted.';
        $response = $response->withJson($result, 200);
        return $response;
    }

}