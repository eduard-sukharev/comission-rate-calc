# Comission rate calculator

## Input data

Operations are given in a CSV file. In each line of the file the following data is provided:

 - operation date in format `Y-m-d`
 - user's identificator, integer
 - user's type, one of `private` or `business`
 - operation type, one of `deposit` or `withdraw`
 - operation amount (for example `2.12` or `3`)
 - operation currency, one of `EUR`, `USD`, `JPY`

## Running

Project requires PHP 7.4 with bcmath extension.

### Local interpreter
To run actual script:

```
php ./bin/console rate:calc input.csv
```

To run tests:

```
php ./vendor/bin/phpunit
```

### Docker
To run in docker simply call:

```
./run.sh
```

You may pass input CSV file path as an argument with `-i` option (by default script will parse `./input.csv`).

To run tests:
```
./run.sh -t
```
