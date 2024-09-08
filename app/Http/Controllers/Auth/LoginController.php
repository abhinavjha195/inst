<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use Auth; 
// use App\Rules\ReCaptcha;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request): void
    {
        //$rules = [$this->username() => 'required|string', 'password' => 'required|string', 'captcha' => 'required|captcha'];
        $rules = [
			$this->username() => 'required|string', 
			'password' => 'required|string',
			
		];

        $this->validate($request, $rules);
    }

    protected function credentials(Request $request)
    {
        return [
            'email' => request()->email,
            'password' => request()->password,
            'isactive' => ( request()->email == 'sarbagyan@cybrain.co.in' ? 0 : 1 )
        ];
    }
	
	protected function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/login');
    }

    // public function login(Request $request) {
    //     $this->validate($request, [
	// 		'email'   => 'required',
	// 		'password' => 'required',
			
	// 	]);	
    //     $vars = array(
    //         'secret' => env('RECAPTCHA_SECRET'),
    //         "response" => $request->input('recaptcha')
    //     );
    //     $url = "https://www.google.com/recaptcha/api/siteverify";
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    //     $encoded_response = curl_exec($ch);
    //     $response = json_decode($encoded_response, true);
    //     curl_close($ch);
        
    //     if($response['success'] && $response['score'] > 0.5 ) {
    //         if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
    //             return redirect('/dashboard/banners');
    //         } else{
    //             return redirect('login')->with('ErrorLogin', 'Incorrect email or password');
    //         }
    //     } else {
    //         return redirect('login')->with('status', 'Some issue with login, try again lator');
    //     }
    // }


    public function login(Request $request) {

        // print_r($request);
        // die();
        // $this->validate($request, [
		// 	'email'   => 'required',
		// 	'password' => 'required',
			
		// ]);	
        // $vars = array(
        //     'secret' => env('RECAPTCHA_SECRET'),
        //     "response" => $request->input('recaptcha')
        // );
        // $url = "https://www.google.com/recaptcha/api/siteverify";
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        // $encoded_response = curl_exec($ch);
        // $response = json_decode($encoded_response, true);
        // curl_close($ch);
        
        if(1==1) {
            // echo Hash::make('abhinav');
            // die();
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect('/dashboard/banners');
            } else{
                return redirect('login')->with('ErrorLogin', 'Incorrect email or password');
            }
        } else {
            return redirect('login')->with('status', 'Some issue with login, try again lator');
        }
    }
}
