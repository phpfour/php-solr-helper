PHP Solr Helper
===============

This is a small helper to ease querying Apache Solr using the solr-php-client library.

Example 1:
----------

Searches the solr for records matching the two fields make and model.

    $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', 8080, '/solr/'));
    $result = $solrHelper->addSingleValueCriteria('make', 'BMW')
                         ->addSingleValueCriteria('model', 'X5')
                         ->search();

Example 2:
----------

Searches the solr for records matching the two fields make, model and a price range between 10000 and 
20000. Also limits the fields to be returned to acode and price.

    $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', 8080, '/solr/'));
    $result = $solrHelper->addSingleValueCriteria('make', 'BMW')
                         ->addMultiValueCriteria('model', array('X5', 'X3'))
                         ->addRangeCriteria('price', array(20000, 50000))
                         ->start(0)
                         ->limit(10)
                         ->order('price desc')
                         ->search();