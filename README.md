PHP Solr Helper
===============

This is a small helper to ease querying Apache Solr using the solr-php-client library.

Example 1:
----------

Searches the solr for records matching the two fields make and model.

    $criteria = array(
        'make'  => 'Toyota',
        'model' => 'Camry'
    );

    $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', 8080, '/solr/'));
    $result = $solrHelper->search($criteria, 0, 10, 'price desc');

Example 2:
----------

Searches the solr for records matching the two fields make, model and a price range between 10000 and 
20000. Also limits the fields to be returned to acode and price.

    $criteria = array(
        'price' => array('start' => 10000, 'end' => 20000),
        'make'  => 'Toyota',
        'model' => 'Camry'
    );

    $solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', 8080, '/solr/'));
    $solrHelper->addSearchField('acode');
    $solrHelper->addSearchField('price');
    $result = $solrHelper->search($criteria, 0, 10, 'price desc');