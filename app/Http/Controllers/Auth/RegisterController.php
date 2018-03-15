<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Str;
Use Illuminate\Http\Request;
use App\Setting;

class RegisterController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Register Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users as well as their
      | validation and creation. By default this controller uses a trait to
      | provide this functionality without requiring any additional code.
      |
     */

use RegistersUsers;

    /**
     * Where to redirect users after registration.
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

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255|unique:users',
                    'password' => 'required|string|min:6|confirmed',
                    'register_code' => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data) {
        return User::create([
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
        ]);
    }

    public function showRegistrationForm() {
        $register_code = Str::random(32);
        $required_lvl = Setting::first()->required_lvl;
        $data = [
            'register_code' => $register_code,
            'required_lvl' => $required_lvl
        ];
        return view('auth.register', $data);
    }

    public function register(Request $request) {
        $name = $request->input('name');
        $code = $request->input('register_code');
        $required_lvl = Setting::first()->required_lvl;
        $page = file_get_contents('https://pathofninja.ru/info_pl.php?pl=' . urlencode($name));

        $lvl = '/.*\[(\d+).*\]/';
        $authcode = '/.*<br>.*(' . $code . ')/';
        //$clanreg = '/.*org:\s\'(.*?)\'/';

        preg_match($lvl, $page, $lvls);
        preg_match($authcode, $page, $matches);
        //preg_match($clanreg, $parser, $clanmatches);

        if (isset($lvls[1])) {//если уровень найден
            if (intval($lvls[1] >= $required_lvl)) {//если уровень подходит
            } else {//если уровень не подходит
                return redirect('/register')->withInput($request->only('name', 'password', 'password_confirmation'))
                                ->withErrors(['register_code' => 'Ваш уровень ниже требуемого']);
            }
        } else {//если уровень не найден
            return redirect('/register')->withInput($request->only('name', 'password', 'password_confirmation'))
                            ->withErrors(['register_code' => 'Парсер сломался, обратитесь к Trigun']);
        }

        if ($matches[1] == $code) {//если код найден и совпадает
            $this->validator($request->all())->validate();

            $this->guard()->login($this->create($request->all()));

            return redirect($this->redirectPath());
        } else {

            return redirect('/register')->withInput($request->only('name', 'password', 'password_confirmation'))
                            ->withErrors(['register_code' => 'Не найден регистрационный код в анкете персонажа']);
        }
        
    }

}
