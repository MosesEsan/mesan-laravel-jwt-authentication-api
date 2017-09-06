# Laravel 5.4 JWT Authentication with Phone Verification using Twilio Authy
A PHP Mobile Authentication API with Phone Verification using Twilio Authy, developed with Laravel 5.4 framework and JWT (JSON Web Tokens) Package.

**This Branch** <br/>
E-mail Verification and Phone Verification using Twilio Authy

**Other Branches** <br/>
<a href="https://github.com/MosesEsan/mesan-laravel-jwt-authentication-api/tree/laravel5.4" target="_blank">Email Verification Only </a><br>
<a href="https://github.com/MosesEsan/mesan-laravel-jwt-authentication-api/tree/sms-verification" target="_blank">Phone Verification only</a>

### Tutorial

The steps below are a compilation of a series of tutorials.

<a name="step1"></a>
### Step 1: Create new project and install jwt-auth

Create Laravel project
```bash
laravel new JWTAuthentication
```
Open composer.json and update the require object to include jwt-auth 

```php
"require": {
    "php": ">=5.6.4",
    "laravel/framework": "5.4.*",
    "laravel/tinker": "~1.0",
    "tymon/jwt-auth": "0.5.*"
}
```
 Then, run 
```bash
composer update 
```

<a name="step2"></a>
### Step 2: Add JWT Provider and Facades
 
We’ll now need to update the providers array in config/app.php with the jwt-auth provider. Open up config/app.php, find the providers array located on line 138 and add this to it:

```php
Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class, 

```
Add in the jwt-auth facades which we can do in config/app.php. Find the aliases array and add these facades to it:

```php
'JWTAuth'   => Tymon\JWTAuth\Facades\JWTAuth::class, 
'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class 
 
```
We also need to publish the assets for this package. From the command line: 
 
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider" 
 
```
 
After you run this command you will see a new file in the config folder called jwt.php. This file contains settings for jwt-auth, one of which we need to change right away. We need to generate a secret key which we can do from the command line: 
```bash
php artisan jwt:generate 
 
```

You’ll see that after running this command we get a new value next to’secret’ where “changeme” was before.

Register the jwt.auth and jwt.refresh middleware in app/http/Kernel.php
 
 ```php
 protected $routeMiddleware = [
 ...
     'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
     'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
 ];
 ```

The full tutorial for e-mail verification is available on my  <a href="https://medium.com/@mosesesan/tutorial-5-how-to-build-a-laravel-5-4-jwt-authentication-api-with-e-mail-verification-61d3f356f823" target="_blank">blog</a>.