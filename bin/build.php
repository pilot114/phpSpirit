<?php

include_once './vendor/autoload.php';

$count = 1000;

$dirTmp = __DIR__ . '/../tmp';
$popularFile = "$dirTmp/{$count}_popular.json";
$composerCacheFile = "$dirTmp/composer_cache.json";
$dirComposerFiles = "$dirTmp/projects";
$reportFile =  __DIR__ . '/../serve/src/report.json';

// 1
//$top = [];
//foreach (getPackages($count) as $i => $package) {
//    $top[] = $package;
//    echo "$i\n";
//}
//file_put_contents($popularFile, json_encode($top, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//
//// 2
//foreach (composerDataGenerator($count, $dirComposerFiles) as $composer) {
//    dump($composer['name']);
//}
//
//// 3
//$results = json_decode(file_get_contents($popularFile), true);
//$stats = build($results, $dirComposerFiles);
//file_put_contents($composerCacheFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// 4
$report = new Report($composerCacheFile, $popularFile);
$report->buildSummary($reportFile, $dirComposerFiles);