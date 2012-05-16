<?php

require_once __DIR__ . '/../vendor/SolrPhpClient/Apache/Solr/Service.php';
require_once __DIR__ . '/../src/Emran/SolrHelper.php';

$criteria = array(
    'make'  => 'Toyota'
);

$solrHelper = new \Emran\SolrHelper(new \Apache_Solr_Service('localhost', '8080', '/solr/'));
$result = $solrHelper->search($criteria, 0, 10, 'price desc');

echo "Total records found: " . $result['total'];