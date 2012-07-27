<?php

$solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
$result = $solrHelper->addSingleValueCriteria('make', 'BMW')->search();

echo "Total records found: " . $result['total'], PHP_EOL;