<?php

include_once './vendor/autoload.php';

function getPackages($limit = 15): \Generator
{
    $url = 'https://packagist.org/explore/popular.json?per_page=100';
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
foreach (getPackages(1000) as $i => $package) {
    $top[] = $package;
    echo "$i\n";
}
file_put_contents('popular.json', json_encode($top));
