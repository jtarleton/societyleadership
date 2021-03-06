# societyleadership
 > http://societyleadership.jamestarleton.com/

 > Admin Account (username: jtarleton, password: jtarleton)

## Commentary From James:

### System Requirements
 -    PHP 5.3+
 -    MySQL 5.5+
 -    Apache mod_rewrite

### Database
 -    A single MySQL table (user)

### Summary of Application Components:

 -	  Controller objects to process Request objects into responses. In general the first and second URL fragments map to controller class 
      and to the controller class method, respectively. (e.g. "report/members" will call the members method of a ReportsController instance.)

 -	  A Route object based on the URL of the raw HTTP request.  

	  *Important: Please note the required Apache rewrite rule to hide "index.php?" (located in .htaccess file in the web root) for this 
	  application to run properly. Apache mod_rewrite must be enabled.*

 -	  A user class as a model, which basically maps directly to societyleadership.user in MySQL.

 -	  A view consisting of an HTML-only template file.  The template contains token strings (e.g. "{{template_token}}") serving as placeholders for data.
	  The tokens in each view file will be replaced with data during a simple templating routine called on the Response.  This routine calls [str_replace](http://php.net/manual/en/function.str-replace.php) (i.e. a doReplace instance method on a Response object).

 -	  A SocietyDB singleton for database connectivity. It will only return a single, non-duplicated PDO instance.

----------------------------

You are required to write a web application using PHP and MySQL that has the following
features. Do not use any frameworks.

## Core Features:
1. A user sign up page that lets the user pick the following:
	 -	  a. Username - throw an error if the username is already in use
	 -	  b. Password - minimum 6 characters
	 -	  c. First name - required
	 -	  d. Last name - required
	 -	  e. Email address - validate input for common email address formats

	 -	  The path for this page should be: [/member/signup](http://societyleadership.jamestarleton.com/member/signup)

2. A report page that shows a list of all the members who have joined. The following should
be shown for each user:
	 -	  a. First name
	 -	  b. Last name
	 -	  c. Username
	 -	  d. Email address

	 -	  The path for this page should be: [/report/members](http://societyleadership.jamestarleton.com/report/members)

3. The report page should have a search box that allows the user to enter an email address
and check if an account with that email address exists.

## Bonus Features:

1. Create a user login page where an existing user can [log in](http://societyleadership.jamestarleton.com/member/login) using their username and
password. Also allow for the users to [log out](http://societyleadership.jamestarleton.com/member/logout).

2. Make the [report page](http://societyleadership.jamestarleton.com/report/members) visible only to an administrator account. For all others, show an
"Insufficient privileges" page. If you implement this, include the administrator username
and password in plain text in the readme file.

## Submission Instructions:
	
 -	  Provide a [zip file](https://github.com/jtarleton/societyleadership/archive/master.zip) with all necessary files.
 -	  Include a [database dump](https://raw.githubusercontent.com/jtarleton/societyleadership/master/societydb.sql) file that loads the schema and some sample data.
 -	  Provide a readme file explaining any choices you made.

You will be assessed on how well you have done the following:

 -	  Covered the core features requirements
 -	  Separated the UI from the back-end logic
 -	  Used object-oriented programming
 -	  Created a functional UI for the application
