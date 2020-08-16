<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 18:11
 */

namespace App\Admin\Page;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;
use Utils\DB;

class Restore extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('page');

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        DB::beginTransaction();

        try {
            // 恢复文章的草稿
            $this->db->table('contents')->where('type', 'page_draft')->whereNotNull('deleted_at')
                ->whereIn('cid', $ids)->update(['deleted_at' => null]);

            // 恢复页面
            $count = $this->db->table('contents')->whereIn('cid', $ids)->where('type', 'page')
                ->whereNotNull('deleted_at')->update(['deleted_at' => null], true);

            DB::commit();

            $this->request->session()->flash('success', '成功恢复 ' . $count . ' 个页面');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('恢复页面时出现问题');

            back();
        }

        return true;
    }
}