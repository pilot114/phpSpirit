# Что это?

Статистический отчет по 1000 популярным php пакетам с https://packagist.org/

# Как это рабатает

- Cкачиваем список самых скачиваемых пакетов с https://packagist.org/explore/popular.json (bin/popular.php)
- С помощью библиотеки knplabs/packagist-api получаем список composer файлов (bin/download.php)
- склеиваем все composer файлы в composer_cache.json (bin/composer_cache.php)
- Генерируем итоговый report.json с агрегированными данными (bin/build.php)
- Рендерим данные в удобочитаемый html отчет (serve/index.html)
