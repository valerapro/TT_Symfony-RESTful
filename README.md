RESTful service on Symfony 3.2.2
=========

Deployment:
 
 - Run composer.
 
 $ composer install 
 
 - Config permition for cache.
 
 http://symfony.com/doc/current/setup/file_permissions.html 
 
 - Clear cache
 
 $ php bin/console cache:clear 
 
 - Run migrations.
 
 $ php bin/console doc:mig:mig
 