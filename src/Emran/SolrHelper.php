<?php

namespace Emran;

class SolrHelper
{
    /**
     * @var \Apache_Solr_Service
     */
    private $solr;

    /**
     * @var array The query parameters for search
     */
    private $query = array();

    /**
     * @var array List of fields returned by search
     */
    private $searchFields;
    
    /**
     * @var string The last error that occurred while dealing with Solr
     */
    public $lastError;

    /**
     * @param \Apache_Solr_Service $solr
     */
    public function __construct(\Apache_Solr_Service $solr)
    {
        $this->solr = $solr;
    }

    /**
     * Add Search Field
     *
     * @param string $field Field name to add in search result
     */
    public function addSearchField($field)
    {
        if (!array_key_exists($field, $this->searchFields)) {
            $this->searchFields[] = $field;
        }
    }

    /**
     * Search Vehicle
     *
     * @param array $criteria The list of criteria against which search will be performed
     * @param int $start Starting point of search
     * @param int $limit Total number of result to return
     * @param string $sort List of fields and order for sorting
     * @param boolean $includeAllFields Include all fields
     *
     * @return array|bool
     */
    public function search($criteria = array(), $start = 0, $limit = 10, $sort = '', $includeAllFields = false)
    {
        $this->query = array();

        foreach ($criteria as $key => $value) {
            if (!is_array($value)) {
                $this->addSingleValueCriteria($key, $value);
            } else {
                if ($this->isRangeCriteria($value)) {
                    $this->addRangeCriteria($key, $value);
                } else {
                    $this->addMultiValueCriteria($value, $key);
                }
            }
        }

        $params = array('sort' => $sort);

        if ($includeAllFields === false && !empty($this->searchField)) {
            $params['fl'] = implode(',', $this->searchFields);
        }

        $queryString = implode(' ', $this->query);

        try {

            $result = $this->solr->search($queryString, $start, $limit, $params);

            if ($result->getHttpStatus() == 200) {

                return array(
                    'total' => $result->response->numFound,
                    'result' => $result->response->docs
                );

            } else {

                $this->lastError = 'Something went wrong. Response from Solr: ' . $result->getHttpStatusMessage();
                return false;

            }

        } catch (\Exception $e) {

            $this->lastError = $e->getMessage();
            return false;

        }
    }

    private function isRangeCriteria($value)
    {
        return isset($value['start']) && isset($value['end']);
    }

    private function addMultiValueCriteria($value, $key)
    {
        $params = array();

        foreach ($value as $singleValue) {
            $params[] = "{$key}:\"{$singleValue}\"";
        }

        $this->query[] = "+(" . implode(" OR ", $params) . ")";
    }

    private function addRangeCriteria($key, $value)
    {
        $this->query[] = "+{$key}:[{$value['start']} TO {$value['end']}]";
    }

    private function addSingleValueCriteria($key, $value)
    {
        if (substr($key, 0, 1) != '-') {
            $this->query[] = "+{$key}:\"{$value}\"";
        } else {
            $this->query[] = "-{$key}:\"{$value}\"";
        }
    }
}