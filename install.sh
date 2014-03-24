#!/bin/sh


if command -v composer >/dev/null 2>&1;
then
	composer install;

elif ! test -f composer;
then
	php -r "readfile('https://getcomposer.org/installer');" | php -- --filename=composer;
	php composer install;
fi;

php vendor/bin/phing install;

exit 0;
