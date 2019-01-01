<?php

namespace Controllers;

use Handlers\FormatsHandler;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Templates\FormatsTemplate;
use Templates\FormatTemplate;

class FormatsController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new FormatsHandler($this->container->get('db'));
    }

    protected function newTemplate($formats)
    {
        if (is_array($formats)) {
            return new FormatsTemplate($formats);
        } else {
            return new FormatTemplate($formats);
        }
    }

    public function postFormat(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $format = $this->handler->insertFormat($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $formatTemplate = new FormatTemplate($format);
        $response = $response->withJson($formatTemplate->getArray(), 200);
        return $response;
    }

    public function putFormat(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $format = $this->handler->updateFormat($id, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $formatTemplate = new FormatTemplate($format);
        $response = $response->withJson($formatTemplate->getArray(), 200);
        return $response;
    }

}