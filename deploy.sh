#! /bin/bash
function install_dependencies()
{
	docker run -it --rm -v $(pwd)/app:/app composer install
}

function run_project()
{
	docker-compose up -d
	docker ps
}

function stop_project()
{
	docker-compose down
}

function about()
{
    echo -e "This project is JSON data store.
             \rFor more information go to url"
}

function print_help_information()
{
	echo
	echo "-a - about project"
	echo "-i - install dependencies for project"
	echo "-r - run project(dependencies must be install, u can use -i -r)"
	echo "-s - stop project"
	echo "-h - print help information"
}



while [ -n "$1" ]
do
	case $1 in
	    -a) about ;;
		-i) install_dependencies ;;
		-r) run_project ;;
		-h) print_help_information ;;
		-s) stop_project ;;
		*) echo "Use -h for help";;
	esac
shift
done
