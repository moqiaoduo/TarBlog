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

    public function id()
    {
        return $this->user->id;
    }

    public function email()
    {
        return $this->user->email;
    }

    public function isAdmin()
    {
        return $this->user->isAdmin();
    }

    public function screenName($return = false)
    {
        $screenName = $this->user->name ?? $this->user->username;

        if ($return)
            return $screenName;

        echo $screenName;
    }

    public function hasLogin()
    {
        return $this->auth->hasLogin();
    }
}