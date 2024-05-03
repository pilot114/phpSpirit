<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Github\{AuthMethod, Client};

function composerDataGenerator(int $count, string $dir) : \Generator
{
    $github = new Client();
    $github->authenticate($_ENV['GITHUB_TOKEN'], null, AuthMethod::ACCESS_TOKEN);

    $gitlab = new Gitlab\Client();

    $packagist = new Packagist\Api\Client();

    $results = json_decode(file_get_contents(__DIR__ . "/../tmp/{$count}_popular.json"), true);

    foreach ($results as $result) {
        $packageName = $result['name'];

        $fullDir = "$dir/$packageName";
        if (is_dir($fullDir)) {
            echo "skip $packageName\n";
            continue;
        }

        $package = $packagist->get($packageName);
        $repo = $package->getRepository();

        if (str_contains($repo, 'github.com/')) {
            $githubRepoName = explode('github.com/', $repo)[1];
            [$owner, $repoName] = explode('/', $githubRepoName);
            $repository = $github->api('repo')->show($owner, $repoName);
            $mainBranchName = $repository['default_branch'];

            $fileServer = str_replace('github.com', 'raw.githubusercontent.com', $repo);
            $composerUrl = "$fileServer/$mainBranchName/composer.json";

        } elseif (str_contains($repo, 'gitlab.com/')) {
            $gitlabRepoName = explode('gitlab.com/', $repo)[1];
            [$namespace, $projectName] = explode('/', $gitlabRepoName);

            $projects = $gitlab->projects()->all([
                'search' => $projectName,
                'per_page' => 100,
            ]);

            $projectId = null;
            foreach ($projects as $project) {
                if ($project['path_with_namespace'] === "$namespace/$projectName") {
                    $projectId = $project['id'];
                    break;
                }
            }
            if ($projectId === null) {
                echo "Project not found.\n";
                exit;
            }
            $project = $gitlab->projects()->show($projectId);
            $mainBranchName = $project['default_branch'];

            $composerUrl = "$repo/-/raw/$mainBranchName/composer.json";

        } else {
            dump($packageName);
            dump($repo);
            die();
        }

        $composerData = json_decode(file_get_contents($composerUrl), true);
        if (!$composerData) {
            dump("failed load $composerUrl");
            die();
        }
        mkdir($fullDir, recursive: true);
        file_put_contents("$fullDir/composer.json", json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        yield $composerData;
    }
}

$count = 1000;
foreach (composerDataGenerator($count, __DIR__ . '/../tmp/projects') as $composer) {
    dump($composer['name']);
}

//    mkdir($dir, recursive: true);
//    `git clone $repo $dir`;
//    echo "cloned $name\n";
