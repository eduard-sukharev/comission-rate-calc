#!/bin/bash

INPUT_FILE=${1:-'input.csv'}
RECREATE=false

# Runs a local dev environment
if ! docker ps -q &> /dev/null
then
    echo "You must be in docker group or root"
    exit 1
fi

while getopts "rh" option
    do
        case "${option}" in
            r) RECREATE=true;;
            h) echo "Helper script to run app in local docker environment."
            echo "Arguments:"
            echo "    input.csv Input filename to parse"
            echo "Options:"
            echo "    -r Rebuild containers"
            exit 0;
            ;;
    esac
done

if [ ${RECREATE} = true ]
then
    docker-compose build --force-rm --no-cache --pull
    docker-compose run php composer install --dev
fi

docker-compose run php bin/console rate:calc ${INPUT_FILE}
