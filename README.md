# CrudRestPHP
skeleton of crud php (web service rest)


# Description
This is an example of PHP Crud using a web service rest
The authentication is present but non implemented... you need to add the check for username and password
For the token we are used the web json token (see https://scotch.io/tutorials/the-anatomy-of-a-json-web-token)
(now is implemented and tested ONLY GET and POST requests)


# Requirements
- PHP (tested php version 5.3.3)
- PHP CURL support enabled 
- Apache rewrite module enabled
- check if AllowOverride All is configured


#Files in the folder API
This folder contain all implemented files
- inc/config.inc.php ==> general configuration file
- utils/ ==> directory that contain some utility file
- .htaccess ==> apache file for manage the url rewrite
- JWTGenerator.php ==> for manage the authentication and the token generation
- WebServices.php ==> base of the web service implementation
- index.php ==> contain the $endpoints array (list of enabled endpoints) and on which to redirect all calls using the .htaccess rules
- CustomWS.php ==> manage the endpoints request


#Files in the folder testEndPoints
Some script php for test the End-Points


#Installation
The installation is really easy... but please follow these steps:

1 - Check if Apache rewrite module is enabled

2 - Check in the apache configuration if the document root (or documento root of the virtual host) have configured "AllowOverride All" (usually is configured "AllowOverride noneThe installation is really easy... please follow these steps:
1 - Check if Apache rewrite module is enabled

2 - Check in the apache configuration if the directive "AllowOverride All" is configured (for the "document root " or the "documento root" of the virtual host)... usually is configured "AllowOverride None"

3 - Check if the PHP CURL Support is enabled

4 - Copy the files in the document root or in a separate virtual host's document root (recommended)

5 - Configure the endpoints enabeled (see index.php file)


#Configuration and authentication

File inc/config.inc.php:

- $secret_key ==> change the key used for generate the token

- $expire_after ==> time of token validity ... use this notation http://php.net/manual/en/function.strtotime.php


File index.php:

- declare the list of endpoints using the array "$endpoints"


File CustomWS.php:

- in the login method you need to add the check user/password (or other mode) for the authentication. Example: check in your users table


File .htaccess:

- usually you don't need to change this file... this contain the rules for rewrite rules


#Next Steps
- Implementation of the DELETE method

