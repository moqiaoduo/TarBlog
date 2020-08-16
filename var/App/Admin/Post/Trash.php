<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 17:10
 */

namespace App\Admin\Post;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;
use Utils\DB;

class Trash extends NoRender
{
    public function execute(): bool
    {
        Auth::check('post-base');

        $isAdmin = Auth::check('post-premium', false); // post-premium级别视为操作的最高权限

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        $meta_ids = [];

        $data = $this->db->table('contents')->where(function ($query) {
            $query->where('type', 'post')->orWhere('type', 'post_draft');
        })->whereIn('cid', $ids)->when(!$isAdmin, function ($query) {
            $query->where('uid', Auth::id()); // 不是管理员无法删除其他用户的文章
        })->whereNull('deleted_at')->pluck('cid');

        DB::beginTransaction();

        try {
            foreach ($data as $cid) {
                // 统计受到影响的meta id
                $meta_ids += $this->db->table('relationships')->where('cid', $cid)
                    ->select('mid')->pluck('mid');
                // “删除”文章的草稿
                $this->db->table('contents')->where('type', 'post_draft')
                    ->where('parent', $cid)->update(['deleted_at' => dateX()]);
            }

            // “删除”文章
            $count = $this->db->table('contents')->whereIn('cid', $ids)
                ->update(['deleted_at' => dateX()], true);

            Sync::metaCount($meta_ids); // 同步计数

            DB::commit();

            $this->request->session()->flash('success', '成功将 ' . $count . ' 篇文章移入回收站');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('将文章移入回收站时出现问题');

            back();
        }

        return true;
    }
}