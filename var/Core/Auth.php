<?php
/**
 * Created by tarblog.
 * Date: 2020/6/2
 * Time: 0:53
 */

namespace Core;

use Core\Http\Cookie;
use Core\Http\Request;
use Core\Http\Session;
use Models\User;
use Utils\DB;
use Utils\Str;

class Auth
{
    /**
     * 请求
     *
     * @var Request
     */
    private $request;

    /**
     * session
     *
     * @var Session
     */
    private $session;

    /**
     * 用户数据缓存
     *
     * @var User
     */
    private $user;

    /**
     * 初始化（使用自动注入）
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->session = $this->request->session();
    }

    /**
     * 是否已登录（根据id判断）
     *
     * @return bool
     */
    public function hasLogin()
    {
        return $this->id() > 0;
    }

    /**
     * 获取用户
     *
     * @return User|null
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $user = $this->session->get('tarblog_user');

        if (!is_null($user)) {
            $user = DB::table('users')->where('auth_token', $user)->firstWithModel(User::class);
            if (is_null($user)) return null;
            $this->user = $user;
            return $user;
        }

        $user = Cookie::get('tarblog_user');

        if (!is_null($user)) {
            $user = DB::table('users')->where('auth_token', $user)->firstWithModel(User::class);
            if (is_null($user)) return null;
            $this->session->set('tarblog_user', $user->remember_token);
            Cookie::set('tarblog_user', $user->remember_token, time() + 7 * 24 * 3600); // 续7天
            $this->user = $user;
            return $user;
        }

        return null;
    }

    /**
     * 获取登录用户id
     *
     * @return int|null
     */
    public function id()
    {
        return $this->user() ? $this->user->id : null;
    }

    /**
     * 获取认证路由（列表）
     * 本程序所支持权限：
     * admin,editor,writer,poster,reader
     * 管理员 编辑  写手  投稿者  读者
     * 如果不在列表中，即无权限
     *
     * @return array
     */
    protected static function authRoute()
    {
        return [
            //访问仪表盘
            'dashboard' => ['admin', 'editor', 'writer', 'poster', 'reader'],
            //基本的写作功能，只能查看、编辑和删除自己的文章，
            //是否自动变为投稿状态取决于程序设定，无法自行设定文章状态和开关评论
            //当然标签是可以自己选的
            'post-base' => ['admin', 'editor', 'writer', 'poster'],
            //基础的管理，写手及以上可以设定文章状态，可以开关评论
            'post-base-manager' => ['admin', 'editor', 'writer'],
            //编辑及以上使用，可以管理所有文章
            'post-premium' => ['admin', 'editor'],
            //编辑及以上使用，可以管理所有分类
            'category' => ['admin', 'editor'],
            //编辑及以上使用，可以管理所有标签
            'tag' => ['admin', 'editor'],
            //页面管理，仅限管理员使用
            'page' => ['admin'],
            //附件管理，仅限管理员使用
            'attachment' => ['admin'],
            //评论管理，投稿者及以上可用
            //写手和投稿者只能管理自己文章的评论，编辑和管理员可以管理所有评论
            'comment' => ['admin', 'editor', 'writer', 'poster'],
            //所有管理员级别的设置只能由管理员操作，例如主题、插件、设置等
            'admin-level' => ['admin']
        ];
    }

    public function check($page, $autoRedirect = true)
    {
        if (!self::hasLogin()) {
            if ($autoRedirect) redirect(siteUrl());
            else return false;
        }
        $identity = $this->user->identity;
        if (!in_array($identity, self::authRoute()[$page])) {
            if ($autoRedirect) redirect(siteUrl());
            else return false;
        }
        return true;
    }

    /**
     * 尝试登录
     *
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return bool
     */
    public function attempt($username, $password, $remember = false)
    {
        $user = DB::table('users')->where('username', $username)
            ->orWhere('email', $username)->first();

        if (!$user) return false;

        if (!password_verify($password, $user['password'])) return false;

        if (empty($user['auth_token']))
            DB::table('users')->where('id', $user['id'])
                ->update(['auth_token' => $user['auth_token'] = Str::random(32)]);

        if ($remember) {
            Cookie::set('tarblog_user', $user['auth_token'], time() + 7 * 24 * 3600); // 续7天
        }

        $this->session->set('tarblog_user', $user['auth_token']);

        return true;
    }

    public function register($username, $password, $email, $extra = [])
    {
        $password = password_hash($password, PASSWORD_DEFAULT);

        return DB::table('users')->insert(compact('username', 'password', 'email') + $extra +
            auto_fill_time());
    }

    public function logout()
    {
        Cookie::delete('tarblog_user');
        $this->session->delete('tarblog_user');
    }
}