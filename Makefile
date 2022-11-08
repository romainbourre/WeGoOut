install:
	composer install

tests: install
	./vendor/bin/phpunit  tests/

run: install
	php --version
	export Environment=Local && php --server localhost:8080 -t ./src/WebApp/public

