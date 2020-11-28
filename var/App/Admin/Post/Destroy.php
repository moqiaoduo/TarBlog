<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 18:09
 */

namespace App\Admin\Post;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;
use Utils\DB;

class Destroy extends NoRender
{
    public function execute(): bool
    {
        Auth::check('post-base');

        $isAdmin = Auth::check('post-premium', false); // post-premium级别视为操作的最高权限

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        $meta_ids = [];

        // 这里相比回收精简了一点
        $ids = $this->db->table('contents')->whereIn('cid', $ids)->when(!$isAdmin, function ($query) {
            $query->where('uid', Auth::id()); // 不是管理员无法删除其他用户的文章
        })->whereNotNull('deleted_at')->where(function ($query) {
            $query->where('type', 'post')->orWhere('type', 'post_draft');
        })->pluck('cid');

        DB::beginTransaction();

        try {
            foreach ($ids as $cid) {
                // 统计受到影响的meta id
                $meta_ids += $this->db->table('relationships')->where('cid', $cid)
                    ->select('mid')->pluck('mid');
                // 删除文章的草稿
                $this->db->table('contents')->where('type', 'post_draft')
                    ->where('parent', $cid)->delete();
            }

            // 删除文章与其关联
            $count = $this->db->table('contents')->whereIn('cid', $ids)->delete(true);
            $this->db->table('relationships')->whereIn('cid', $ids)->delete();

            Sync::metaCount($meta_ids); // 同步计数

            DB::commit();

            $this->request->session()->flash('success', '成功将 ' . $count . ' 篇文章永久删除');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('永久删除文章时出现问题');

            back();
        }

        return true;
    }
}