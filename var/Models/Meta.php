<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 22:59
 */

namespace Models;

use Core\Database\Model;
use Core\Paginator;
use Utils\DB;

class Meta extends Model
{
    public function getPostsPaginate($page, $pageSize = null)
    {
        $r = DB::table('relationships')->where('mid', $this->mid)->pluck('cid');

        if (empty($r)) return new Paginator();

        if (empty($pageSize)) $pageSize = get_option('pageSize', 10);

        return DB::table('contents')->where('type','post')->whereNull('deleted_at')
            ->whereIn('status', ['publish', 'password'])->whereIn('cid', $r)
            ->orderByDesc('created_at')->paginate($page, $pageSize);
    }

    public function getPostBySlug($slug, $model = null)
    {
        $result = DB::query("select * from contents c where deleted_at is null and type = 'post' and slug = ? and ".
            "exists(select * from relationships r where mid = ? and r.cid = c.cid)", [$slug, $this->mid], true);

        if (is_null($result)) return null;

        return $model ? new $model($result) : $result;
    }

    public function getPostById($id, $model = null)
    {
        $result = DB::query("select * from contents c where deleted_at is null and type = 'post' and cid = ? and ".
            "exists(select * from relationships r where mid = ? and r.cid = c.cid)", [$id, $this->mid], true);

        if (is_null($result)) return null;

        return $model ? new $model($result) : $result;
    }
}