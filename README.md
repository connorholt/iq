## Сборка проекта
- Склонировать репу *git clone git@github.com:connorholt/iq.git*
- В корне проекта запустить *make dev-start*, которая объединяет следующие команды:
-- docker-compose build --no-cache (может занять некоторое время)
-- chmod 777 на папку с логами
-- composer update
-- cache:clear
-- doctrine:migrations:migrate

- http://localhost:8080/app_dev.php/

## Запуск проекта
- Первый раз нужно сделать все из *Сборка проекта*
- Запустить команду make dev-up
- Если в консоль вывились состояния консьюмеров, это для отладки, просто нажмите enter
- Для вставки начальных даных в таблицу баланса, нужно открыть главную страницу *localhost:8080/app_dev.php/* в будущем вынесу в seed

- После запуска сервисы доступны:
-- приложение *localhost:8080/app_dev.php/*
-- база данных (админер) *localhost:5000*
-- rabbitmq *localhost:15672*
-- kibana *localhost:81*

## Технологии используемые в проекте
- Docker
- Php 7.1
- Postgres 9.6
- Rabbitmq
- Redis
- Nginx, php-fpm
- Supervisor

## Принцип работы
- Супервизор запускает консьюмер billingConsumer (10 процессов, это можно поменять)
- Консьюмеры разбирают очередь billing, куда приходят сообщения с определенными типами (примеры сообщение можно посмотреть тут: https://github.com/connorholt/iq/blob/master/backend/src/MemberBotBundle/Controller/DefaultController.php)
- По типу сообщения создается команда, которая кладется в комманд бас
- Комманд бас передает команду нужному обработчику
- Обработчик ставит лок в редисе для пользователя, чтобы другие процессы не могли поменять ему баланс, сообщения которые не могут быть выполнены т.к. стоит лок возвращают статус reject (это можно легко изменить).
- Далее в транзакции выполняются запросы в базу данных
- Если было какое-то исключение или если все отработало локи снимаются, и файрится событие.
- В очередь отправляем подтверждение, что сообщение обработано

## Доработки
- Phpunit
- EventListener
- Создать сиды для вставки изначальных данных

## Комманды
- make dev-start *билдит контейнеры, запускает, ставит все права как надо*
- make dev-build *собирает все*
- make dev-down *все выключает*
- make dev-clear *удалит все образы и контейнеры*

