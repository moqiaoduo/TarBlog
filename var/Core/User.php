<?php
/**
 * Created by tarblog.
 * Date: 2020/7/30
 * Time: 15:50
 */

namespace Core;

class User
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var \Models\User|null
     */
    private $user;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        $this->user = $auth->user();
    }

    /**
     * 获取用户id
     *
     * @return int|null
     */
    public function id()
    {
        return $this->hasLogin() ? $this->user->id : null;
    }

    /**
     * 获取用户邮箱
     *
     * @return string|null
     */
    public function email()
    {
        return $this->hasLogin() ? $this->user->email : null;
    }

    /**
     * 判断用户是否为管理员
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasLogin() && $this->user->isAdmin();
    }

    /**
     * 显示在屏幕上的名称
     *
     * @param bool $return
     * @return string|void
     */
    public function screenName($return = false)
    {
        if (!$this->hasLogin()) return null;

        $screenName = $this->user->name ?? $this->user->username;

        if ($return)
            return $screenName;

        echo $screenName;
    }

    /**
     * 是否登录
     *
     * @return bool
     */
    public function hasLogin()
    {
        return $this->auth->hasLogin();
    }
}