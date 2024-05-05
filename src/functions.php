<?php

use Github\{AuthMethod, Client};

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

function composerDataGenerator(int $count, string $dir) : \Generator
{
    $github = new Client();
    $github->authenticate(getenv('GITHUB_TOKEN'), null, AuthMethod::ACCESS_TOKEN);

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
    }
    return $stats;
}



class Field
{
    public int $count = 0;
    public array $data = [];

    public function __construct(
        public string $name,
        public string $desc,
        array $data,
    ) {
        $this->count = count($data);
    }
}

/**
 * TODO: create CLI report
 */
class Report
{
    protected array $data = [];
    protected array $packages = [];

    // for output
    protected array $nonStandardKeys = [];

    public function __construct(string $composerName, string $packagesListName)
    {
        $this->data = json_decode(file_get_contents($composerName), true);
        $this->packages = json_decode(file_get_contents($packagesListName), true);
    }

    public function buildSummary(string $path, string $dirComposerFiles): void
    {
        $result = [
            'head' => $this->head($dirComposerFiles),
            'composerFields' => []
        ];
        $this->keys();

        foreach ($this->data as $field => $packages) {
            $methodName = str_replace('-', '_', $field);
            $result['composerFields'][$field] = (array) $this->$methodName($packages);
        }
        file_put_contents($path, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Статистика по ключам композер файла
     */
    protected function keys(): void
    {
        $standardComposerKeys = [
            'name',
            'description',
            'version',
            'type',
            'keywords',
            'homepage',
            'readme',
            'time',
            'license',
            'authors',
            'support',
            'funding',
            // Package links
            'require',
            'require-dev',
            'conflict',
            'replace',
            'provide',
            'suggest',

            'autoload',
            'autoload-dev',
            'include-path',
            'target-dir',
            'minimum-stability',
            'prefer-stable',
            'repositories',
            'config',
            'scripts',
            'extra',
            'bin',
            'archive',
            'abandoned',
            'non-feature-branches',
        ];

        $keys = array_keys($this->data);
        $countKeys = count($keys);
        dump("Всего использованных ключей: $countKeys");

        $nonStandardKeys = array_diff($keys, $standardComposerKeys);
        $countNonStandardKeys = count($nonStandardKeys);
        dump("Нестандартных ключей: $countNonStandardKeys");

        $this->nonStandardKeys = array_map(
            fn($x) => array_map(
                fn($y) => json_decode(base64_decode($y), true),
                $x
            ),
            array_filter(
                $this->data,
                fn($key) => in_array($key, $nonStandardKeys), ARRAY_FILTER_USE_KEY
            )
        );

        $countStandardComposerKeys = count($standardComposerKeys);
        dump("Стандартных ключей: $countStandardComposerKeys");

        $this->data = array_filter($this->data, fn($key) => !in_array($key, $nonStandardKeys), ARRAY_FILTER_USE_KEY);

        // sort by popularity
        uasort($this->data, fn($a, $b) => count($a) < count($b) ? 1 : -1);
    }

    protected function head(string $dirComposerFiles): array
    {
        $finder = new Symfony\Component\Finder\Finder();
        $finder->files()->in($dirComposerFiles)->name(['*.json']);

        $packageNames = [];
        foreach ($finder as $item) {
            $packageNames[] = str_replace('/composer.json', '', $item->getRelativePathname());
        }

        return [
            'repoCount' => count($packageNames),
        ];
    }



    protected function description(array $input): Field
    {
        return new Field('description', 'Description of package', $input);
    }

    protected function require_dev(array $input): Field
    {
        return new Field('require-dev', 'Dev dependencies', $input);
    }

    protected function homepage(array $input): Field
    {
        return new Field('homepage', 'Home page', $input);
    }

    protected function autoload_dev(array $input): Field
    {
        return new Field('autoload-dev', 'Autoload for dev', $input);
    }

    protected function minimum_stability(array $input): Field
    {
        return new Field('minimum-stability', 'stability', $input);
    }

    protected function prefer_stable(array $input): Field
    {
        return new Field('prefer-stable', 'prefer stable', $input);
    }

    protected function provide(array $input): Field
    {
        return new Field('provide', 'provide', $input);
    }

    protected function replace(array $input): Field
    {
        return new Field('replace', 'replace', $input);
    }

    protected function version(array $input): Field
    {
        return new Field('version', 'version', $input);
    }

    protected function include_path(array $input): Field
    {
        return new Field('include-path', 'include path', $input);
    }

    protected function name(array $input): Field
    {
        $field = new Field('name', 'Name of package', $input);

        foreach ($input as $data) {
            $data = json_decode(base64_decode($data), true);
            [$vendor, $name] = explode('/', $data);
            $field->data[$vendor][] = $name;
        }
        return $field;
    }

    /**
     * Заброшенные пакеты:
     * старый пакет => новый пакет (или bool)
     */
    protected function abandoned(array $input): Field
    {
        $field = new Field('abandoned', 'abandoned', $input);

        foreach ($input as $package => $data) {
            $newPackage = json_decode(base64_decode($data), true);
            $field->data[$package] = $newPackage;
        }
        return $field;
    }

    /**
     * Используется при создании архива через composer archive
     * name - имя архива (игнорируем)
     * exclude - что не должно попасть в архив
     *
     * пакет => [паттерны]
     */
    protected function archive(array $input): Field
    {
        $field = new Field('archive', 'archive', $input);

        foreach ($input as $package => $data) {
            $archive = json_decode(base64_decode($data), true);
            $field->data[$package] = $archive['exclude'];
        }
        return $field;
    }

    /**
     * Авторы
     * name: Имя, обычно реальное
     * email: email =)
     * homepage: Вебсайт автора
     * role: роль в проекте
     *
     * автор => [
     *      [email],
     *      [homepage],
     *      [пакет => роль]
     * ]
     */
    protected function authors(array $input): Field
    {
        $field = new Field('authors', 'authors of package', $input);

        foreach ($input as $package => $data) {
            $authors = json_decode(base64_decode($data), true);
            foreach ($authors as $author) {
                $field->data[$author['name']] ??= [
                    [], [], []
                ];
                if (isset($author['email'])) {
                    $field->data[$author['name']][0][] = $author['email'];
                    $field->data[$author['name']][0] = array_unique($field->data[$author['name']][0]);
                }
                if (isset($author['homepage'])) {
                    $field->data[$author['name']][1][] = trim(str_replace('http://', 'https://', $author['homepage']), '/');
                    $field->data[$author['name']][1] = array_unique($field->data[$author['name']][1]);
                }
                $field->data[$author['name']][2][$package] = $author['role'] ?? 'Developer';
            }
        }

        return $field;
    }

    /**
     * Варианты автозагрузки
     */
    protected function autoload(array $input): Field
    {
        $field = new Field('autoload', 'autoload', $input);

        foreach ($input as $package => $data) {
            $auto = json_decode(base64_decode($data), true);
            foreach ($auto as $type => $config) {
                $field->data[$type][$package] ??= [];
                $field->data[$type][$package][] = $config;
            }
        }
        return $field;
    }

    /**
     * Список команд пакетов
     */
    protected function bin(array $input): Field
    {
        $field = new Field('bin', 'bin', $input);

        foreach ($input as $package => $data) {
            $bin = json_decode(base64_decode($data), true);
            $field->data[$package] = $bin;
        }
        return $field;
    }

    /**
     * ~50 разных опций для Composer
     */
    protected function config(array $input): Field
    {
        $field = new Field('config', 'config', $input);

        foreach ($input as $package => $data) {
            $config = json_decode(base64_decode($data), true);
            foreach ($config as $optionName => $option) {
                $field->data[$optionName] ??= [];
                $field->data[$optionName][$package] = $option;
            }
        }
        return $field;
    }

    protected function conflict(array $input): Field
    {
        $field = new Field('conflict', 'conflict', $input);

        foreach ($input as $package => $data) {
            $conflict = json_decode(base64_decode($data), true);
            $field->data[$package] = $conflict;
        }
        return $field;
    }

    /**
     * Кастомные опции для плагинов
     */
    protected function extra(array $input): Field
    {
        $field = new Field('extra', 'extra', $input);

        foreach ($input as $package => $data) {
            $extra = json_decode(base64_decode($data), true);
            foreach ($extra as $optionName => $option) {
                $field->data[$optionName] ??= [];
                $field->data[$optionName][$package] = $option;
            }
        }
        return $field;
    }

    /**
     * Информация о спонсорстве
     */
    protected function funding(array $input): Field
    {
        $field = new Field('funding', 'funding', $input);

        foreach ($input as $package => $data) {
            $funding = json_decode(base64_decode($data), true);
            foreach ($funding as $type => $fund) {
                $field->data[$fund['type']] ??= [];
                $field->data[$fund['type']][$package] = $fund['url'];
            }
        }
        return $field;
    }

    protected function keywords(array $input): Field
    {
        $field = new Field('keywords', 'keywords', $input);

        foreach ($input as $package => $data) {
            $keywords = json_decode(base64_decode($data), true);
            foreach ($keywords as $keyword) {
                $field->data[$keyword] ??= [];
                $field->data[$keyword][] = $package;
            }
        }
        return $field;
    }

    protected function license(array $input): Field
    {
        $field = new Field('license', 'license', $input);

        foreach ($input as $package => $data) {
            $license = json_decode(base64_decode($data), true);
            if (!is_array($license)) {
                $license = [$license];
            }
            foreach ($license as $item) {
                $field->data[$item] ??= [];
                $field->data[$item][] = $package;
            }
        }
        return $field;
    }

    protected function stability(array $input): Field
    {
        $field = new Field('stability', 'stability', $input);

        foreach ($input as $package => $data) {
            $stability = json_decode(base64_decode($data), true);
            $field->data[$stability] ??= [];
            $field->data[$stability][] = $package;
        }
        return $field;
    }

    /**
     * Пакеты не с Packagist
     */
    protected function repositories(array $input): Field
    {
        $field = new Field('repositories', 'repositories', $input);

        foreach ($input as $package => $data) {
            $repositories = json_decode(base64_decode($data), true);
            foreach ($repositories as $repository) {
                if (!isset($repository['type'])) {
                    continue;
                }
                $field->data[$repository['type']][$package] ??= [];
                $field->data[$repository['type']][$package][] = $repository['package'] ?? $repository['url'];
            }
        }
        return $field;
    }

    protected function require(array $input): Field
    {
        $field = new Field('require', 'require', $input);

        foreach ($input as $package => $data) {
            $required = json_decode(base64_decode($data), true);
            foreach ($required as $dep => $version) {
                $field->data[$dep][$version] ??= [];
                $field->data[$dep][$version][] = $package;
            }
        }
        return $field;
    }

    protected function scripts(array $input): Field
    {
        // TODO: нужно через regex вытащить типичные используемые утилиты, сгруппировать по ним
        // phpunit php-cs-fixer psalm phpcs paratest phpcbf phpstan
        // также стоит выбрать названия команд
        return new Field('scripts', 'scripts', $input);
    }

    protected function suggest(array $input): Field
    {
        $field = new Field('suggest', 'suggest', $input);

        foreach ($input as $package => $data) {
            $suggest = json_decode(base64_decode($data), true);
            foreach ($suggest as $pack => $desc) {
                $field->data[$pack] ??= [];
                $field->data[$pack][] = $desc;
            }
        }
        return $field;
    }

    protected function support(array $input): Field
    {
        $field = new Field('suggest', 'suggest', $input);

        foreach ($input as $package => $data) {
            $support = json_decode(base64_decode($data), true);
            foreach ($support as $type => $item) {
                $field->data[$type] ??= [];
                $field->data[$type][$package] = $item;
            }
        }
        return $field;
    }

    protected function type(array $input): Field
    {
        $field = new Field('type', 'type', $input);

        foreach ($input as $package => $data) {
            $type = json_decode(base64_decode($data), true);
            $field->data[$type] ??= [];
            $field->data[$type][] = $package;
        }
        return $field;
    }
}