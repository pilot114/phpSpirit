<?php

include_once './vendor/autoload.php';

class Report
{
    protected array $data = [];

    public function __construct(string $fileName)
    {
        $this->data = json_decode(file_get_contents($fileName), true);
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
        $prep = array_filter($counts, fn($key) => in_array($key, $nonStandardKeys), ARRAY_FILTER_USE_KEY);
        arsort($prep);
        dump($prep);

        $countStandardComposerKeys = count($standardComposerKeys);
        dump("Стандартных ключей: $countStandardComposerKeys");
        $prep = array_filter($counts, fn($key) => !in_array($key, $nonStandardKeys), ARRAY_FILTER_USE_KEY);
        arsort($prep);
        dump($prep);
    }

    public function buildSummary()
    {
        $this->keys();

// для простых случаев
//dump(array_map(fn($x) => json_decode(base64_decode($x)), $results['type']));

//        $prepared = authors($results['authors']);

//dump(array_map(fn($x) => count($x), $prepared));
//        dump(array_keys($prepared));
        return null;
    }
}

$report = new Report('./composer_cache.json');
$data = $report->buildSummary();

/**
 * Заброшенные пакеты:
 * старый пакет => новый пакет (или bool)
 */
function abandoned(array $input): array
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
function archive(array $input): array
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
function authors(array $input): array
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
function autoload(array $input): array
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
function bin(array $input): array
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
function config(array $input): array
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

function conflict(array $input): array
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
function extra(array $input): array
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
function funding(array $input): array
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

function keywords(array $input): array
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

function license(array $input): array
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

function stability(array $input): array
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
function repositories(array $input): array
{
    $output = [];
    foreach ($input as $package => $data) {
        $repositories = json_decode(base64_decode($data), true);
        foreach ($repositories as $repository) {
            $output[$repository['type']][$package] ??= [];
            $output[$repository['type']][$package][] = $repository['package'] ?? $repository['url'];
        }
    }
    return $output;
}

function required(array $input): array
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

function scripts(array $input): array
{
    // TODO: нужно через regex вытащить типичные используемые утилиты, сгруппировать по ним
    // phpunit php-cs-fixer psalm phpcs paratest phpcbf phpstan
    // также стоит выбрать названия команд
}

function suggest(array $input): array
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

function support(array $input): array
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

function type(array $input): array
{
    $output = [];
    foreach ($input as $package => $data) {
        $type = json_decode(base64_decode($data), true);
        $output[$type] ??= [];
        $output[$type][] = $package;
    }
    return $output;
}
