<?php

include_once __DIR__ . '/../vendor/autoload.php';

function build(array $results, string $dir): array
{
    $stats = [];
    foreach ($results as $result) {
        $projectDir = "$dir/{$result['name']}";
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

$dirTmp = __DIR__ . '/../tmp';

$results = json_decode(file_get_contents("$dirTmp/1000_popular.json"), true);
$stats = build($results, "$dirTmp/projects");
file_put_contents("$dirTmp/composer_cache.json", json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
