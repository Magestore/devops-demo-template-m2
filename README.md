

Use cases
----

#### Run service live:

```
docker run -itd -p 80:80 -p 443:443 -p 3306:3306 \
	-v $(pwd)/data/www:/var/www/html \
	-v $(pwd)/data/mysql:/var/lib/mysql \
	-v $(pwd)/config/apache2:/etc/apache2 \
	--name starter_m2 \
	thinlt/omnichannel_magento2
```

#### Create a temporary container for testing purposes:

```
docker run -i -t --rm fauria/lamp bash
```

#### Create a temporary container to debug a web app:

```
docker run --rm -p 8080:80 -e LOG_STDOUT=true -e LOG_STDERR=true -e LOG_LEVEL=debug -v /my/data/directory:/var/www/html fauria/lamp
```

#### Create a container linking to another [MySQL container](https://registry.hub.docker.com/_/mysql/):

```
docker run -d --link my-mysql-container:mysql -p 8080:80 -v /my/data/directory:/var/www/html -v /my/logs/directory:/var/log/httpd --name my-lamp-container fauria/lamp
```

#### Get inside a running container and open a MariaDB console:

```
docker exec -i -t my-lamp-container bash
mysql -u root
```
=======
# devops-demo-m2-starter

