<?php

include_once __DIR__ . '/../vendor/autoload.php';

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

$count = 1000;

$top = [];
foreach (getPackages($count) as $i => $package) {
    $top[] = $package;
    echo "$i\n";
}
file_put_contents(__DIR__ . "/../tmp/{$count}_popular.json", json_encode($top, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
