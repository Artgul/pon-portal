<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
//auth routes>>
Route::group(['namespace' => 'Auth'], function () {
    Route::get('/login', 'LoginController@showLoginForm')->name('login');
    Route::post('/login', 'LoginController@login');
    Route::post('/logout', 'LoginController@logout')->name('logout');

    Route::get('/register', 'RegisterController@showRegistrationForm')->name('register');
    Route::post('/register', 'RegisterController@register');

    Route::get('/password/reset', 'ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('/password/reset', 'ResetPasswordController@reset');
});
//auth routes<<

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {

$name = 'Trigun'; //$request->input('name');
$code = 'Алгебраист'; //$request->input('register_code');
$page = file_get_contents('https://pathofninja.ru/info_pl.php?pl=' . urlencode($name));

$lvl = '/.*\[(\d+).*\]/';
$authcode = '/.*<br>.*(' . $code . ')/';
//$clanreg = '/.*org:\s\'(.*?)\'/';

preg_match($lvl, $page, $lvls);
preg_match($authcode, $page, $matches);
//preg_match($clanreg, $parser, $clanmatches);

if (isset($lvls[1])) {//если уровень найден
    if (intval($lvls[1] >= 17)) {//если уровень подходит
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
});
