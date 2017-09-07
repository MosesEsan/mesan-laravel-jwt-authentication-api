# Laravel 5.4 JWT Authentication with Phone Verification using Twilio Authy
A PHP Mobile Authentication API with Phone Verification using Twilio Authy, developed with Laravel 5.4 framework and JWT (JSON Web Tokens) Package.

**This Branch** <br/>
Phone Verification using Twilio Authy

**Other Branch** <br/>
<a href="https://github.com/MosesEsan/mesan-laravel-jwt-authentication-api" target="_blank">Email Verification</a>

## Testing
Use Chrome plugin <a href="https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=en" target="_blank">Postman</a> to test.<br/>

**Try accessing test route without token [GET]**<br/>

http://mosesesan.com/demos/jwt-phone-auth/api/test<br/>

You should receive the following error message.

```json
 {
     "error": "token_not_provided"
 }
```

**Register** <br/>
Create a POST request to api/register with form-data under Body tab.<br/>

http://mosesesan.com/demos/jwt-phone-auth/api/register

```json
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6Ly9tb3Nlc2VzYW4uY29tL2RlbW9zL2p3dC1waG9uZS1hdXRoL2FwaS9yZWdpc3RlciIsImlhdCI6MTUwNDc0NTUzMiwiZXhwIjoxNTA0NzQ5MTMyLCJuYmYiOjE1MDQ3NDU1MzIsImp0aSI6IlN3SEFWamNWYmZyNTRJeGMifQ.s3vCeWME10GqcLpqyz7KpMnQWw1iJAdeyvO0g6aTqO0",
        "verified": false,
        "message": "Thanks for signing up! Verify your account to complete your registration."
    }
}
```

**Send Verification Code** <br/>
Create a POST request to api/phone/send-verification with form-data under Body tab.<br/>

http://mosesesan.com/demos/jwt-phone-auth/api/phone/send-verification

```json
{
    "success": true,
    "data": {
        "message": "We have sent you an SMS with a code to 3476990472. To complete your registration, please enter the activation code."
    }
}
```

**Verify Code** <br/>
Create a POST request to api/phone/verify-code with form-data under Body tab.<br/>

http://mosesesan.com/demos/jwt-phone-auth/api/phone/verify-code?token=[token_goes_here]

```json
{
    "success": true,
    "data": {
        "message": "You have successfully verified your account."
    }
}
```

**Login** <br/>
Create a POST request to api/login with form-data under Body tab.

http://mosesesan.com/demos/jwt-phone-auth/api/login


```json
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6Ly9tb3Nlc2VzYW4uY29tL2RlbW9zL2p3dC1waG9uZS1hdXRoL2FwaS9sb2dpbiIsImlhdCI6MTUwNDc0NTc2NSwiZXhwIjoxNTA0NzQ5MzY1LCJuYmYiOjE1MDQ3NDU3NjUsImp0aSI6InowaHl3UXpCRmtaS1FnQ2wifQ.mt__sJbISaCOKc9-ZN31rt1FDWpnT6D8ChwYM5vx0j4",
        "verified": true
    }
}
```

**Try accessing test route with the token [GET]**

http://mosesesan.com/demos/jwt-phone-auth/api/test?token=[token_goes_here]

You should receive

```json
{
    "foo": "bar"
}
```

**Logout** <br/>
Create a GET request to api/logout.

http://mosesesan.com/demos/jwt-phone-auth/api/logout?token=[token_goes_here]

**Recover Password** <br/>
Create a POST request to api/recover with form-data under Body tab.

http://mosesesan.com/demos/jwt-phone-auth/api/recover

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

**Unique Phone Number** <br/>

Attempt to verify with the phone number you used in the previous test.
