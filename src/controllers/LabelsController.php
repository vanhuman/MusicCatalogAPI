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
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new LabelsHandler($this->container->get('db'));
    }

    public function getLabel(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        try {
            $label = $this->handler->getLabel($id);
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
            $labels = $this->handler->getLabels($sortBy, $sortDirection);
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
            $label = $this->handler->insertLabel($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $labelTemplate = new LabelTemplate($label);
        $response = $response->withJson($labelTemplate->getArray(), 200);
        return $response;
    }

    public function putLabel(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $label = $this->handler->updateLabel($id, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $labelTemplate = new LabelTemplate($label);
        $response = $response->withJson($labelTemplate->getArray(), 200);
        return $response;
    }
}