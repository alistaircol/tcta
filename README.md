## Pre-requisites

Application is in a containers.

* `tcta`

 ## Installation

We have our app in mounted in `tcta` container.

We need to bring our container up and install `composer` dependencies.

```
docker-compose up -d
docker exec -it -u 1000 tcta bash -c "composer install"
```

I'm using `-u 1000` so I don't get any files on my host created with default `uid` of `0` (root).

## Development

XDebug is installed in the container, so you should edit `.containers/php/config/php/xdebug.ini` and update the `xdebug.remote_host` to your host machine IP address.

Any new utilities to install in the container, to rebuild the container:

```
docker-compose down
docker-compose up -d --build tcta
```

## Testing

```
docker exec -it -u 1000 tcta bash -c "./vendor/bin/phpunit"
# pretty output
docker exec -it -u 1000 tcta bash -c "./vendor/bin/phpunit --testdox"
```

## Design Choices

* Using `symfony/command` for a nice command interface 
* Using `league/csv` because it's nicer than native PHP csv functions
* Using `carbon/carbon` for date logic

```
php cakes report
```
