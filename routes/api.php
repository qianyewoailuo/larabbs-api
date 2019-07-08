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
    // 短信验证码
    $api->post('verificationCodes', 'VerificationCodesController@store')
        ->name('api.verificationCodes.store');

    // 手机用户注册
    $api->post('users','UsersController@store')
        ->name('api.users.store');
});
