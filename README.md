# TCTA

Generate a report.

## Usage

I decided to use a CSV with headers instead of text file. This meant the column order is not important.

Example file:

`example.csv`

```
Name,DOB
Steve,1992-10-14
Mary,1989-06-21
```

---

To create a report for this file, pass the file contents via `stdin`.

Example:

```
docker exec -i -u 1000 tcta bash -c "php cakes report" < example.csv 
```

This will output:

```
Date,"Number of Small Cakes","Number of Large Cakes","Names of people getting cake"
2020-06-23,1,0,Mary
2020-10-15,1,0,Steve
```

---

You can save this to a file. Example `output.csv`:

```
docker exec -i -u 1000 tcta bash -c "php cakes report" < example.csv > output.csv
```


## Pre-requisites

Application is in a Docker container.

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

Example output:

```
docker exec -it -u 1000 tcta bash -c "./vendor/bin/phpunit --testdox "
PHPUnit 9.0.1 by Sebastian Bergmann and contributors.

Next Leap Year
 ✔ Next leap year from 2019 is 2020
 ✔ Next leap year from 1997is 2000
 ✔ Next leap year from 2097is 2104
 ✔ Person birthday on leap year
 ✔ Person born in leap year will have birthday in next leap year when requested in between dates

Person Cake Date
 ✔ Dave born 26 june 1986 birthday friday 26 june gets a cake on monday 29 june 2020
 ✔ Rob born 5 july 1950 birthday sunday 5 july gets a cake on tuesday 7 july 2020
 ✔ Born 1 january birthday 1 january 2020 gets a cake on friday 3 january 2020
 ✔ Born 24 december birthday thursday 24 gets a cake on monday 28 december 2020
 ✔ Born thursday 31 december birthday thursday 31 gets cake on monday 4 january 2021

People Cake Person
 ✔ Sam born 13 july kate born 14 july share a large cake on 15 july
 ✔ Two born same date will share a large cake the next working day
 ✔ Three born same date will share a large cake the next working day and the third will have a small cake 2 working days later
 ✔ Alex born 20 july jen born 21 july pete born 22 july that alex and jen share large cake 22 july and pete has small cake on 24 july
 ✔ One person born day before two others share same birthday that a large cake is given on second day to share and one person from shared birthday will receive small cake
 ✔ One person born end of year is not incuded in current year distribution since it is moved to new year
 ✔ Steve born 14 oct gets small cake 15 oct mary born 21 june gets small cake 23 june

Time: 51 ms, Memory: 8.00 MB

OK (17 tests, 83 assertions)
```

## Design Choices

* Using `symfony/command` for a nice command interface 
* Using `league/csv` because it's nicer than native PHP csv functions
* Using `carbon/carbon` for date logic
