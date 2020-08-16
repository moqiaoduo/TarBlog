<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 19:13
 */

namespace Models;

use Core\Database\Model;
use Utils\DB;

/**
 * @property $cid
 * @property $title
 * @property $slug
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $content
 * @property $uid
 */
class Attachment extends Model
{
    public function author()
    {
        return DB::table('users')->where('id', $this->uid)->first();
    }
}