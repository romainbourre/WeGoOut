install:
	composer install

tests: install
	./vendor/bin/phpunit  tests/

watch.tests:
	./vendor/bin/phpunit-watcher watch --bootstrap vendor/autoload.php tests/

run: install
	php --version
	export Environment=Local && php --server localhost:8080 -t ./src/WebApp/public

