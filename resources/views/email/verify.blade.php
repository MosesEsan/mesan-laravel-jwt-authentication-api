<?php
/**
 * Created by PhpStorm.
 * User: mosesesan
 * Date: 8/3/16
 * Time: 3:23 PM
 */
?>
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
    <a href="{{ URL::to('api/verify/email/' . $confirmation_code) }}">Verify your account</a>.

</div>

</body>
</html>