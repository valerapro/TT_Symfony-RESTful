RESTful service on Symfony 3.2
=========

<h4>Technical specification.</h4>
 
Need to implement REST API service on Symfony3

The functionality of the service:
- Uploading photos with the ability to attach tags;
- Adding tags to photos;
- Removal of tags;
- Delete photo;
- Display the photos list with pagnac;
- Search photos by tags.

It would be a plus coverage, code documentation (api methods). From databases you can use mongoDB, MySQL, PostgreSQL.


<h4>Testing API methods</h4>
 
 $ phpunit tests/ApiBundle/Controller/TagControllerTest.php
 
 $ phpunit tests/ApiBundle/Controller/PhotoControllerTest.php
 