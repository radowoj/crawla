.PHONY: build
build:
	docker build . -t crawla

.PHONY: sh
sh: build
	docker run -ti -v $$(pwd):/app crawla /bin/sh

.PHONY: test
test: build
	docker run -ti -v $$(pwd):/app crawla vendor/bin/phpunit --testdox

.PHONY: coverage
coverage:
	docker run -ti -v $$(pwd):/app crawla vendor/bin/phpunit --testdox --coverage-html=coverage/html