<?php
/**
 * Created by tarblog.
 * Date: 2020/6/2
 * Time: 0:52
 */

namespace Models;

use Core\Database\Model;
use Utils\DB;

/**
 * @property $id
 * @property $name
 * @property $email
 * @property $password
 * @property $username
 * @property $url
 * @property $identity
 * @property $remember_token
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    public function isAdmin()
    {
        return $this->identity == 'admin';
    }

    public function postCount()
    {
        return DB::table('contents')->where('type', 'post')
            ->where('uid', $this->id)->count();
    }
}