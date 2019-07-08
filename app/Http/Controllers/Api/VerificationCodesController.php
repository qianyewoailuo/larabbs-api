<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\VerificationCodeRequest;
use Overtrue\EasySms\EasySms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        // 返回一个测试用的响应,成功响应则表示接口路由正常
        // return $this->response->array([
        //     'test_message' => 'store verification code'
        // ]);
        $phone = $request->phone;

        // 生成4位随机数
        $code = mt_rand(1000, 9999);

        // 发送验证码短信
        try {
            $result = $easySms->send($phone, [
                // 你在腾讯云配置的 [短信正文] 的模板ID
                'template' => 329361,
                // data数组的内容对应于腾讯云 [短信正文] 里的变量
                'data' => [
                    $code,   // 变量1 - 验证码
                    10,   // 变量2 - 有效时间
                ],
            ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            // 捕获并返回错误信息
            $message = $exception->getException('qcloud')->getMessage();
            return $this->response->errorInternal($message ?: '短信发送异常');
        }

        // 缓存 key
        $cache_key = 'verificationCode_' . str_random(15);
        // 验证码过期时间 这里也可以使用 $expiredAt = 60*10
        // 因为除了以整数形式传递过期时间的秒数，还可以传递 DateTime 实例来表示该数据的过期时间
        $expiredAt = Carbon::now()->addMinutes(10);
        Cache::put($cache_key, ['phone' => $phone, 'code' => $code], $expiredAt);

        return $this->response->array([
            'key' => $cache_key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);     // 201 Created - 对创建新资源的 POST 操作进行响应

    }
}
