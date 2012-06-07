<?php

require_once __DIR__ . '/../vendor/SolrPhpClient/Apache/Solr/Service.php';
require_once __DIR__ . '/../src/Emran/SolrHelper.php';

$solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
$result = $solrHelper->addSingleValueCriteria('make', 'BMW')->search();

echo "Total records found: " . $result['total'], PHP_EOL;