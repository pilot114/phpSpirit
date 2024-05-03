<?php

include_once './vendor/autoload.php';

use Symfony\Component\Finder\Finder;

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

    public function buildSummary(string $path): void
    {
        $result = [
            'head' => $this->head(),
            'composerFields' => []
        ];
        $this->keys();

        foreach ($this->data as $field => $packages) {
            if (method_exists($this, $field)) {
                dump("> $field()");
                $item = $this->$field($packages);
            } else {
                dump("# $field");
                $item = $this->common($packages, $field);
            }
            $result['composerFields'][$field] = $item;
        }
        file_put_contents($path, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function head(): array
    {
//        $finder = new Finder();
//        foreach ($this->data['_projects'] as $projectDir) {
//            $dirSize = `du -sh $projectDir`;
//            dump($dirSize);
//            die();
//
//            dump($projectDir);
//            $phpFiles = $finder->files()->in($projectDir)->name(['*.php']);
//            foreach ($phpFiles as $file) {
//                $size = $file->getSize();
//                echo $size;
//                die();
//            }
//        }
//        unset($this->data['_projects']);
        return [
            'repoCount'    => 0,
            'totalSize'    => '',
            'totalGitSize' => '',
            'totalPhpSize' => '',
        ];
    }

    protected function common(array $input, string $name): array
    {
        $item = [
            'name' => $name,
            'desc' => 'desc',
            'count' => 0,
            'data' => [],
        ];
        foreach ($input as $packageName => $x) {
            $item['data'][$packageName] = json_decode(base64_decode($x));
        }
        return $item;
    }

    protected function name(array $input): array
    {
        $item = [
            'name' => 'name',
            'desc' => 'desc',
            'count' => 0,
            'data' => [],
        ];
        $output = [];
        foreach ($input as $package => $data) {
            $data = json_decode(base64_decode($data), true);
            [$vendor, $name] = explode('/', $data);
            $output[$vendor][] = $name;
        }
        return $output;
    }

    /**
     * Статистика по ключам композер файла
     */
    protected function keys()
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

        $counts = [];
        foreach ($this->data as $composerFieldName => $packages) {
            $counts[$composerFieldName] = count($packages);
        }
        $keys = array_keys($this->data);

        $countKeys = count($keys);
        dump("Всего использованных ключей: $countKeys");

        $nonStandardKeys = array_diff($keys, $standardComposerKeys);
        $countNonStandardKeys = count($nonStandardKeys);
        dump("Нестандартных ключей: $countNonStandardKeys");

        $prep = array_filter($this->data, fn($key) => in_array($key, $nonStandardKeys), ARRAY_FILTER_USE_KEY);
        $prep = array_map(
            fn($x) => array_map(
                fn($y) => json_decode(base64_decode($y), true),
                $x
            ),
            $prep
        );
        $this->nonStandardKeys = $prep;

        $countStandardComposerKeys = count($standardComposerKeys);
        dump("Стандартных ключей: $countStandardComposerKeys");
        $this->data = array_filter($this->data, fn($key) => !in_array($key, $nonStandardKeys), ARRAY_FILTER_USE_KEY);

        // sort by popularity
        uasort($this->data, fn($a, $b) => count($a) < count($b) ? 1 : -1);
    }

    /**
     * Заброшенные пакеты:
     * старый пакет => новый пакет (или bool)
     */
    protected function abandoned(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $newPackage = json_decode(base64_decode($data), true);
            $output[$package] = $newPackage;
        }
        return $output;
    }

    /**
     * Используется при создании архива через composer archive
     * name - имя архива (игнорируем)
     * exclude - что не должно попасть в архив
     *
     * пакет => [паттерны]
     */
    protected function archive(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $archive = json_decode(base64_decode($data), true);
            $output[$package] = $archive['exclude'];
        }
        return $output;
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
    protected function authors(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $authors = json_decode(base64_decode($data), true);
            foreach ($authors as $author) {
                $output[$author['name']] ??= [
                    [], [], []
                ];
                if (isset($author['email'])) {
                    $output[$author['name']][0][] = $author['email'];
                    $output[$author['name']][0] = array_unique($output[$author['name']][0]);
                }
                if (isset($author['homepage'])) {
                    $output[$author['name']][1][] = trim(str_replace('http://', 'https://', $author['homepage']), '/');
                    $output[$author['name']][1] = array_unique($output[$author['name']][1]);
                }
                $output[$author['name']][2][$package] = $author['role'] ?? 'Developer';
            }
        }
        return $output;
    }

    /**
     * Варианты автозагрузки
     */
    protected function autoload(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $auto = json_decode(base64_decode($data), true);
            foreach ($auto as $type => $config) {
                $output[$type][$package] ??= [];
                $output[$type][$package][] = $config;
            }
        }
        return $output;
    }

    /**
     * Список команд пакетов
     */
    protected function bin(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $bin = json_decode(base64_decode($data), true);
            $output[$package] = $bin;
        }
        return $output;
    }

    /**
     * ~50 разных опций для Composer
     */
    protected function config(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $config = json_decode(base64_decode($data), true);
            foreach ($config as $optionName => $option) {
                $output[$optionName] ??= [];
                $output[$optionName][$package] = $option;
            }
        }
        return $output;
    }

    protected function conflict(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $conflict = json_decode(base64_decode($data), true);
            $output[$package] = $conflict;
        }
        return $output;
    }

    /**
     * Кастомные опции для плагинов
     */
    protected function extra(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $extra = json_decode(base64_decode($data), true);
            foreach ($extra as $optionName => $option) {
                $output[$optionName] ??= [];
                $output[$optionName][$package] = $option;
            }
        }
        return $output;
    }

    /**
     * Информация о спонсорстве
     */
    protected function funding(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $funding = json_decode(base64_decode($data), true);
            foreach ($funding as $type => $fund) {
                $output[$fund['type']] ??= [];
                $output[$fund['type']][$package] = $fund['url'];
            }
        }
        return $output;
    }

    protected function keywords(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $keywords = json_decode(base64_decode($data), true);
            foreach ($keywords as $keyword) {
                $output[$keyword] ??= [];
                $output[$keyword][] = $package;
            }
        }
        return $output;
    }

    protected function license(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $license = json_decode(base64_decode($data), true);
            if (!is_array($license)) {
                $license = [$license];
            }
            foreach ($license as $item) {
                $output[$item] ??= [];
                $output[$item][] = $package;
            }
        }
        return $output;
    }

    protected function stability(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $stability = json_decode(base64_decode($data), true);
            $output[$stability] ??= [];
            $output[$stability][] = $package;
        }
        return $output;
    }

    /**
     * Пакеты не с Packagist
     */
    protected function repositories(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $repositories = json_decode(base64_decode($data), true);
            foreach ($repositories as $repository) {
                if (!isset($repository['type'])) {
                    continue;
                }
                $output[$repository['type']][$package] ??= [];
                $output[$repository['type']][$package][] = $repository['package'] ?? $repository['url'];
            }
        }
        return $output;
    }

    protected function require(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $required = json_decode(base64_decode($data), true);
            foreach ($required as $dep => $version) {
                $output[$dep][$version] ??= [];
                $output[$dep][$version][] = $package;
            }
        }
        return $output;
    }

    protected function scripts(array $input): array
    {
        // TODO: нужно через regex вытащить типичные используемые утилиты, сгруппировать по ним
        // phpunit php-cs-fixer psalm phpcs paratest phpcbf phpstan
        // также стоит выбрать названия команд
        return [];
    }

    protected function suggest(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $suggest = json_decode(base64_decode($data), true);
            foreach ($suggest as $pack => $desc) {
                $output[$pack] ??= [];
                $output[$pack][] = $desc;
            }
        }
        return $output;
    }

    protected function support(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $support = json_decode(base64_decode($data), true);
            foreach ($support as $type => $item) {
                $output[$type] ??= [];
                $output[$type][$package] = $item;
            }
        }
        return $output;
    }

    protected function type(array $input): array
    {
        $output = [];
        foreach ($input as $package => $data) {
            $type = json_decode(base64_decode($data), true);
            $output[$type] ??= [];
            $output[$type][] = $package;
        }
        return $output;
    }
}

$tmpDir = __DIR__ . '/../tmp/';
$report = new Report("$tmpDir/composer_cache.json", "$tmpDir/1000_popular.json");
$report->buildSummary('./serve/src/report.json');