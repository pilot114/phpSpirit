<?php

include_once './vendor/autoload.php';

$results = json_decode(file_get_contents('popular.json'), true);

function build(array $results): array {
    $stats = [];
    foreach ($results as $result) {
        $projectDir = "projects/{$result['name']}";
        $composerFile = "$projectDir/composer.json";
        if (!is_file($composerFile)) {
            dump('project without composer.json in root: ' . $result['name']);
            continue;
        }
        $composer = json_decode(file_get_contents($composerFile), true);
        if (!$composer) {
            dump(sprintf('error composer.json: %s - %s', $result['name'], json_last_error_msg()));
            continue;
        }
        foreach ($composer as $key => $value) {
            $stats[$key][$result['name']] = base64_encode(json_encode($value));
        }
        $stats['_projects'][] = $projectDir;
    }
    return $stats;
}

$stats = build($results);

file_put_contents('composer_cache.json', json_encode($stats));
