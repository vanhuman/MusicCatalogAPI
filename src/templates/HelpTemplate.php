<?php

namespace Templates;

class HelpTemplate implements TemplateInterface
{
    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var array $fields
     */
    protected $fields;

    public function __construct(string $table, array $fields)
    {
        $this->table = $table;
        $this->fields = $fields;
    }

    public function getArray()
    {
        $relatedSortFieldsText = '';
        $relatedSortFieldsDirectionText = '';
        if ($this->table === 'albums') {
            $fieldsMapFunc = function($field) {
                $pos = strpos($field, '_id');
                if ($pos > 0) {
                    $field = str_replace('_id', '', $field) . ' (object)';
                }
                return $field;
            };
            $fields['fields'] = array_map($fieldsMapFunc, $this->fields['fields']);
            $relatedSortFieldsText = ' | one of related sortfields: ' . implode($this->fields['relatedSortFields'], ', ');
            $relatedSortFieldsDirectionText = ' (for related sortfields: ' . $this->fields['defaultRelatedSortDirection'] . ')';
        }

        $fieldsMapFunc = function($field) {
            if (in_array($field, $this->fields['mandatoryFields'])) {
                $field = $field . ' | mandatory';
            }
            return $field;
        };
        $bodyArguments = array_map($fieldsMapFunc, $this->fields['fields']);
        $idIndex = array_search('id', $bodyArguments);
        array_splice($bodyArguments, $idIndex, 1);

        $helpObject = [
            'GET /' . $this->table . '/{id}' => [
                'URI_arguments' => [
                    'id' => 'integer | optional',
                ],
                'query_string_arguments' => [
                    'token' => 'string | mandatory',
                ],
                'response' => [
                    'debug' => [
                        'query' => 'executed MySQL query'
                    ],
                    rtrim($this->table, 's') => $this->fields['fields'],
                ]
            ],
            'GET /' . $this->table . '/' => [
                'query_string_arguments' => [
                    'token' => 'integer | mandatory',
                    'page' => 'integer | default: 1',
                    'keywords' => 'string',
                    'sortby' => 'string | one of: ' . implode($this->fields['sortFields'], ', ') . $relatedSortFieldsText
                        . ' | default: ' . $this->fields['defaultSortField'],
                    'sortdirection' => 'string | one of: ' . implode($this->fields['sortDirections'], ', ')
                        . ' | default: ' . $this->fields['defaultSortDirection'] . $relatedSortFieldsDirectionText,
                ],
                'response' => [
                    'pagination' => [
                        'page' => 'page number from request',
                        'page_size' => 'size of the page',
                        'number_of_records' => 'number of records on this page',
                        'total_number_of_records' => 'total number of records',
                    ],
                    'parameters' => 'JSON object with all sort parameters of the request',
                    'debug' => [
                        'query' => 'executed MySQL query'
                    ],
                    $this->table => $this->fields['fields'],
                ]
            ],
            'POST /' . $this->table => [
                'query_string_arguments' => [
                    'token' => 'string | mandatory',
                ],
                'body_arguments' => $bodyArguments,
                'response' => [
                    rtrim($this->table, 's') => $this->fields['fields'],
                ]
            ],
            'PUT /' . $this->table . '/{id}' => [
                'URI_arguments' => [
                    'id' => 'integer | mandatory',
                ],
                'query_string_arguments' => [
                    'token' => 'string | mandatory',
                ],
                'body_arguments' => $bodyArguments,
                'response' => [
                    rtrim($this->table, 's') => $this->fields['fields'],
                ]
            ],
            'DELETE /' . $this->table . '/{id}' => [
                'URI_arguments' => [
                    'id' => 'integer | mandatory',
                ],
                'query_string_arguments' => [
                    'token' => 'string | mandatory',
                ],
                'response' => 'confirmation or error message'
            ],
        ];
        return $helpObject;
    }
}