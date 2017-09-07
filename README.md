# Laravel 5.4 JWT Authentication API with E-Mail Verification
A PHP Mobile Authentication API with E-mail verification, developed with Laravel 5.4 framework and JWT (JSON Web Tokens) Package.

**This Branch** <br/>
Email Verification

**Other Branch** <br/>
<a href="https://github.com/MosesEsan/mesan-laravel-jwt-authentication-api/tree/phone-verification" target="_blank">Phone Verification using Twilio Authy</a>

## Testing
Use Chrome plugin <a href="https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=en" target="_blank">Postman</a> to test.<br/>

**Try accessing test route without token [GET]**<br/>

http://mosesesan.com/demos/jwt-email-auth/api/test<br/>

You should receive the following error message.

```json
 {
     "error": "token_not_provided"
 }
```

**Register and Verify** <br/>
Create a POST request to api/register with form-data under Body tab. **Make sure to enter a valid email address so you can receive the verification email.**<br/>

http://mosesesan.com/demos/jwt-email-auth/api/register

```json
{
  "success":true,
  "message":"Thanks for signing up! Please check your email to complete your registration."
}
```

Verify the email address by clicking the link in the verification email.

**Login** <br/>
Create a POST request to api/login with form-data under Body tab.

http://mosesesan.com/demos/jwt-email-auth/api/login

If you attempt to login without verifying your email address, you will receive the error below:

```json
{
    "success": false,
    "error": "Invalid Credentials. Please make sure you entered the right information and you have verified your email address."
}
```

If you have verified your email address, you should receive a token back

```json
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODg4OC9tZXNhbi1sYXJhdmVsLWp3dC1hdXRoZW50aWNhdGlvbjIvcHVibGljL2FwaS9sb2dpbiIsImlhdCI6MTUwMjU2NzE5MSwiZXhwIjoxNTAyNTcwNzkxLCJuYmYiOjE1MDI1NjcxOTEsImp0aSI6IkVIVWV6dVp0UDhhSmQ2QUUifQ.OjlzNKmTItphLs29B7WsFstmrtgDW2qE7gv26LcR3Og"
    }
}
```

**Try accessing test route with the token [GET]**

http://mosesesan.com/demos/jwt-email-auth/api/test?token=[token_goes_here]

You should receive

```json
{
    "foo": "bar"
}
```

**Logout** <br/>
Create a GET request to api/logout.

http://mosesesan.com/demos/jwt-email-auth/api/logout?token=[token_goes_here]

**Recover Password** <br/>
Create a POST request to api/recover with form-data under Body tab.

http://mosesesan.com/demos/jwt-email-auth/api/recover

```json
{
    "success": true,
    "data": {
        "msg": "A reset email has been sent! Please check your email."
    }
}
```

**Unique Email** <br/>

Attempt to register with the email address you used in the previous test.

## Tutorial

The steps below are a compilation of a series of tutorials.

<a name="step1"></a>
**Step 1: Create new project and install jwt-auth**

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
**Step 2: Add JWT Provider and Facades**
 
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
**Step 3: Set Up Routes**

Open up routes/api.php.

```php
Route::post('login', 'AuthController@login'); 
Route::post('register', 'AuthController@register'); 
Route::post('recover', 'AuthController@recover');
 
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
**Step 4: Set Up Database**

Since we are going to allow users to create their accounts within the application, we will need a table to store all of our users. Thankfully, Laravel already ships with a migration to create a basic users table, so we do not need to manually generate one. The default migration for the users table is located in the database/migrations directory.

We need to create a new table and add an extra column to the users table. Firstly, we need a boolean field ‘is_verified’to keep track of whether a user has verified their email address, this will be set to false by default.

Create new table “user_verifications” that will store token of user verification code. When a user is signed up, a verification code is generated and stored in the table, an email is then sent to the user asking them to verify their account by following a link to /user/verify/{verification_code}.

When a user follows this link, we take the passed in verification code and search for it within the user_verifications table. If a matching verified code is found we set the is_verified field for this user to true.

The full tutorial is available on my  <a href="https://medium.com/@mosesesan/tutorial-5-how-to-build-a-laravel-5-4-jwt-authentication-api-with-e-mail-verification-61d3f356f823" target="_blank">blog</a>.