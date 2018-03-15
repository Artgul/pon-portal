<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;

class ResetPasswordController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Password Reset Controller
      |--------------------------------------------------------------------------
      |
      | This controller is responsible for handling password reset requests
      | and uses a simple trait to include this behavior. You're free to
      | explore this trait and override any methods you wish to tweak.
      |
     */

//use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest');
    }

    protected function validator(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255|exists:users',
                    'password' => 'required|string|min:6|confirmed',
                    'register_code' => 'required',
        ]);
    }
    
    public function showResetForm() {
        $register_code = Str::random(32);
        $data = [
            'register_code' => $register_code
        ];
        return view('auth.passwords.reset', $data);
    }

    public function reset(Request $request) {
        $name = $request->input('name');
        $user = User::where('name', $name)->first();
        $password = $request->input('password');
        if (!$user) {
            return redirect('/register')->withInput($request->only('name', 'password', 'password_confirmation'))
                            ->withErrors(['register_code' => 'Register, before trying reset password']);
        }

        $code = $request->input('register_code');
        $page = file_get_contents('https://pathofninja.ru/info_pl.php?pl=' . urlencode($name));

        $authcode = '/.*<br>.*(' . $code . ')/';

        preg_match($authcode, $page, $matches);


        if (isset($matches[1])) {//если код найден и совпадает
            $this->validator($request->all())->validate();
            
            $user->password = Hash::make($password);
            $user->save();

            event(new PasswordReset($user));

            $this->guard()->login($user);

            return redirect($this->redirectPath());
        } else {

            return redirect('/password/reset')->withInput($request->only('name', 'password', 'password_confirmation'))
                            ->withErrors(['register_code' => 'Не найден регистрационный код в анкете персонажа']);
        }
    }

}
