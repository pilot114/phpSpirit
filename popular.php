<?php

include_once './vendor/autoload.php';

function getPackages($limit = 15): \Generator
{
    $url = 'https://packagist.org/explore/popular.json';
    $yielded = 0;

    while(true) {
        $data = json_decode(file_get_contents($url), true);
        if (empty($data['packages'])) {
            return;
        }
        $url = $data['next'];
        foreach ($data['packages'] as $package) {
            if ($yielded >= $limit) {
                return;
            }
            yield $package;
            $yielded++;
        }
    }
}

$top = [];
foreach (getPackages(1000) as $package) {
    $top[] = $package;
}
file_put_contents('popular.json', json_encode($top));
