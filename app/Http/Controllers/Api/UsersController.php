<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Api\UserRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * phoneUser regist
     */
    public function store(UserRequest $request)
    {
        $verifyData = Cache::get($request->verification_key);

        if(!$verifyData){
            // 参数错误返回 422
            return $this->response->error('验证码已失效,请重新发送',422);
        }

        if(!hash_equals($verifyData['code'],$request->verification_code)){
            // 防时序攻击字符串比较: 验证码不一致时返回 401
            return $this->response->errorUnauthorized('验证码错误');
        }

        // create user
        $user = User::query()->create([
            'name'      => $request->name,
            'phone'     => $verifyData['phone'],
            'password'  => Hash::make($request->password),
        ]);

        // clean code cache
        Cache::forget($request->verification_key);
    }
}
