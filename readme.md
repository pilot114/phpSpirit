# Description

Статистический отчет по 10000 популярным php пакетам с https://packagist.org/

# Как это рабатет

- С popular.json cкачиваем список пакетов с https://packagist.org/explore/popular.json
- С помощью библиотеки knplabs/packagist-api получаем список репозиториев, скачиваем в ./projects
- composer_cache.php проходит по скачанным репозиториям и скливает все композер-файлы в composer_cache.json
- build.php генерирует отчет