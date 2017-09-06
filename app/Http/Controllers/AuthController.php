<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Hash, Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;


class AuthController extends Controller
{
    /**
     * API Register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
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

        if ($validator->fails())
            return response()->json(['success' => false, 'error' => $validator->messages()]);

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);

        $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'verified' => false, //add flag to indicate account has not be verified
                'message' => 'Thanks for signing up! Verify your account to complete your registration.',
            ]
        ]);
    }

    /**
     * Send Verification Code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerificationCode(Request $request)
    {
        $rules = [
            'phone_number' => 'required|min:9|unique:users',
            'country_code' => 'required'
        ];

        $input = $request->all();

        $validator = Validator::make($input, $rules);
        if ($validator->fails())
            return response()->json(['success' => false, 'error' => $validator->messages()]);

        $phone_number = $request->phone_number;

        //CURL REQUEST STARTS HERE
        $params = array(
            'api_key' => getenv('AUTHY_API_KEY'),
            'via' => 'sms',
            'phone_number' => $request->phone_number,
            'country_code' => $request->country_code
        );

        $defaults = array(
            CURLOPT_URL => "https://api.authy.com/protected/json/phones/verification/start",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        curl_close($ch);

        //Decode the json object you retrieved when you ran the request.
        $decoded_response = json_decode($response, false);
        //CURL REQUEST ENDS HERE

        if ($decoded_response->success) {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => "We have sent you an SMS with a code to $phone_number. To complete your registration, please enter the activation code.",
                ]
            ]);
        } else {
            return response()->json(['success' => false, 'error' => $decoded_response->message]);
        }
    }

    /**
     * API Verify Code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyCode(Request $request)
    {
        $rules = [
            'country_code' => 'required',
            'phone_number' => 'required',
            'verification_code' => 'required'
        ];

        $input = $request->all();

        $validator = Validator::make($input, $rules);
        if ($validator->fails())
            return response()->json(['success' => false, 'error' => $validator->messages()]);

        $phone_number = $request->phone_number;
        $country_code = $request->country_code;
        $verification_code = $request->verification_code;

        //CURL REQUEST STARTS HERE
        $url = "https://api.authy.com/protected/json/phones/verification/check?phone_number=$phone_number&country_code=$country_code&verification_code=$verification_code";

        $defaults = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('X-Authy-API-Key: ' . getenv('AUTHY_API_KEY')),
//            CURLOPT_POSTFIELDS => $params
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);

        curl_close($ch);

        //Decode the json object you retrieved when you ran the request.
        $decoded_response = json_decode($response, false);

        //CURL REQUEST ENDS HERE

        if ($decoded_response->success) {
            //save the phone number in the database
            //update is verified column in database
            $user = Auth::user();
            $user->update([
                'is_verified' => 1,
                'phone_number' => $phone_number
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => "You have successfully verified your account.",
                ]
            ]);
        } else {
            return response()->json(['success' => false, 'error' => $decoded_response->message]);
        }
    }

    /**
     * API Login, on success return JWT Auth token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $input = $request->only('email', 'password');

        $validator = Validator::make($input, $rules);

        if ($validator->fails())
            return response()->json(['success' => false, 'error' => $validator->messages()]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['success' => false, 'error' => 'Invalid Credentials. Please make sure you entered the right information and you have verified your email address.'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'could_not_create_token'], 500);
        }

        $user = Auth::user();

        $verified = ($user->is_verified == 1) ? true : false;

        // all good so return the token
        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'verified' => $verified, //add flag to indicate if account has been verified
            ]
        ]);
    }


    /**
     * Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $this->validate($request, ['token' => 'required']);

        try {
            JWTAuth::invalidate($request->input('token'));
            return response()->json(['success' => true]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }

    /**
     * API Recover Password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recover(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $error_message = "Your email address was not found.";
            return response()->json(['success' => false, 'error' => ['email' => $error_message]], 401);
        }

        try {
            Password::sendResetLink($request->only('email'), function (Message $message) {
                $message->subject('Your Password Reset Link');
            });
        } catch (\Exception $e) {
            //Return with error
            $error_message = $e->getMessage();
            return response()->json(['success' => false, 'error' => $error_message], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'A reset email has been sent! Please check your email.'
            ]
        ]);
    }



    /**
     * Create Error Message
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getErrorMessage(){

    }
}
