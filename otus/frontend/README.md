# otus-app

This is a test app using AngularJS, NodeJS and Docker


## INSTALLATION

** There seems to be a bug with osx mounted volume where `npm install` does not run or finish **
- With Docker
````
clone repo
````
````
docker-compose up
````
````
docker exec {container} sh -c "cd /var/project/frontend && npm install"
````
````
docker exec {container} sh -c "cd /var/project/backend && npm install"
````
````
docker exec {container} sh -c "pm2 start process.yml"
````

- Without Docker

Install
- Nodejs
- NPM
- Bower
- pm2 (globally)
- Angular

````
clone repo
````
````
cd backend
````
````
npm install
````
````
cd ../frontend
````
````
npm install
````
````
cd ../
````
````
pm2 start process.yml
````
APP - http://localhost:9000

API - http://localhost:9001

- JSON source located in data/students_classes.json

- MYSQL source located in data/students_classes.sql
