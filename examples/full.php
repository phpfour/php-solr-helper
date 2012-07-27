<?php

$solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));

$result = $solrHelper->addSingleValueCriteria('make', 'BMW')
                     ->addMultiValueCriteria('model', array('X5', 'X3'))
                     ->addRangeCriteria('price', array(20000, 50000))
                     ->start(0)
                     ->limit(10)
                     ->order('price desc')
                     ->debug(true)
                     ->search();

echo "Total records found: " . $result['total'], PHP_EOL;