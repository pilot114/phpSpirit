# Что это?

Статистический отчет по 1000 популярным php пакетам с https://packagist.org/

# Как это работает

- Cкачиваем список самых скачиваемых пакетов с https://packagist.org/explore/popular.json
- С помощью библиотеки knplabs/packagist-api получаем список composer файлов
- склеиваем все composer файлы в composer_cache.json
- Генерируем итоговый report.json с агрегированными данными
- Рендерим данные в удобочитаемый html отчет

# run

    GITHUB_TOKEN=token php ./bin/build.php