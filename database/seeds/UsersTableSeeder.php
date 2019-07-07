<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 获取 Faker 实例
        $faker = app(Faker\Generator::class);

        // 头像假数据
        $avatars = [
            'https://cdn.learnku.com/uploads/images/201710/14/1/s5ehp11z6s.png',
            'https://cdn.learnku.com/uploads/images/201710/14/1/Lhd1SHqu86.png',
            'https://cdn.learnku.com/uploads/images/201710/14/1/LOnMrqbHJn.png',
            'https://cdn.learnku.com/uploads/images/201710/14/1/xAuDMxteQy.png',
            'https://cdn.learnku.com/uploads/images/201710/14/1/ZqM7iaP4CR.png',
            'https://cdn.learnku.com/uploads/images/201710/14/1/NDnzMutoxX.png',

            // 中世纪怪物头像
            'https://s2.ax1x.com/2019/05/05/E0TbtK.png',
            'https://s2.ax1x.com/2019/05/05/E0TqfO.png',
            'https://s2.ax1x.com/2019/05/05/E0TX1e.png',
            'https://s2.ax1x.com/2019/05/05/E0TznA.png',
            'https://s2.ax1x.com/2019/05/05/E0THk6.png',
            'https://s2.ax1x.com/2019/05/05/E0TOpD.png',
            'https://s2.ax1x.com/2019/05/05/E0Tj6H.png',
            'https://s2.ax1x.com/2019/05/05/E07S0I.png',
            'https://s2.ax1x.com/2019/05/05/E07p7t.png',

            //  花丸幼稚园头像
            'https://s2.ax1x.com/2019/05/05/E04lVK.md.jpg',
            'https://s2.ax1x.com/2019/05/05/E041UO.md.jpg',
            'https://s2.ax1x.com/2019/05/05/E04uK1.jpg',
            'https://s2.ax1x.com/2019/05/05/E04KDx.md.jpg',

        ];

        // 生成数据集合
        $users = factory(User::class) // 根据指定的 User 生成模型工厂构造器，对应加载 UserFactory.php 中的工厂设置。
                    ->times(10) // 生成数据的数量
                    ->make()    // 生成集合对象
                    ->each(function ($user,$index) use ($faker,$avatars){
                        // each是 合对象提供的方法，用来迭代集合中的内容并将其传递到回调函数中
                        // 从头像数组中随机取出一个赋值
                        $user->avatar = $faker->randomElement($avatars);  // 随机元素可以使用 array_rand 或 mt_rand 等函数综合获取
                    });

        // 让隐藏字段可见并将数据集合转换为数组,确保入库时数据库不会报错
        $user_array = $users->makeVisible([
            'password','remember_token'
        ])->toArray();

        // 插入数据
        User::insert($user_array);

        // 单独处理第一个用户的数据
        $user = User::find(1);
        $user->name = 'qianyewoailuo';
        $user->email = 'qianyewoailuo@126.com';
        $user->avatar = 'https://s2.ax1x.com/2019/05/05/E04Mb6.jpg';
        $user->save();

        // 初始化用户角色，将 1 号用户指派为『站长』
        $user->assignRole('Founder');

        // 将 2 号用户指派为『管理员』
        $user = User::find(2);
        $user->assignRole('Maintainer');
    }
}
