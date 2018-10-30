# home24

I created this task using Symfony3.4

### Installation 
- clone project and got project directory in terminal
- run `composer install` 
- you will be prompeted to add some params needed for the project
- make sure that `var/cache` , `var/logs` , and `var/sessions` has r/w permission
- create database or run `bin/console doctrine:database:create --env test`
- create database or run `bin/console doctrine:database:create --env prod`
- run `bin/console doctrine:migrations:migrate -n`
- run `bin/console doctrine:migrations:migrate -n --env test`
- run `bin/console doctrine:fixtures:load`
- run `composer test`
- finally run `bin/console server:run`


### Account APIs:
- `POST /api/login` login with email & password
- `GET /api/profile` get information to your profile using bearer authontication (JWT)
### CRUD APIs:
- `GET /api/posts` 
- `GET /api/posts/{id}` 
- `POST /api/posts/add` 
- `POST /api/posts/{id}/edit` 
- `DELETE /api/posts/{id}/delete`

### APIs details
you can use this url `/api/doc` [on top-right add the apikey (default is `123`)]  
or  
you can use postman `var/postman` two files attached environment and APIs structure  

### Questions
1. Please explain your choice of technologies.  
I used Symfony 3.4 because it has a lot of functionality that make coding easier. Also, because this my main experience in  

2. What is the difference between PUT and POST methods?  
I found simple difinition for both
"POST /posts/add" create a new post, and respond with the new URL identifying that book: "/posts/5".  
"PUT /posts/5" would have to either create a new post with the id of 5, or replace the existing post with ID 5.  

3. What approaches would you apply to make your API responding fast? 4. How would you monitor your API?  
- I used doctrine caching on retriving data
- return JSON data
- on apache I enabled gzip module to compress response
- for get all posts I use pagination and filteration

4. How would you monitor your API?  
I use postman monitoring 

5. Which endpoints from the task could by publically cached?  
I used caching on the get all posts  
once, data requested it will be same for 300 seconds then start retreive from database again 
I use file_system caching to not use PHP extention (like memecache and apc) just for simplicity   

### Notes
- PHPUnitTest for login & profile (simple)
- some time postman token expire you need to get new one and update your environment variable
