<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 16:47
 */

namespace Models;

use Utils\DB;

class Page extends Content
{
    public function author()
    {
        return DB::table('users')->where('id', $this->uid)->first();
    }
}