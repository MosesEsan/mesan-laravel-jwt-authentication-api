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
<h2>Reset Your Password</h2>

<div>
Please follow the link below to
<a href="{{ URL::to('api/reset/' . $confirmation_code) }}">reset your password</a>.

</div>

</body>
</html>