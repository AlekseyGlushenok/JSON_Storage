#! /bin/bash

function install_dependencies()
{
    sudo apt-get update
    sudo apt-get install -y apache2-utils
    sudo docker run -it --rm -v $(pwd)/app:/app composer install
}

function configurate_project()
{
    sudo chmod 777 -R app/
    clear
    echo "Настройка авторизации"
    echo "Введите имя пользвателя"
    read user
    htpasswd -c ./Nginx/htpasswd $user
    clear
    echo "Настройка приложения"
    echo "Введите директорию для хранения данных"
    read uploadPath
    echo "UPLOAD_PATH=/$uploadPath" >> app/.env
}

function init_run()
{
    install_dependencies
    configurate_project
    docker-compose up -d
    docker ps
}

function run()
{
    docker-compose up -d
    docker ps
}

function stop()
{
    docker-compose stop
}

function help()
{
    echo "-i - Первый запуск"
    echo "-r - Запустить приложение"
    echo "-s - Остроновить приложение"
    echo "-h - help"
}


case $1 in
    -i) init_run ;;
    -r) run ;;
    -s) stop ;;
    -h) help ;;
    *) echo "use -h for help" ;;
esac