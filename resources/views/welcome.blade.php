<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Moses Esan </title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 34px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    JWT Authentication API with E-mail and Phone Verification using Twilio Authy
                </div>


                <div class="links" style="margin-bottom: 20px; text-align: left;">
                    <ul>
                        <li>Register <strong>(/api/register)</strong></li>
                        <li>Send Verification Code <strong>(/api/phone/send-verification)</strong></li>
                        <li>Login <strong>(/api/login)</strong></li>
                        <li>Recover Password <strong>(/api/recover)</strong></li>
                        <li>Verify Code <strong>(/api/phone/verify-code)</strong></li>
                    </ul>
                </div>


                <div class="links">
                    <a href="https://github.com/MosesEsan/mesan-laravel-jwt-authentication-api/tree/laravel5.4">GitHub (Email Verification)</a>
                    <a href="https://github.com/MosesEsan/mesan-laravel-jwt-authentication-api/tree/sms-verification">GitHub (Phone Verification)</a>
                    <a href="https://medium.com/@mosesesan/tutorial-5-how-to-build-a-laravel-5-4-jwt-authentication-api-with-e-mail-verification-61d3f356f823">Tutorial (Email Verification)</a>
                    <a href="http://mosesesan.com">My Website</a>
                </div>
            </div>
        </div>
    </body>
</html>
