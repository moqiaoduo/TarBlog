<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 18:09
 */

namespace App\Admin\Page;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;
use Utils\DB;

class Destroy extends NoRender
{
    public function execute(): bool
    {
        Auth::check('page');

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        DB::beginTransaction();

        try {
            // 删除页面的草稿
            $this->db->table('contents')->where('type', 'page_draft')->whereIn('cid', $ids)
                ->whereNotNull('deleted_at')->delete();

            // 删除页面
            $count = $this->db->table('contents')->whereIn('cid', $ids)->where('type', 'page')
                ->whereNotNull('deleted_at')->delete(true);

            DB::commit();

            $this->request->session()->flash('success', '成功将 ' . $count . ' 个页面永久删除');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('永久删除页面时出现问题');

            back();
        }

        return true;
    }
}