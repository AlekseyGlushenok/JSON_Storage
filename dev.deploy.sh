#! /bin/bash

function install_dependencies()
{
    sudo apt-get update
    sudo apt-get install -y python3-pip apache2-utils
    which docker
    if ! which docker > /dev/null; then
        wget -O docker.sh https://get.docker.com
        bash docker.sh
    fi
    pip3 install docker-compose
    sudo docker run -it --rm -v $(pwd)/app:/app composer install
}

function configurate_project()
{
    echo "Введите имя пользвателя"
    read user
    htpasswd -c ./Nginx/htpasswd $user
}

function run()
{
    install_dependencies
    configurate_project
    docker-compose up -d
    docker ps
}

run