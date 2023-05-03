include .env
build:
	# docker-compose up -d

run:
	symfony server:start -d
	docker-compose up -d

test:
	go test -v ./...
