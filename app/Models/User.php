<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
// 邮箱验证 trait, 拥有下面3个方法:
// hasVerifiedEmail() 检测用户 Email 是否已认证；
// markEmailAsVerified() 将用户标示为已认证
// sendEmailVerificationNotification() 发送 Email 认证的消息通知，触发邮件的发送
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
// PHP 的接口类，继承此类将确保 User 遵守契约，拥有上面提到的三个方法
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use App\Models\Traits\ActiveUserHelper;
use App\Models\Traits\LastActivedAtHelper;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmailTrait;
    use ActiveUserHelper, LastActivedAtHelper;
    use HasRoles;

    use Notifiable {
    // 将 trait 中的 notify 方法当做为 laravelNotify 引用
    notify as protected laravelNotify;
    }

    // 重写 trait 中的notify方法
    public function replyNotify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了！
        if ($this->id == \Auth::id()) {
            return;
        }

        // 只有数据库类型通知才需提醒，直接发送 Email 或者其他的都 Pass
        if (method_exists($instance, 'toDatabase')) {
            $this->increment('notification_count');
        }

        $this->notify($instance);
    }

    /**
     * 清空user未读消息以及标志notification消息已读
     */
    public function markAsRead()
    {
        // 清空user未读消息
        $this->notification_count = 0;
        $this->save();
        // 标志notification消息已读
        $this->unreadNotifications->markAsRead();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'email', 'password', 'weixin_openid', 'weixin_unionid', 'avatar', 'introduction'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 使用 属性修改器 为后台修改密码时进行加密处理
    public function setPasswordAttribute($value)
    {
        if (strlen($value) != 60) {
            // 不是60位字符串的密码参数则做加密处理
            $value = Hash::make($value);
        }

        $this->attributes['password'] = $value;
    }

    // 使用 属性修改器 为后台修改头像图片时进行地址补全
    public function setAvatarAttribute($value)
    {
        // 如果不是 `http` 子串开头，那就是从后台上传的，需要补全 URL
        if (!starts_with($value, 'http')) {
            $value = config('app.url') . "/uploads/images/avatar/$value";
        }

        $this->attributes['avatar'] = $value;
    }

    /**
     * 关联关系
     * 一个用户拥有多个话题
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * 关联关系
     * 一个用户拥有多个回复
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * 权限控制中的判断重用方法
     */
    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }
}
