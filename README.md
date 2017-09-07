

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

