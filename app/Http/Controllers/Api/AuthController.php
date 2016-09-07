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
            'phone_number' => 'required|phone_number|min:9|unique:users',
            'password' => 'required|confirmed|min:6',
            'v_type' => 'required',
        ];

        $input = $request->only(
            'name',
            'email',
            'phone_number',
            'password',
            'password_confirmation',
            'v_type'
        );

        $validator = Validator::make($input, $rules);

//        print_r($validator) ;

        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }

        $name = $input['name'];
        $email = $input['email'];
        $phone_number = $input['phone_number'];
        User::create([
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone_number,
            'password' => Hash::make( $input['password']),
            'type' => "email",
        ]);

        return response()->json(['success'=> true, 'message'=> 'Thanks for signing up!']);
    }

    /**
     * API Register User with FACEBOOK
     *
     * @param Request $request
     */
    public function registerWithFacebook(Request $request) {
        $input = $request->only(
            'name',
            'email',
            'fbID'
        );

        if(empty($input['email'])){
            return response()->json(['success'=> false, 'error'=> "No email address provided"]);
        }else{
            $user =
                User::where('email', '=', $input['email'])
                    ->where('type', "fb")
                    ->where('fbID', $input['fbID'])
                    ->first();

            //Refactor the code below
            if ($user){
                //Log user in
                $email = $user->email;
                $password = $email;

                $credentials = [
                    'email' => $email,
                    'password' => $password
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

            }else{
                //Register user
                $rules = [
                    'name' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users'
                ];

                $validator = Validator::make($input, $rules);
                //print_r($validator) ;

                if($validator->fails()) {
                    $error = $validator->messages()->toJson();
                    return response()->json(['success'=> false, 'error'=> $error]);
                }

                $email = $input['email'];
                $password = $email;
                User::create([
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'password' => Hash::make($password),
                    'confirmed' => 1,
                    'type' => "fb",
                    'fbID' => $input['fbID']
                ]);

                $credentials = [
                    'email' => $email,
                    'password' => $password
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
        }
    }

    /**
     * API Send Verification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerification(Request $request)
    {
        $rules = [
            'email' => 'required',
            'v_type' => 'required',
        ];

        $input = $request->only(
            'email',
            'v_type'
        );

        $validator = Validator::make($input, $rules);

        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user) return response()->json(['success'=> false, 'error'=> "User was not found."]);

        $verification_code = rand(10000, 99999);
        $confirmation_code = str_random(30); //Generate confirmation code
        $type = $request->v_type;

        if ($type === "sms") $user->verification_code = $verification_code;
        else $user->confirmation_code = $confirmation_code;
        $user->save();

        if($type === "sms"){
            //SMS Verification
            $decoded_response = $this->sendSMSVerification($verification_code,$request->phone_number);

            foreach ( $decoded_response['messages'] as $message ) {
                if ($message['status'] == 0) {
                    return response()->json(['success'=> true, 'message'=> "Verification code has been sent to ".$request->phone_number+'.']);
                } else {
                    return response()->json(['success'=> false, 'error'=> "Error {$message['status']} {$message['error-text']}"]);
                }
            }

        }elseif($request->v_type === "email"){
            //Email verification
            $email =$user->email;
            $name =$user->name;
            Mail::send('email.verify', ['confirmation_code' => $confirmation_code],
                function($m) use ($email, $name){
                    $m->from($_ENV['MAIL_USERNAME'], 'Test API');
                    $m->to($email, $name)
                        ->subject('Verify your email address');
                });
            return response()->json(['success'=> true, 'message'=> 'Please check your email.']);
        }
    }

    /**
     * API Verify User
     *
     * @param Request $request
     */
    public function verifyUser($type, $code)
    {
        if(!$code) return "Invalid link/code";

        if ($type === "email") $user = User::where('confirmation_code', $code)->first();
        else $user = User::where('verification_code', $code)->first();

        if (!$user) {
            if ($type === "email"){
                return response()->json(['success'=> false, 'error'=> "User Not Found."]);
            }else{
                return response()->json(['success'=> false, 'error'=> "Incorrect Code. Please make sure you entered the correct code."]);
            }
        }

        $user->confirmed = 1;
        if ($type === "email") $user->confirmation_code = null;
        else $user->verification_code = null;

        $user->save();

        if ($type === "email"){
            $email =$user->email;
            $name =$user->name;
            Mail::send('email.welcome', ['email' => $email, 'name' => $name],
                function ($m) use ($email, $name) {
                    $m->from($_ENV['MAIL_USERNAME'], 'Test API');
                    $m->to($email, $name)->subject('Welcome To Test API');
                });
        }

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
                $m->from('[from_email_add]', 'Test API');
                $m->to($email, $name)
                    ->subject('Verify your email address');
            });

        return response()->json(['success'=> true, 'message'=> 'A new verification email has been sent! Please check your email.']);
    }

    /**
     * API Recover Password
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
     * API Login with Facebook
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWithFacebook($email, $password)
    {
        $credentials = [
            'email' => $email,
            'password' => $password
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


    /**
     * Send SMS Verification Code
     *
     * @param $verification_code, $phone_number
     */

    public function sendSMSVerification($verification_code, $phone_number){
        $otp_prefix = ':';

        //Your message to send, Add URL encoding here.
        $message = "Hello! Welcome to TestApp. Your Verification code is $otp_prefix $verification_code";

        $url = 'https://rest.nexmo.com/sms/json?'.http_build_query(
                [
                    'api_key' =>  $_ENV['NEXMO_API_KEY'],
                    'api_secret' => $_ENV['NEXMO_API_SECRET'],
                    'to' => $phone_number,
                    'from' => $_ENV['NEXMO_FROM_NUMBER'],
                    'text' => $message
                ]
            );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        //Decode the json object you retrieved when you ran the request.
        $decoded_response = json_decode($response, true);
        return $decoded_response;
    }
}
