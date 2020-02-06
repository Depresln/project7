# project7 API
This is my 7th project for OpenClassrooms.

GitHub link:
https://github.com/Depresln/project7


----- Local Installation -----

1. Get the project from the ProjectAPI folder and open your symfony local server with "symfony server:start"

2. Open Postman at your localhost url and add /api/login_check
    
3. With the POST method enter your logs in the body in json format. Here are some working examples:
{
	"username": "admin",
	"password": "pass"
}

{
	"username": "user1",
	"password": "pass"
}

{
	"username": "user2",
	"password": "pass"
}


4. Get the JWT token that is given to you and paste it in Authorization > Bearer Token then you can navigate in the API as you please.

5. For more informations about the API, the documentation is available at /api/doc on your browser and does not require any log in to access.


----- Online Installation -----

1. Project is already available online at www.project7.nicolasdep.com/public/index.php/api/doc (link to the doc)

2. Open Postman and navigate in the API using www.project7.nicolasdep.com/public/index.php/api/login_check to log in

3. Use the Local Installation guide starting from step 3 to navigate in the API