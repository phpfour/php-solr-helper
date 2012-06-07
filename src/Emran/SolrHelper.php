<?php

namespace Emran;

class SolrHelper
{
    /**
     * @var \Apache_Solr_Service
     */
    protected $solr;

    /**
     * @var array The query parameters for search
     */
    protected $query = array();

    /**
     * @var int Start of result
     */
    protected $start = 0;

    /**
     * @var int Number of result to return
     */
    protected $limit = 20;

    /**
     * @var string The sort order of results
     */
    protected $orderBy;

    /**
     * @var array The fields to include in results
     */
    protected $fields;

    /**
     * @var boolean Whether to show debug message
     */
    protected $debug;

    /**
     * Constructor
     *
     * Initiate the class with an instance of Apache_Solr_Service.
     *
     * @param \Apache_Solr_Service $solr
     */
    public function __construct(\Apache_Solr_Service $solr)
    {
        $this->solr = $solr;
    }

    /**
     * Add a single value criteria
     *
     * Example:
     *
     * $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
     * $solrHelper->addSingleValueCriteria('make', 'Toyota');
     * $solrHelper->search();
     *
     * @param $key
     * @param $value
     *
     * @return SolrHelper
     */
    public function addSingleValueCriteria($key, $value)
    {
        if (substr($key, 0, 1) != '-') {
            $this->query[] = "+{$key}:\"{$value}\"";
        } else {
            $this->query[] = "-{$key}:\"{$value}\"";
        }

        return $this;
    }

    /**
     * Add a multi value criteria
     *
     * Example:
     *
     * $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
     * $solrHelper->addMultiValueCriteria('model', array('Corolla', 'Camry'));
     * $solrHelper->search();
     *
     * @param $key
     * @param $value
     *
     * @return SolrHelper
     */
    public function addMultiValueCriteria($key, $value)
    {
        $params = array();

        foreach ($value as $singleValue) {
            $params[] = "{$key}:\"{$singleValue}\"";
        }

        $this->query[] = "+(" . implode(" OR ", $params) . ")";
        return $this;
    }

    /**
     * Add a range criteria
     *
     * Example:
     *
     * $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
     * $solrHelper->addRangeCriteria('price', array('50000', '75000'));
     * $solrHelper->search();
     *
     * @param $key
     * @param $value
     *
     * @return SolrHelper
     */
    public function addRangeCriteria($key, $value)
    {
        $this->query[] = "+{$key}:[{$value[0]} TO {$value[1]}]";
        return $this;
    }

    /**
     * Add a greater than criteria
     *
     * Example:
     *
     * $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
     * $solrHelper->addGreaterThanCriteria('price', 50000);
     * $solrHelper->search();
     *
     * @param $key
     * @param $value
     *
     * @return SolrHelper
     */
    public function addGreaterThanCriteria($key, $value)
    {
        $this->query[] = "+{$key}:[{$value} TO *]";
        return $this;
    }

    /**
     * Add a less than criteria
     *
     * Example:
     *
     * $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
     * $solrHelper->addLessThanCriteria('price', 50000);
     * $solrHelper->search();
     *
     * @param $key
     * @param $value
     *
     * @return SolrHelper
     */
    public function addLessThanCriteria($key, $value)
    {
        $this->query[] = "+{$key}:[* TO {$value}]";
        return $this;
    }

    /**
     * Specify the start of result
     *
     * @param $start
     * @return SolrHelper
     */
    public function start($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Specify number of document to return
     *
     * @param $limit
     * @return SolrHelper
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Specify the sorting field and order
     *
     * @param $orderBy
     * @return SolrHelper
     */
    public function order($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Specify the fields to return
     *
     * @param array $fields
     * @return SolrHelper
     */
    public function fields($fields = array())
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Set debug mode
     *
     * @param $mode boolean
     * @return SolrHelper
     */
    public function debug($mode)
    {
        $this->debug = $mode;
        return $this;
    }

    /**
     * Perform the actual search based on the criteria
     *
     * Example:
     *
     * $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
     *
     * $solrHelper->addSingleValueCriteria('make', 'Toyota');
     *            ->addMultiValueCriteria('model', array('Corolla', 'Camry'));
     *            ->addRangeCriteria('price', array('50000', '75000'));
     *            ->start(10)
     *            ->limit(20)
     *            ->order('price desc')
     *            ->search();
     *
     * @return array
     * @throws \Apache_Solr_Exception
     * @throws \Exception
     */
    public function search()
    {
        if (empty($this->query)) {
            throw new \Exception('Search cannot be performed without criteria.');
        }

        $params = array();

        if (!empty($this->orderBy)) {
            $params['sort'] = $this->orderBy;
        }

        if (!empty($this->fields)) {
            $params['fl'] = implode(',', $this->fields);
        }

        $queryString = implode(' ', $this->query);
        $result = $this->solr->search($queryString, $this->start, $this->limit, $params);

        if ($this->debug) {
            echo "Query String: " . $queryString, PHP_EOL;
            echo "Solr Response: " . $result->getRawResponse(), PHP_EOL;
        }

        if ($result->getHttpStatus() == 200) {
            return array(
                'total' => $result->response->numFound,
                'result' => $result->response->docs
            );
        } else {
            throw new \Apache_Solr_Exception($result->getHttpStatusMessage());
        }
    }

    /**
     * Clears the criteria for reuse of searching
     */
    public function clear()
    {
        $this->query = '';
    }

    /**
     * Delete all documents from solr
     */
    public function deleteAll()
    {
        $this->solr->deleteByQuery('*:*');
        $this->solr->commit();
    }
}