<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Api\AuthorizationRequest;

class AuthorizationsController extends Controller
{

    /**
     * 第三方登录
     */
    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        // 如果社交登录类型在数组中(当前数组只有weixin)
        if (!in_array($type, ['weixin'])) {
            // 返回响应 400 请求无效 (因为我们当前暂时考虑使用微信登录)
            return $this->response->errorBadRequest();
        }

        // 实例化 weixin 驱动
        $driver = Socialite::driver($type);

        try {
            // 如果有授权码参数传递 - 服务端处理
            if ($code = $request->code) {
                // 则通过授权码服务端处理获取 access_token 等相关数据
                $response = $driver->getAccessTokenResponse($code);
                $token = array_get($response, 'access_token');
            } else {
                // 否则客户端直接传递获取 access_token openid 之类
                $token = $request->access_token;

                if ($type == 'weixin') {
                    $driver->setOpenId($request->openid);
                }
            }
            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception  $e) {
            return $this->response->errorUnauthorized('参数错误，未获取用户信息');
        }

        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;

                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;
        }

        // 返回创建成功的用户ID信息
        // return $this->response->array(['token' => $user->id]);
        // 获取 jwt token
        $token = \Auth::guard('api')->fromUser($user);
         // 使用 jwt 返回响应
        return $this->respondWithToken($token);
    }

    /**
     * 普通登录
     * jwt 文档参考 : https://jwt-auth.readthedocs.io/en/develop/quick-start/
     */
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;

        // 过滤判断用户登录使用的是邮箱还是手机
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username : $credentials['phone'] = $username;

        $credentials['password'] = $request->password;

        // 获取 jwt token
        if (!$token = \Auth::guard('api')->attempt($credentials)) {
            return $this->response->errorUnauthorized('用户名或密码错误');
        }

        // 正确获取后返回响应 代码复用下面集成
        // return $this->response->array([
        //     'access_token' => $token,
        //     'token_type' => 'Bearer',
        //     'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
        // ])->setStatusCode(201);
        return $this->respondWithToken($token);
    }

    /**
     * 登录成功后使用 jwt 返回响应
     */
    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * 刷新 JWT token
     */
    public function update()
    {
        // $token = \Auth::guard('api')->refresh();
        $token = auth('api')->refresh();
        return $this->respondWithToken($token);
    }


     /**
      * 删除 JWT token
      */
    public function destroy()
    {
        // $token = \Auth::guard('api')->logout();
        $token = auth('api')->logout();
        // 204 No Content - 对不会返回响应体的成功请求进行响应（比如 DELETE 请求）
        return $this->response->noContent();
    }
}
