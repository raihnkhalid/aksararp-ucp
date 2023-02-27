<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use App\Models\UserVerify;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    private function checkInput()
    {
        $login = request()->input('username');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $login]);
        return $field;
    }

    public function sendEmailVerification()
    {
        $token = Str::random(64);
        UserVerify::create([
            'user_id' => Auth::user()->id,
            'token' => $token
        ]);

        Mail::send('emails.emailVerificationEmail', ['token' => $token], function($message){
            $message->to(Auth::user()->email);
            $message->subject('Account Verification');
        });

        return redirect()->route('verifyAccount')->with(['message' => 'We have successfully resend your activation code, please check your email.', 'button' => 'Resend Verification Email', 'link' => 'sendVerify', 'resend' => 'true']);
    }

    public function index()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function verifyPage()
    {
        return view('verifyAccount');
    }

    public function forgotPassword()
    {
        return view('auth.forgotPassword');
    }

    public function home()
    {
        if (Auth::check()) {
            return view('home');
        }

        return redirect()->route('login')
                ->with('messageNotLogin', 'You must log in first!');
    }

    public function actionLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:8',
        ]);

        if(Auth::attempt([$this->checkInput() => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();

            return redirect()->intended('home');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records'
        ]);
    }

    public function actionRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|min:8',
        ]);

        $users = Users::create([
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'admin' => '0',
            'ip_register' => $request->ip()
        ]);

        $token = Str::random(64);
        UserVerify::create([
            'user_id' => $users->id,
            'token' => $token
        ]);

        Mail::send('emails.emailVerificationEmail', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Account Verification');
        });

        Session::flash('message', 'true');
        return redirect('register');
    }

    public function actionForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = Str::random(64);
        PasswordResets::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('emails.emailForgotPassword', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });


    }

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect('login');
    }

    public function verifyAccount($token)
    {
        $isVerified = Auth::user()->is_email_verified;

        if ($isVerified) {
            $message = 'Your account has been successfully verified, you can start using UCP now.';
            $button = 'Home';
            $link = 'home';
        } else {
            $verifyUser = UserVerify::where('token', $token)->first();

            if ($verifyUser) {
                $verifyUser->user->is_email_verified = 1;
                if($verifyUser->user->save()){
                    UserVerify::where('user_id', $verifyUser->user_id)->delete();
                    $message = 'Your account has been successfully verified, you can start using UCP now.';
                    $button = 'Home';
                    $link = 'home';
                }
            } else {
                $message = 'Your token is invalid, try to request new verification email.';
                $button = 'Resend Verification Email';
                $link = 'sendVerify';

            }
        }

        return redirect()->route('verifyAccount')->with(['message' => $message, 'button' => $button, 'link' => $link, 'resend' => true]);

    }

    public function verify(Request $request)
    {
        if ($request->session()->has('resend')) {
            return view('verifyAccount');
        } else {
            $isVerified = Auth::user()->is_email_verified;

            if ($isVerified) {
                Session::flash('message', 'Your account has been successfully verified, you can start using UCP now.');
                Session::flash('button', 'Home');
                Session::flash('link', 'home');
            } else {
                Session::flash('message', 'You need to confirm your account before accessing UCP. We have sent you an activation code, please check your email.');
                Session::flash('button', 'Resend Verification Email');
                Session::flash('link', 'sendVerify');
            }
        }
        return view('verifyAccount');
    }


}
