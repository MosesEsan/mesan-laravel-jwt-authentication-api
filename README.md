# laravel-jwt-authentication
Laravel 5.2 Authentication API using JWT with E-mail verification.

The steps below are a compilation of a series of tutorials.

<ul>  
  <li><a href="#tutorial1">Tutorial 1: Install jwt-auth</a></li>
  <li><a href="#tutorial2">Tutorial 2: API Routes Setup</a></li>
  <li><a href="#tutorial3">Tutorial 3: Prepare Database</a></li>
  <li><a href="#tutorial4">Tutorial 4: Authenticating Users</a></li>
  <li><a href="#tutorial5">Tutorial 5: Email Templates</a></li>
</ul>

<a name="tutorial1"></a> 
<h1>Tutorial 1: Install jwt-auth</h1>

Open composer.json and update the require object to include jwt-auth: 
```php
"require": { 
    "php": ">=5.5.9", 
    "laravel/framework": "5.2.*", 
    "tymon/jwt-auth": "0.5.*" 
}, 
```
 
Then, run 
```bash
composer update 
```
 
 
<h2>What are JWT (JSON Web Tokens)?</h2>
 
JWT works by providing client application(consumer) with a token to store, when client wants to get or post something to the server, it needs to send this token. Token can be regenerated from time to time. This token exchanging is better than sending user login credentials to authenticate during requests and storing it on client side. 

You would not need to touch any core code of creating JWT, because tymon/jwt-auth package took care of that, we just need to authenticate users and use this package to return a token for future requests authentication. 
We’ll now need to update the providers array in <b>config/app.php</b> with the jwt-auth provider. Open up <b>config/app.php</b>, find the <b>providers</b> array located on line 111 and add this to it: 
 
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
 
After you run this command you will see a new file in the <b>config</b> folder called <b>jwt.php</b>. This file contains settings for jwt-auth, one of which we need to change right away. We need to generate a secret key which we can do from the command line: 
 
```bash
php artisan jwt:generate 
 
```

You’ll see that after running this command we get a new value next to'secret' where “changeme” was before. 
 
 

<a name="tutorial2"></a> 
<h1>Tutorial 2: API Routes Setup</h1>
```php
Route::group(['prefix' => 'api'], function() { 
     //anything goes here will be under api/ 
 
        Route::post('login', 'Api\AuthController@login'); 
        Route::post('register', 'Api\AuthController@register'); 
        Route::get('verify/{confirmationCode}', 'Api\AuthController@confirm'); 
        Route::post('resend', 'Api\AuthController@resendVerification'); 
        Route::post('recover', 'Api\AuthController@recoverPassword'); 
 
 
        Route::group(['middleware' => ['jwt.auth']], function() { 
                  Route::post('logout', 'Api\AuthController@logout'); 
 
                  Route::get('test', function(){ 
              return response()->json(['foo'=>'bar']); 
          }); 
      }); 
}); 
```

<a name="tutorial3"></a> 
<h1>Tutorial 3: Prepare Database</h1>

<h2>The users Table </h2>

Since we are going to allow users to create their accounts within the application, we will need a table to store all of our users. Thankfully, Laravel already ships with a migration to create a basic users table, so we do not need to manually generate one. The default migration for the users table is located in the database/migrations directory. 

We need to create just two fields in addition to the fields that are standard in most users tables (username, email, password, etc.). Firstly, we need a boolean field 'confirmed' to keep track of whether a user has confirmed their email address, this will be set to false by default.  

The second field that we require is a confirmation_code string field. When a user is signed up we set this field to a random string, an email is then sent to the user asking them to confirm their account by following a link to /register/verify/{confirmation_code}. When a user follows this link, we take the passed in confirmation code and search for it within the users table. If a matching confirmation code is found we set the confirmed field for this user to true and set the confirmation code to null.

```php
<?php  
  
use Illuminate\Database\Schema\Blueprint;  
use Illuminate\Database\Migrations\Migration;  
  
class CreateUsersTable extends Migration  
{  
    /**  
     * Run the migrations.  
     *  
     * @return void  
     */  
    public function up()  
    {  
        Schema::create('users', function (Blueprint $table) {  
            $table->increments('id');  
            $table->string('name');  
            $table->string('email')->unique();  
            $table->string('password');  
            $table->boolean('confirmed')->default(0);  
            $table->string('confirmation_code')->nullable();  
            $table->rememberToken();  
            $table->timestamps();  
        });  
    }  
  
    /**  
     * Reverse the migrations.  
     *  
     * @return void  
     */  
    public function down()  
    {  
        Schema::drop('users');  
    }  
} 
```

And start migrations  
```bash
php artisan migrate  
```
 
If you are using MAMP be sure to add the unix_socket key with a value of the path that the mysql.sock resides in MAMP.  

```php
'mysql' => [  
    'driver' => 'mysql',  
    'host' => env('DB_HOST', 'localhost'),  
    'port' => env('DB_PORT', '3306'),  
    'database' => env('DB_DATABASE', 'jwt-api-db'),  
    'username' => env('DB_USERNAME', 'jwt-api'),  
    'password' => env('DB_PASSWORD', 'testpwd'),  
    'charset' => 'utf8',  
    'collation' => 'utf8_unicode_ci',  
    'prefix' => '',  
    'strict' => false,  
    'engine' => null,  
    'unix_socket'   => '/Applications/MAMP/tmp/mysql/mysql.sock',  
],  
 ```
 
Update app/User.php  

```php 
protected $fillable = [  
    'name', 'email', 'password', 'confirmation_code'  
];  
```
  
Update .env file 
 
```php
MAIL_DRIVER=smtp 
MAIL_HOST=smtp.gmail.com 
MAIL_PORT=587 
MAIL_USERNAME=[the_email-address_emails_are_sent_from] 
MAIL_PASSWORD=[the_email_account_password] 
MAIL_ENCRYPTION=tls 
 
DB_CONNECTION=mysql 
DB_HOST=127.0.0.1 
DB_PORT=3306 
DB_DATABASE=[db_name] 
DB_USERNAME=[db_user] 
DB_PASSWORD=[db_pwd] 
```


<a name="tutorial4"></a> 
<h1>Tutorial 4: Authenticating Users</h1>

This is an important part of the API, you will be authenticating users so that they can access the information they have access to. To use authentication, you need to register it in your <b>http/Kernel.php</b>
 
By default, Laravel has CSRF token verification turned on, but since we’re using JWTs in a stateless manner now, we don’t really need CSRF tokens. We can turn this default behavior off by commenting out the <b>VerifyCsrfToken</b> middleware in Kernel.php. 
 
 ```php
protected $routeMiddleware = [ 
   'auth' => \App\Http\Middleware\Authenticate::class, 
   'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class, 
   'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class, 
   'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class, 
   'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class, 
  'jwt.auth' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class, 
  'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class, 
];   
 ```
 
<h2>Log User In and Out</h2>
 
As you can see from the above code, theres a login route which is not grouped under jwt.auth middleware 
Now create a controller for log in: 

```bash
php artisan make:controller Api/AuthController 
```
 
This will create <b>Http/Controllers/Api/AuthController.php</b>, in it, paste this in

```php
<?php 
 
namespace App\Http\Controllers\Api; 
 
use Illuminate\Http\Request; 
 
use App\Http\Requests; 
use App\Http\Controllers\Controller; 
 
 
use JWTAuth; 
use Tymon\JWTAuth\Exceptions\JWTException; 
use Validator; 
use App\User; 
use Hash, Mail; 
use Illuminate\Support\Facades\Password; 
use Illuminate\Mail\Message; 
 
 
class AuthController extends Controller 
{ 
    /** 
     * API Register User 
     * 
     * @param Request $request 
     */ 
    public function register(Request $request) { 
        $rules = [ 
            'name' => 'required|max:255', 
            'email' => 'required|email|max:255|unique:users', 
            'password' => 'required|confirmed|min:6', 
        ]; 
 
        $input = $request->only( 
            'name', 
            'email', 
            'password', 
            'password_confirmation' 
        ); 
 
        $validator = Validator::make($input, $rules); 
 
//        print_r($validator) ; 
 
        if($validator->fails()) { 
            $error = $validator->messages()->toJson(); 
            return response()->json(['success'=> false, 'error'=> $error]); 
        } 
 
        $confirmation_code = str_random(30); //Generate confirmation code 
 
        $name = $input['name']; 
        $email = $input['email']; 
        User::create([ 
            'name' => $input['name'], 
            'email' => $input['email'], 
            'password' => Hash::make( $input['password']), 
            'confirmation_code' => $confirmation_code 
        ]); 
 
        Mail::send('email.verify', ['confirmation_code' => $confirmation_code], 
            function($m) use ($email, $name){ 
            $m->from('[from_email_address]', 'Test API'); 
            $m->to($email, $name) 
                ->subject('Verify your email address'); 
        }); 
 
        return response()->json(['success'=> true, 'message'=> 'Thanks for signing up! Please check your email.']); 
    } 
 
    /** 
     * API Confirm User 
     * 
     * @param Request $request 
     */ 
    public function confirm($confirmation_code) 
    { 
        if(!$confirmation_code) return "Invalid link"; 
 
        $user = User::where('confirmation_code', $confirmation_code)->first(); 
 
        if (!$user) return response()->json(['success'=> false, 'error'=> "User Not Found"]); 
 
        $user->confirmed = 1; 
        $user->confirmation_code = null; 
        $user->save(); 
 
        $email =$user->email; 
        $name =$user->name; 
 
        Mail::send('email.welcome', ['email' => $email, 'name' => $name], 
            function ($m) use ($email, $name) { 
            $m->from('[from_email_address]', 'Test API');
                $m->to($email, $name)->subject('Welcome To Test API'); 
            }); 
 
        return response()->json(['success'=> true, 'message'=> 'You have successfully verified your account.']); 
    } 
 
 
    /** 
     * API Resend Verification 
     * 
     * @param Request $request 
     * @return \Illuminate\Http\JsonResponse 
     */ 
    public function resendVerification(Request $request) 
    { 
        $user = User::where('email', $request->email)->first(); 
 
        if (!$user) return response()->json(['success'=> false, 'error'=> "Your email address was not found."]); 
 
        $confirmation_code = str_random(30); //Generate confirmation code 
        $user->confirmation_code = $confirmation_code; 
        $user->save(); 
 
        $email =$user->email; 
        $name =$user->name; 
 
        Mail::send('email.verify', ['confirmation_code' => $confirmation_code], 
            function($m) use ($email, $name){ 
            $m->from('[from_email_address]', 'Test API');
                $m->to($email, $name) 
                    ->subject('Verify your email address'); 
            }); 
 
        return response()->json(['success'=> true, 'message'=> 'A new verification email has been sent! Please check your email.']); 
    } 
 
    /** 
     * API Resend Verification 
     * 
     * @param Request $request 
     * @return \Illuminate\Http\JsonResponse 
     */ 
    public function recoverPassword(Request $request) 
    { 
        $user = User::where('email', $request->email)->first(); 
 
        if (!$user) return response()->json(['success'=> false, 'error'=> "Your email address was not found."]); 
 
        Password::sendResetLink($request->only('email'), function (Message $message) { 
            $message->subject('Your Password Reset Link'); 
 
        }); 
 
        return response()->json(['success'=> true, 'message'=> 'A reset email has been sent! Please check your email.']); 
    } 
 
    /** 
     * API Login, on success return JWT Auth token 
     * 
     * @param Request $request 
     * @return \Illuminate\Http\JsonResponse 
     */ 
    public function login(Request $request) 
    { 
//        $credentials = $request->only('email', 'password'); 
 
        $credentials = [ 
            'email' => $request->email, 
            'password' => $request->password, 
            'confirmed' => 1 
        ]; 
 
        try { 
            // attempt to verify the credentials and create a token for the user 
            if (! $token = JWTAuth::attempt($credentials)) { 
                return response()->json(['success' => false, 'error' => 'Invalid Credentials. Please make sure you entered the right information and you have verified your account. '], 401); 
            } 
        } catch (JWTException $e) { 
            // something went wrong whilst attempting to encode the token 
            return response()->json(['success' => false, 'error' => 'could_not_create_token'], 500); 
        } 
 
        // all good so return the token 
        return response()->json(compact('token')); 
    } 
 
    /** 
     * Log out 
     * Invalidate the token, so user cannot use it anymore 
     * They have to relogin to get a new token 
     * 
     * @param Request $request 
     */ 
    public function logout(Request $request) { 
        $this->validate($request, [ 
            'token' => 'required' 
        ]); 
 
        JWTAuth::invalidate($request->input('token')); 
    } 
 
} 
 ```
 
 
<a name="tutorial5"></a> 
<h1>Tutorial 5: E-Mail Templates</h1>

Create a file blade on resources /views/email/<b>verify.blade.php</b> as content for email delivery and input the code like this. 

```html
<!DOCTYPE html> 
<html lang="en-US"> 
<head> 
    <meta charset="utf-8"> 
</head> 
<body> 
<h2>Verify Your Email Address</h2> 
 
<div> 
    Thanks for creating an account with Test API. 
    Please follow the link below to verify your email address 
    <a href="{{ URL::to('api/verify/' . $confirmation_code) }}">Verify your account</a>. 
 
</div> 
 
</body> 
</html>

```

/views/email/welcome.blade.php 
```html
<!DOCTYPE html> 
<html lang="en-US"> 
<head> 
    <meta charset="utf-8"> 
</head> 
<body> 
<h2>Welcome To Test API</h2> 
 
<div> 
    Thanks for joining Test API. 
 
    <br/> 
 
</div> 
 
</body> 
</html>
```
