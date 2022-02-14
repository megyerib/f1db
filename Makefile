image := f1db_xampp
container := $(image)_instance
mysql_volume := $(image)_mysql_volume

htdocs_dir := $(shell pwd)/public_html
http_port := 80
db_file := sql/f1.sql

all:
	docker build -t $(image) .
	docker volume create $(mysql_volume)
	docker run --name $(container) -d -v $(mysql_volume):/tmp/mysql $(image)
	sleep 2
	docker cp $(db_file) $(container):/tmp/db.sql
	docker exec -it $(container) bash -c ' \
		/opt/lampp/bin/mysql -u root < /tmp/db.sql && \
		/opt/lampp/lampp stop && \
		mv /opt/lampp/var/mysql/* /tmp/mysql/ && \
		chown mysql /tmp/mysql/'
	docker stop $(container)
	docker rm $(container)

run:
	docker run --name $(container) \
		-p $(http_port):80 \
		-d \
		-v $(htdocs_dir):/opt/lampp/htdocs \
		-v $(mysql_volume):/opt/lampp/var/mysql \
		$(image)

stop:
	docker stop $(container)
	docker rm $(container)

bash:
	docker exec -it $(container) bash

clean:
	docker rmi -f $(image)
	docker volume rm -f $(mysql_volume)