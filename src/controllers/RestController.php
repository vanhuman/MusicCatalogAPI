<?php

namespace Controllers;

use Enums\ExceptionType;
use Models\McException;
use Slim\Http\Response;
use Slim\Http\Request;

use Handlers\DatabaseHandler;
use Helpers\TypeUtility;
use Models\GetParams;

abstract class RestController extends BaseController
{
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
     * Generic get method, for GET requests for all endpoints using id.
     * @return Response
     */
    public function getById(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $id = array_key_exists('id', $args) ? $args['id'] : null;
        if (!isset($id)) {
            return $this->messageController->showError(
                $response,
                new \Exception(
                    'Trying to retrieve object by id, but the id is not set.',
                    500
                )
            );
        }
        try {
            $result = $this->handler->selectById($id);
        } catch (\Exception $e) {
            $exception = new McException($e->getMessage(), $e->getCode(), ExceptionType::DB_EXCEPTION());
            return $this->messageController->showError($response, $exception);
        }
        $template = $this->newTemplate($result['body']);
        $templateArray = $template->getArray();
        $returnObject = $this->getByIdReturnObject($result, $templateArray);
        return $response->withJson($returnObject, 200);
    }

    /**
     * Generic get method, for GET requests for all endpoints without id.
     * @return Response
     */
    public function get(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $params = $this->collectGetParams($request);
        try {
            $result = $this->handler->select($params);
        } catch (\Exception $e) {
            $exception = new McException($e->getMessage(), $e->getCode(), ExceptionType::DB_EXCEPTION());
            return $this->messageController->showError($response, $exception);
        }
        $template = $this->newTemplate($result['body']);
        $templateArray = $template->getArray();
        $returnObject = $this->getReturnObject($params, $result, $templateArray);
        return $response->withJson($returnObject, 200);
    }

    /**
     * Generic post method, for POST requests for all endpoints.
     * @return Response
     */
    public function post(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $body = $request->getParsedBody();
        try {
            $result = $this->handler->insert($body);
        } catch (\Exception $e) {
            $exception = new McException($e->getMessage(), $e->getCode(), ExceptionType::DB_EXCEPTION());
            return $this->messageController->showError($response, $exception);
        }
        $template = $this->newTemplate($result['body']);
        return $response->withJson($template->getArray(), 200);
    }

    /**
     * Generic put method, for PUT requests for all endpoints.
     * @return Response
     */
    public function put(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $result = $this->handler->update($id, $body);
        } catch (\Exception $e) {
            $exception = new McException($e->getMessage(), $e->getCode(), ExceptionType::DB_EXCEPTION());
            return $this->messageController->showError($response, $exception);
        }
        if (!isset($result)) {
            return $this->messageController->showError($response,
                new \Exception('No record with id ' . $id . ' found to update', 404)
            );
        }
        $template = $this->newTemplate($result['body']);
        return $response->withJson($template->getArray(), 200);
    }

    /**
     * Generic delete method, for DELETE requests for all endpoints.
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        if ($request->getUri()->getPath()) {
            // get the base of the URI ('albums', 'artists', etc)
            $table = explode('/', $request->getUri()->getPath())[0];
            // remove the last s since we want singular entity names
            $table = rtrim($table, 's');
        } else {
            return $this->messageController->showError($response,
                new \Exception(
                    'Trying to delete a record, but unable to determine the table to delete from.',
                    500
                )
            );
        }
        $id = $args['id'];
        try {
            $this->handler->delete($table, $id);
        } catch (\Exception $e) {
            $exception = new McException($e->getMessage(), $e->getCode(), ExceptionType::DB_EXCEPTION());
            return $this->messageController->showError($response, $exception);
        }
        return $this->messageController->showMessage($response, ucfirst($table) . ' with id ' . $id . ' deleted.');
    }

    /**
     * @return Response
     */
    public function removeOrphans(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        if ($request->getUri()->getPath()) {
            // get the base of the URI ('albums', 'artists', etc)
            $table = explode('/', $request->getUri()->getPath())[0];
            // remove the last s since we want singular entity names
            $table = rtrim($table, 's');
        } else {
            return $this->messageController->showError($response,
                new \Exception(
                    'Trying to remove orphans, but unable to determine the table to delete from.',
                    500
                )
            );
        }
        try {
            $result = $this->handler->removeOrphans($table);
        } catch (\Exception $e) {
            $exception = new McException($e->getMessage(), $e->getCode(), ExceptionType::DB_EXCEPTION());
            return $this->messageController->showError($response, $exception);
        }
        $returnObject = [
            'entity' => $table,
            'deleted' => $result,
        ];
        return $response->withJson($returnObject, 200);
    }

    /**
     * Function to gather all request arguments in one object.
     * @return GetParams
     */
    protected function collectGetParams(Request $request)
    {
        $page = $request->getParam('page');
        if (!isset($page) || (!TypeUtility::isInteger($page))) {
            $page = 1;
        }
        $page = (int)$page;
        $paramsArray = [
            'page' => $page,
            'pageSize' => $this->container->get('settings')->get('pageSize'),
            'sortBy' => $request->getParam('sortby'),
            'sortDirection' => $request->getParam('sortdirection'),
            'filter' => [
                'artist_id' => $request->getParam('artist_id'),
                'label_id' => $request->getParam('label_id'),
                'genre_id' => $request->getParam('genre_id'),
                'format_id' => $request->getParam('format_id'),
            ],
            'keywords' => $request->getParam('keywords'),
        ];
        return new GetParams($paramsArray);
    }

    /**
     * Function to build the return object for GET requests with id.
     * $request is what comes back from the handler, $templateArray is the object template converted to array.
     * @return array
     */
    protected function getByIdReturnObject(array $result, array $templateArray)
    {
        $returnArray = [];
        if ($this->container->get('settings')->get('showDebug')) {
            $returnArray['debug'] = [];
            $returnArray['debug']['query'] = $result['query'];
        }
        $returnArray = array_merge($returnArray, $templateArray);
        return $returnArray;
    }

    /**
     * Function to build the return object for GET requests without id.
     * $params is what is being sent to the handler, $request is what comes back from the handler,
     * $templateArray is the object template converted to array.
     * @return array
     */
    protected function getReturnObject(GetParams $params, array $result, array $templateArray)
    {
        // current($templateArray) is the first value in the album dictionary
        if (current($templateArray) === null || sizeof(current($templateArray)) === 0) {
            $numberOfRecords = 0;
        } else {
            $numberOfRecords = sizeof(current($templateArray));
        }
        $returnArray['pagination'] = [
            'page' => $params->page,
            'page_size' => $params->pageSize,
            'number_of_records' => $numberOfRecords,
            'total_number_of_records' => $result['total_number_of_records'],
        ];
        if ($this->container->get('settings')->get('showParams')) {
            $returnArray['parameters'] = [];
            $returnArray['parameters']['sortby'] = $result['sortby'];
            $returnArray['parameters']['sortdirection'] = $result['sortdirection'];
            foreach ($params->filter as $property => $value) {
                if (isset($params->filter->{$property})) {
                    $returnArray['parameters'][$property] = $value;
                }
            }
            if (isset($params->keywords)) {
                $returnArray['parameters']['keywords'] = $params->keywords;
            }
        }
        if ($this->container->get('settings')->get('showDebug')) {
            $returnArray['debug'] = [];
            $returnArray['debug']['query'] = $result['query'];
        }
        $returnArray = array_merge($returnArray, $templateArray);
        return $returnArray;
    }
}