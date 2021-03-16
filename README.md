# Comission rate calculator

## Input data

Operations are given in a CSV file. In each line of the file the following data is provided:

 - operation date in format `Y-m-d`
 - user's identificator, integer
 - user's type, one of `private` or `business`
 - operation type, one of `deposit` or `withdraw`
 - operation amount (for example `2.12` or `3`)
 - operation currency, one of `EUR`, `USD`, `JPY`

## Installation

Project requires PHP 7.4 with bcmath extension.

### Local interpreter

```
composer install
```

### Docker

Building and installing dependencies done with `-r` option of `run.sh` script (which also runs actual console command):
```
./run.sh -r
```

## Running

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

```
./run.sh
```

You may pass input CSV file path as an argument with `-i` option (by default script will parse `./input.csv`).

To run tests:
```
./run.sh -t
```

To run code or tests in [debug mode](https://xdebug.org/docs/step_debug#activate_debugger) add `-d`:
```
./run.sh -d
```
