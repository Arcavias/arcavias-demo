#!/bin/sh


if command -v composer >/dev/null 2>&1;
then
	composer install;

elif ! test -f composer;
then
	if command -v curl >/dev/null 2>&1; then
		curl -sS https://getcomposer.org/installer | php;
	else
		php -r "readfile('https://getcomposer.org/installer');" | php;
	fi

	mv composer.phar composer;
fi;

php composer install;
php vendor/bin/phing install;

exit 0;
