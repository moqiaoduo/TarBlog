<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 19:04
 */

namespace Utils;

use Core\Facade;
use Models\User;

/**
 * @method static int|null id()
 * @method static bool hasLogin()
 * @method static bool check($page, $autoRedirect = true)
 * @method static User|null user()
 * @method static bool attempt($username, $password, $remember = false)
 * @method static bool register($username, $password, $email, $extra = [])
 * @method static void logout()
 *
 * @see \Core\Auth
 */
class Auth extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeInstanceAlias()
    {
        return 'auth';
    }
}