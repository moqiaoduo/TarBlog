<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 11:44
 */

namespace App\Admin\Tag;

use App\NoRender;
use Utils\Auth;
use Utils\DB;

class Delete extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('tag');

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        DB::beginTransaction();

        try {
            $count = $this->db->table('metas')->whereIn('mid', $ids)->delete(true);

            $this->db->table('relationships')->whereIn('mid', $ids)->delete();

            DB::commit();

            $this->request->session()->flash('success', '成功删除 ' . $count . ' 个标签');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('删除标签时出现问题');

            back();
        }

        return true;
    }
}