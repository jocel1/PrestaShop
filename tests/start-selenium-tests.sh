#!/bin/sh

git clone https://github.com/PrestaShop/PSFunctionalTests.git
npm install selenium-standalone@latest
cd ./node_modules/selenium-standalone/bin/; ./selenium-standalone install
DISPLAY=:10 ./selenium-standalone start &> /dev/null &
cd ../../../
npm install
BASEDIR=$(pwd)
echo "[PHP]
      auto_prepend_file='$BASEDIR/tests/set_environment.php'" > ./tests/php.ini;
php -S "$1" -c ./tests/php.ini &
cd PSFunctionalTests/test/itg/1.7
../../../../node_modules/mocha/bin/mocha index.webdriverio.js --URL=$1


