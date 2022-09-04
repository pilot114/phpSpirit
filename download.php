<?php

include_once './vendor/autoload.php';

$client = new Packagist\Api\Client();
$results = json_decode(file_get_contents('popular.json'), true);

foreach ($results as $result) {
    $package = $client->get($result['name']);
    $repo = $package->getRepository();
    $name = $package->getName();
    $dir = "projects/$name";
    if (is_dir($dir)) {
        echo "skip $name\n";
        continue;
    }
    mkdir($dir, recursive: true);
    `git clone $repo $dir`;
    echo "cloned $name\n";
}