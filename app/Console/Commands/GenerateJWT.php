<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateJWT extends Command
{

    protected $signature = 'larabbs:generate-token';

    protected $description = '快速为用户生成 token';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $userId = $this->ask('输入用户ID');

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return $this->error('用户不存在');
        }

        // 一年以后过期
        $ttl = 365 * 24 * 60;
        $this->info(\Auth::guard('api')->setTTL($ttl)->fromUser($user));
    }
}
