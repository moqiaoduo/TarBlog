<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/27
 * Time: 1:11
 */

namespace App\Admin;

use App\NoRender;
use Core\Http\Cookie;
use Utils\DB;
use Utils\Str;

class Logout extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $other = $this->request->get('other', 0);

        DB::table('users')->where('id', $this->user->id())
            ->update(['auth_token' => $token = Str::random(32)]); // 更新token

        // 假如是登出其他设备，那本地为了不被登出，就得更新token
        if ($other) {
            $old_token = $this->request->session('tarblog_user');
            $this->request->session()->set('tarblog_user', $token);
            // 记住登录状态的话，得更新到cookie
            if (!empty($old_cookie = Cookie::get('tarblog_user')) && $old_token == $old_cookie)
                Cookie::set('tarblog_user', $token);
        }

        $this->request->session()->flash('success', '其他设备已被强制登出');

        back();

        return true;
    }
}