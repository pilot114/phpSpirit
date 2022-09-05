<?php

include_once './vendor/autoload.php';

$results = json_decode(file_get_contents('popular.json'), true);

function build(array $results): array {
    $stats = [];
    foreach ($results as $result) {
        $composerFile = "projects/{$result['name']}/composer.json";
        if (!is_file($composerFile)) {
            dump('project without composer.json');
            dump($result);
            continue;
        }
        $composer = json_decode(file_get_contents($composerFile), true);
        foreach ($composer as $key => $value) {
            $stats[$key][$result['name']] = base64_encode(json_encode($value));
        }
    }
    return $stats;
}

$stats = build($results);
dump(count($stats));
