<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reply;

class TopicReplied extends Notification implements ShouldQueue  // shouldQueue 增加队列
{
    use Queueable;

    public $reply;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['mail'];
        // return ['database'];    // 使用数据库频道通知
        return ['database','mail'];     // 使用数据库与邮件频道通知
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return reply_data
     */
    public function toDatabase($notifiable)
    {
        $topic = $this->reply->topic;
        $link =  $topic->link(['#reply' . $this->reply->id]);

        // 存入数据库里的数据
        return [
            'reply_id' => $this->reply->id,
            'reply_content' => $this->reply->content,
            'user_id' => $this->reply->user->id,
            'user_name' => $this->reply->user->name,
            'user_avatar' => $this->reply->user->avatar,
            'topic_link' => $link,
            'topic_id' => $topic->id,
            'topic_title' => $topic->title,
        ];
    }

    public function toMail($notifiable)
    {
        $url = $this->reply->topic->link(['#reply' . $this->reply->id]);

        return (new MailMessage)
            ->subject('您有新回复')
            ->line('你的话题有新回复')
            ->action('查看回复',$url);
    }

}
