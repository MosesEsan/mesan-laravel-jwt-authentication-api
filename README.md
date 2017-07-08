# Laravel 5.4 JWT Authentication with E-Mail Verification
A PHP Mobile Authentication API which includes E-mail verification, developed with Laravel 5.4 framework and JWT (JSON Web Tokens) Package.

### Tutorial

The steps below are a compilation of a series of tutorials.

<ul>
  <li><a href="#step1">Step 1: Create new project and install jwt-auth</a></li>
  <li><a href="#step2">Step 2: Add JWT Provider and Facades</a></li>
  <li><a href="#step3">Step 3: Set Up Routes</a></li>
  <li><a href="#step4">Step 4:  Set Up Database</a></li>
  <li><a href="#step5">Step 5: Register and Verify Email Address</a></li>
  <li><a href="#step6">Step 6: Log User In and Out</a></li>
  <li><a href="#step7">Step 7: Testing</a></li>
</ul>

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

<a name="step3"></a>
### Step 3: Set Up Routes

Open up routes/api.php.

```php
Route::post('login', 'AuthController@login'); 
Route::post('register', 'AuthController@register'); 
 
Route::group(['middleware' => ['jwt.auth']], function() { 
    Route::post('logout', 'AuthController@logout'); 
  
    Route::get('test', function(){ 
        return response()->json(['foo'=>'bar']); 
    }); 
});
```
Open up routes/web.php and add the route for verifying.

```php
....
Route::get('user/verify/{verification_code}', 'AuthController@verifyUser');
```

<a name="step4"></a>
### Step 4: Set Up Database

Since we are going to allow users to create their accounts within the application, we will need a table to store all of our users. Thankfully, Laravel already ships with a migration to create a basic users table, so we do not need to manually generate one. The default migration for the users table is located in the database/migrations directory.

We need to create a new table and add an extra column to the users table. Firstly, we need a boolean field ‘is_verified’to keep track of whether a user has verified their email address, this will be set to false by default.

Create new table “user_verifications” that will store token of user verification code. When a user is signed up, a verification code is generated and stored in the table, an email is then sent to the user asking them to verify their account by following a link to /user/verify/{verification_code}.

When a user follows this link, we take the passed in verification code and search for it within the user_verifications table. If a matching verified code is found we set the is_verified field for this user to true.

Available on my <a href="http://mosesesan.com/blog/2017/06/19/laravel-jwt-authentication-with-e-mail-verification/#step4" target="_blank">blog</a>.



<a name="step5"></a>
### Step 5: Register and Verify Email Address

Available on my <a href="http://mosesesan.com/blog/2017/06/19/laravel-jwt-authentication-with-e-mail-verification/#step5" target="_blank">blog</a>.

<a name="step6"></a>
### Step 6: Log User In and Out

Available on my <a href="http://mosesesan.com/blog/2017/06/19/laravel-jwt-authentication-with-e-mail-verification/#step6" target="_blank">blog</a>.

<a name="step7"></a>
### Step 7: Testing

Available on my  <a href="http://mosesesan.com/blog/2017/06/19/laravel-jwt-authentication-with-e-mail-verification/#step7" target="_blank">blog</a>.
