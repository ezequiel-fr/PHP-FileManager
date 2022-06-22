<?php

require __DIR__.'/FileManager/autoload.php';

$query = new FileManager\FileManager();

$query->setDirectory('/');
$result = $query->scan();

print_r($result);
