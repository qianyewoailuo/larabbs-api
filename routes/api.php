<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes API路由
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 示例 api
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


$api = app(Dingo\Api\Routing\Router::class);

// 第一个 dingo/api 测试 - version测试选择
$api->version('v1',function($api){
    $api->get('version',function(){
        return response('this is version v1 response');
    });
});

$api->version('v2',function($api){
    $api->get('version',function(){
        return response('this is version v2 response');
    });
});

// 发送短信验证码 API
$api->version('v1',[
    // namespace 参数使 v1 版本的路由都会指向 App\Http\Controllers\Api
    'namespace' => 'App\Http\Controllers\Api'
],function($api){
    $api->group([
        'middleware' => 'api.throttle', // From:dingo\api\src\Ratelimit\Throttle\throttle.php
        'limit'      => config('api.rate_limits.sign.limit'),
        'expires'    => config('api.rate_limits.sign.expires'),
    ],function($api){
        // 短信验证码
        $api->post('verificationCodes', 'VerificationCodesController@store')
            ->name('api.verificationCodes.store');

        // 手机用户注册
        $api->post('users', 'UsersController@store')
            ->name('api.users.store');

        // 图片验证码
        $api->post('captchas','CaptchasController@store')
            ->name('api.captchas.store');

        // 第三方登录
        $api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
            ->name('api.socials.authorizations.store');

        // 登录
        $api->post('authorizations','AuthorizationsController@store')
            ->name('api.authorizations.store');

        // 刷新 JWT token
        $api->put('authorizations/current', 'AuthorizationsController@update')
            ->name('api.authorizations.update');
        // 删除 JWT token
        $api->delete('authorizations/current', 'AuthorizationsController@destroy')
            ->name('api.authorizations.destroy');
    });
});
