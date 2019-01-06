<?php

namespace Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\Request;

use Handlers\DatabaseHandler;
use Helpers\TypeUtility;

abstract class Controller
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * This generic handler is overridden for each separate controller subclass.
     * @var DatabaseHandler $handler
     */
    protected $handler;

    /**
     * This function is implemented in the subclasses.
     * @param $models
     * @return mixed
     */
    abstract protected function newTemplate($models);

    /**
     * Generic get method, for GET requests for all endpoints.
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function get(Request $request, Response $response, $args)
    {
        $params = $this->collectGetParams($request, $args);
        try {
            $result = $this->handler->select($params);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $template = $this->newTemplate($result['body']);
        $templateArray = $template->getArray();
        $returnObject = $this->buildGetReturnObject($params, $result, $templateArray);
        return $response->withJson($returnObject, 200);
    }

    /**
     * Generic post method, for POST requests for all endpoints.
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function post(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $result = $this->handler->insert($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $template = $this->newTemplate($result['body']);
        return $response->withJson($template->getArray(), 200);
    }

    /**
     * Generic put method, for PUT requests for all endpoints.
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function put(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $result = $this->handler->update($id, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $template = $this->newTemplate($result['body']);
        return $response->withJson($template->getArray(), 200);
    }

    /**
     * Generic delete method, for DELETE requests for all endpoints.
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        if ($request->getUri()->getPath()) {
            // get the base of the URI ('albums', 'artists', etc)
            $table = explode('/', $request->getUri()->getPath())[0];
            // remove the last s since we want singular entity names
            $table = rtrim($table, 's');
        } else {
            return $this->showError($response, 'ERROR: No route path found.', 500);
        }
        $id = $args['id'];
        try {
            $this->handler->delete($table, $id);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $result = ucfirst($table) . ' with id ' . $id . ' deleted.';
        return $response->withJson($result, 200);
    }

    /**
     * Function to gather all request arguments in one object.
     * @param Request $request
     * @param $args
     * @return array
     */
    protected function collectGetParams(Request $request, $args)
    {
        $page = $request->getParam('page');
        if (!isset($page) || !TypeUtility::isInteger($page)) {
            $page = 1;
        }
        $page = (int)$page;
        return [
            'id' => array_key_exists('id', $args) ? $args['id'] : null,
            'page' => $page,
            'page_size' => $this->container->get('settings')->get('pageSize'),
            'sortby' => $request->getParam('sortby'),
            'sortdirection' => $request->getParam('sortdirection'),
            'filter' => [
                'artist_id' => $request->getParam('artist_id'),
                'label_id' => $request->getParam('label_id'),
                'genre_id' => $request->getParam('genre_id'),
                'format_id' => $request->getParam('format_id'),
            ]
        ];
    }

    /**
     * Function to build the return object for GET requests around the object template.
     * $params is what is being sent to the handler, $request is what comes back from the handler,
     * $templateArray is the object template converted to array.
     * @param array $params
     * @param array $result
     * @param array $template
     * @return array
     */
    protected function buildGetReturnObject($params, $result, $templateArray)
    {
        /*
         * in case of the albumsController, current($templateArray) is one album or an array with albums
         * and current(current($templateArray)) is a specific album id or the first album object, which is an array
        */
        if (current($templateArray) === null || sizeof(current($templateArray)) === 0) {
            // we were asking for a list of albums but there are none
            $numberOfRecords = 0;
        } else if (is_array(current(current($templateArray)))) {
            // we were asking for a list of albums and there is at least one
            $numberOfRecords = sizeof(current($templateArray));
        } else {
            // we were asking for one specific album
            $numberOfRecords = 1;
        }
        // only show pagination and parameters if we were getting a list of objects
        if (!isset($params['id'])) {
            $returnArray['pagination'] = [
                'page' => (int)$params['page'],
                'page_size' => $this->container->get('settings')->get('pageSize'),
                'number_of_records' => $numberOfRecords,
                'total_number_of_records' => $result['total_number_of_records'],
            ];
            if ($this->container->get('settings')->get('showParams')) {
                $returnArray['parameters'] = [];
                if (isset($params['sortby'])) {
                    $returnArray['parameters']['sortby'] = $params['sortby'];
                } else {
                    $returnArray['parameters']['sortby'] = $result['sortby'];
                }
                if (isset($params['sortdirection'])) {
                    $returnArray['parameters']['sortdirection'] = $params['sortdirection'];
                } else {
                    $returnArray['parameters']['sortdirection'] = $result['sortdirection'];
                }
                foreach ($params['filter'] as $key => $value) {
                    if (isset($params['filter'][$key])) {
                        $returnArray['parameters'][$key] = $value;
                    }
                }
            }
        }
        if ($this->container->get('settings')->get('showDebug')) {
            $returnArray['debug'] = [];
            $returnArray['debug']['query'] = $result['query'];
        }
        $returnArray = array_merge($returnArray, $templateArray);
        return $returnArray;
    }

    /**
     * Generic error messaging.
     * @param Response $response
     * @param string $errorMessage
     * @return Response
     */
    protected function showError($response, $errorMessage, $status)
    {
        $response = $response->withJson($errorMessage, $status);
        return $response;
    }
}