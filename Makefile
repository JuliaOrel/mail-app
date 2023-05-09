include .env
build:
	# docker-compose up -d

run:
	symfony server:start -d
	docker-compose up -d

stop:
	symfony server:stop
	docker-compose down

test:
	go test -v ./...
