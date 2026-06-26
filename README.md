### Установка

Добавить `"./vendor/4geo35/catalog-exchange/src/resources/views/components/**/*.blade.php",
        "./vendor/4geo35/catalog-exchange/src/resources/views/admin/**/*.blade.php",
        "./vendor/4geo35/catalog-exchange/src/resources/views/livewire/admin/**/*.blade.php",` в `tailwind.admin.config.js`, созданный в пакете `tailwindcss-theme`.

Запустить миграции для создания таблиц `php artisan migrate`

### Использование

По умолчанию для 1С настроен адрес `exchange/catalog`, можно поменять в конфиге `catalog-exhcnage.routePrefix`.  
Через параметр `catalog-exhcnage.useQueue` регулируется, что обработка будет идти через очередь, т.е. при запуске файла срабатывает событие запуска и слушатель попадает в очередь, слушателя можно поменять через `catalog-exchange.customFireProcessExchangeFileListener`.  
Настройки файла также находятся в конфиге, вложенность для элементов обозначается через `->`.

Обработку можно переопределить, назначив новый класс для аксессоров фасадов `catalog-exhcnage.customExchangeLoadFileActionsManager / customExchangeParserActionsManager / customExchangeItemsActionsManager / customExchangeProtocolActionsManager / customExchangeAuthActionsManager`.

В админке есть страница, где отображаются текущие выгрузки, их можно создавать вручную, загружать туда файлы и запускать. Прогресс отображается под каждым файлом.

Если выгрузка большая либо происходит часто, лучше отключить очередь и поставить команду в расписание, чтобы выгрузка не занимала очередь на другие задачи.  
В `routes/console.php` добавить:

    Schedule::command('run:exchange-file')->everyMinute();

Команда находит файл, который помечен на обработку, но не запущен и не закончен, и начинает его обработку, так же как слушатель.

Для очистки старых выгрузок, нужно добавить в расписание команду (Через сколько дней выгрузка считается старой, указывается в `catalog-exhcnage.oldCmlsDays`):

    Schedule::command('run:clear-cml-files')->dailyAt("5:00");
