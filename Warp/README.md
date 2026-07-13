# Notas de la version

**Stack**

- Magento community 2.4.7
- PHP 8.3-fpm
- MariaDb 10.6
- Opensearch 2.12
- Redis 7.0

## Modulos basicos de 3ros

**Require**

- cweagans/composer-patches
- lillik/magento2-price-decimal
- smile/elasticsuite
- redchamps/module-clean-admin-menu
- markshust/magento2-module-disabletwofactorauth
- mageplaza/module-backend-reindex
- mageplaza/module-smtp
- magtools/m2-cronrun
- magtools/m2-spamblocker
- magtools/m2-trustedemail

**Require Dev**

- smile/module-debug-toolbar
- squizlabs/php_codesniffer

## Modulos basicos 66ecommerce

**app/code**

- Brands
- DbClean
- ElasticsuiteCore
- PromoMassAction
- Theme
- Widgets

## Devops resources

- codeanalizer.sh
- deploywarp.sh
- bitbucket-pipelines.yml
- app/devops/TestPR.xml
- app/devops/ initial_db, install, dump, etc

## Modificaciones relevantes

### composer.json

- Se añade repositorio localextensions -> extensions/
- scripts post install para vendor/bin/phpcs

### grunt-config.json

    {
      "themes": "app/design/themes"
    }

- Se añade archivo app/design/themes.js con configuracion de themes

### package.json

Se hace downgrade de dos paquetes por conflictos con calculos de modulos magento.

- "grunt-contrib-less": "~2.1.0",
- "less": "3.13.1",

### docker-compose-warp.yml

- nginx: se añade mapeo para m2-cors
- nginx: se añade mapeo para globalblacklist
- nginx: se añade mapeo para bad bots
- elasticsearch: se cambia recurso a opensearchproject/opensearch 
- elasticsearch: se añade seguridad OPENSEARCH_INITIAL_ADMIN_PASSWORD
- elasticsearch: se añade configs .warp/docker/config/opensearch
- elasticsearch: se añade comando para instalar plugin analysis-phonetic

### Docker config

- crontab: */5 (cada 5 minutos)
- mysql: configs especificas performance mariadb en conf.d/docker.cnf
- nginx: htpassw preconfigurado para basic auth
- nginx: bad-bot-blocker (configuracion completa + entradas en nginx.conf)
- nginx: m2-cors.conf (configuraciones para cors)
- nginx: vhost files para entornos local, test y produccion
- nginx: vhost files con proteccion DDOS, repeticion account + catalogsearch
- nginx: vhost test con basic auth + bearer token en X-Authorization-Bearer
- nginx: nginx.conf mejoras performance, ddos, bad bots blocker
- opensearch: configuraciones especiales para utilizar opensearch
- php-fpm: mejoras de performance pm (Process Manager)

## Conexion a la Api

La conexion a la api funciona normalmente en local y produccion, pero en los 
entornos de test se requiere enviar un header adicional X-Authorization-Bearer:

    Authorization Basic NjZlY29tbTo2NmVjb21t = base_64(66ecomm:66ecomm)
    
GET TOKEN
    
    curl --location 'https://testing.66ecommerce.com/rest/V1/integration/admin/token' \
    --header 'Authorization: Basic NjZlY29tbTo2NmVjb21t' \
    --header 'Cookie: PHPSESSID=7de89146646ff8601d90955948ad40cd' \
    --form 'username="magento_user"' \
    --form 'password="magento_pass"'
    
GET ORDER
    
    curl --location 'https://testing.66ecommerce.com/rest/V1/orders/495921' \
    --header 'Authorization: Basic NjZlY29tbTo2NmVjb21t' \
    --header 'X-Authorization-Bearer: eyJraWQiOiIxIiwiYWxnIjoiSFMyNTYifQ.eyJ1aWQiOjgwLCJ1dHlwaWQiOjIsImlhdCI6MTcyNjY5MDcxNSwiZXhwIjoxNzI2Njk0MzE1fQ.cOvRBQiqqJVBT9eNX1rP6vawXioD-3zE4-EonSSKS6o' \
    --header 'Content-Type: application/json' \
    --header 'Cookie: PHPSESSID=7de89146646ff8601d90955948ad40cd' \
    --data ''