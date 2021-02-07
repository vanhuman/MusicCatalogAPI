<?php

namespace Handlers;

use Exception;
use Models\GetParams;
use PDO;
use Psr\Container\ContainerInterface;

abstract class DatabaseHandler
{
    public const SORT_DIRECTIONS = ['ASC', 'DESC'];

    abstract public function selectById(int $id);

    abstract public function select(GetParams $params);

    abstract public function insert(array $body);

    abstract public function update(int $id, array $body);

    /* @var PDO $db */
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('databaseConnection')->getDatabase();
    }

    /**
     * Generic delete function to handle all delete requests.
     * @throws Exception
     */
    public function delete(string $table, int $id): int
    {
        $query = 'DELETE FROM ' . $table . ' WHERE id = ' . (int)$id;
        $result = $this->db->query($query);
        if ($result->rowCount() === 0) {
            throw new Exception(ucfirst($table) . ' with id ' . $id . ' not found.', 404);
        }
        return $result->rowCount();
    }

    /**
     * Function to delete rows that are not referenced on any album
     * @return int
     * @throws Exception
     */
    public function removeOrphans(string $table): int
    {
        $query = 'DELETE FROM ' . $table;
        $query .= ' WHERE NOT EXISTS (';
        $query .= '   SELECT id FROM album WHERE album.' . $table . '_id = ' . $table . '.id LIMIT 1';
        $query .= ')';
        $result = $this->db->query($query);
        return $result->rowCount();
    }

    /**
     * Return arrays for keys, variables and data to be used in an SQL INSERT prepared statement.
     * @return array
     */
    protected function formatPostdataForInsert(array $postData)
    {
        $count = 0;
        foreach ($postData as $key => $value) {
            if ($key !== 'id') {
                $keys[] = $key;
                $values[] = urldecode($value);
                $variables[] = ':variable' . $count;
                $data[':variable' . $count] = urldecode($value);
                $count++;
            }
        }
        $formattedPostData['keys'] = '`' . implode($keys, '`, `') . '`';
        $formattedPostData['variables'] = implode($variables, ', ');
        $formattedPostData['data'] = $data;
        return $formattedPostData;
    }

    /**
     * Return array with keys/variables and data to be used in an SQL UPDATE prepared statement.
     * @return array
     */
    protected function formatPostdataForUpdate(array $postData)
    {
        $count = 0;
        foreach ($postData as $key => $value) {
            if ($key !== 'id') {
                $keys_variables[] = '`' . $key . '` = :variable' . $count;
                $data[':variable' . $count] = urldecode($value);
                $count++;
            }
        }
        $formattedPostData['keys_variables'] = implode(', ', $keys_variables);
        $formattedPostData['data'] = $data;
        return $formattedPostData;
    }

    /**
     * Get the sortby parameter or get the default.
     * @return string
     */
    protected function getSortByFromParams(GetParams $params, array $sortFields, string $defaultSortField)
    {
        if (!in_array($params->sortBy, $sortFields)) {
            return $defaultSortField;
        } else {
            return $params->sortBy;
        }
    }

    /**
     * Get the sortdirection parameter or get the default.
     * @return string
     */
    protected function getSortDirectionFromParams(GetParams $params, string $defaultSortDirection)
    {
        if (!in_array(strtoupper($params->sortDirection), self::SORT_DIRECTIONS)) {
            return $defaultSortDirection;
        } else {
            return strtoupper($params->sortDirection);
        }
    }

    /**
     * Validate fields indicated in the specific handlers as mandatory.
     * @throws Exception
     */
    protected function validateMandatoryFields(array $postData, array $mandatoryFields)
    {
        foreach ($mandatoryFields as $field) {
            if (!array_key_exists($field, $postData)) {
                throw new Exception(ucfirst($field) . ' is a mandatory field.', 400);
            }
        }
    }

    /**
     * Validate that the postdata only holds permitted fields.
     * @throws Exception
     */
    protected function validateKeys(array $postData, array $fields)
    {
        // other keys than the database fields are not allowed
        foreach ($postData as $key => $value) {
            if (!in_array($key, $fields)) {
                throw new Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }

}
