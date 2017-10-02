#!/bin/sh

npm install prestashop/PSFunctionalTests
npm install selenium-standalone@latest
cd ../node_modules/selenium-standalone/bin/; ./selenium-standalone install
DISPLAY=:10 ./selenium-standalone start &> /dev/null &
cd ../../../../
php -S 127.0.0.1:8020 -c ./tests/php.ini

