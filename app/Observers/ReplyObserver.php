<?php

namespace App\Observers;

use App\Models\Reply;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    /**
     * 评论成功后统计并修改回复数
     */
    public function created(Reply $reply)
    {
        // 评论完毕统计评论数一般有两种方案
        // 1. 直接自增 +1
        // $reply->topic->increment('reply_count',1);
        // 2. 统计话题关联的回复数 - 此为最优方案
        $reply->topic->reply_count = $reply->topic->replies->count();
        $reply->topic->save();
    }

    /**
     * HTMLPurifier 进行XXS过滤
     */
    public function creating(Reply $reply)
    {
        $reply->content = clean($reply->content, 'user_topic_body');
    }
}
