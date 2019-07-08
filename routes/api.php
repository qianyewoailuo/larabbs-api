<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
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

// 第一个 dingo/api 测试 - version测试选择
$api = app(Dingo\Api\Routing\Router::class);

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
