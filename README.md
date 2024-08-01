# PHP DDNS

A simple console application that updates cloudflare A record with a new external IP.

Currently used as a simple "ddns" solution for VPN access.

## Setup

1. Install dependencies

``` shell
$ composer install
```

2. Configure environment with .env

``` shell
$ cp .env.example .env
$ vim .env
```

You're going to need Cloudflare API key, API email and Zone ID

3. Run the command

``` shell
$ php ddns update
```

4. Setup cron job

```shell
*/15 * * * * php /path/to/project/src/boostrap.php ddns:update-a-record
```
