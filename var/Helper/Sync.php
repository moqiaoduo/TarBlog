<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 18:00
 */

namespace Helper;

use Utils\DB;

class Sync
{
    public static function metaCount($ids)
    {
        foreach (array_unique($ids) as $mid) {
            $cid = DB::table('relationships')->where('mid', $mid)->pluck('cid');

            if (empty($cid)) $count = 0;
            else $count = DB::table('contents')->whereNull('deleted_at')
                ->whereIn('cid', $cid)->count();

            // 由于有些文章不是彻底从数据库移除，所以要查明是不是已经标记删除了
            DB::table('metas')->where('mid', $mid)->update(['count' => $count]);
        }
    }

    public static function meta($cid, $list)
    {
        $ids = DB::table('relationships')->where('cid', $cid)->pluck('mid');

        $delete_ids = array_diff($ids, $list);

        $add_ids = array_diff($list, $ids);

        if (!empty($delete_ids))
            DB::table('relationships')->whereIn('mid', $delete_ids)->delete();

        foreach ($add_ids as $mid)
            DB::table('relationships')->insert(['cid' => $cid, 'mid' => $mid]);

        self::metaCount(array_merge($ids, $list));
    }

    public static function comment($cid)
    {
        DB::table('contents')->where('cid', $cid)
            ->update(['commentsNum' => DB::table('comments')->where('cid', $cid)->count()]);
    }
}