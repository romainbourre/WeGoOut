install:
	composer install

run: install
	php --version
	export Environment=Local && php --server localhost:8080 -t ./src/app/public

