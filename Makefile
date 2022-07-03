.PHONY: build
build:
	docker build . -t crawla

.PHONY: attach
attach: build
	docker run -ti -v $$(pwd):/app crawla /bin/sh

.PHONY: test
test: build
	docker run -ti -v $$(pwd):/app crawla vendor/bin/phpunit --testdox