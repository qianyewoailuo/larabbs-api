<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Requests\Api\CaptchaRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CaptchasController extends Controller
{
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder)
    {
        $key       = 'captcha_'. str_random(15);
        $phone     = $request->phone;

        // 创建图片验证码
        $captcha   = $captchaBuilder->build();
        // 验证码有效时间
        $expiredAt = Carbon::now()->addMinutes(5);

        // 供验证的缓存信息
        Cache::put($key, ['phone'=>$phone,'code'=>$captcha->getPhrase()], $expiredAt);

        // 返回的结果信息
        $result = [
            'captcha_key'     => $key,
            'expiredAt'       => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline(),    // base64 图片验证码
        ];

        return $this->response->array($result)->setStatusCode(201);
    }
}

