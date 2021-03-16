#!/bin/bash

INPUT_FILE='input.csv'
RECREATE=false
RUN_TESTS=false

# Runs a local dev environment
if ! docker ps -q &> /dev/null
then
    echo "You must be in docker group or root"
    exit 1
fi

while getopts "i:rht" option
    do
        case "${option}" in
            i) INPUT_FILE=${OPTARG};;
            t) RUN_TESTS=true;;
            r) RECREATE=true;;
            h) echo "Helper script to run app in local docker environment."
            echo "Options:"
            echo "    -r Rebuild containers"
            echo "    -t Run tests"
            echo "    -i Input file (default: input.csv)"
            exit 0;
            ;;
    esac
done

if [ ${RECREATE} = true ]
then
    docker-compose build --force-rm --no-cache --pull
    docker-compose run php composer install --dev
fi

if [ ${RUN_TESTS} = false ]
then
    docker-compose run -e XDEBUG_SESSION=1 php bin/console rate:calc ${INPUT_FILE}
else
    docker-compose run -e XDEBUG_SESSION=1 php vendor/bin/phpunit
fi

