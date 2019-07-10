<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use Laravel\Socialite\Facades\Socialite;

class AuthorizationsController extends Controller
{

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
        return $this->response->array(['token' => $user->id]);
    }
}
