<?php
/**
 * Created by tarblog.
 * Date: 2020/8/12
 * Time: 15:24
 */

namespace App\Admin\User;

use App\NoRender;
use Utils\Auth;
use Utils\DB;

class Delete extends NoRender
{
    public function execute(): bool
    {
        Auth::check('admin-level');

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        $ids = array_filter($ids, function ($id) {
            return $id != Auth::id();
        });

        $count = $this->db->table('users')->whereIn('id', $ids)->delete(true);

        $this->request->session()->flash('success', '已成功删除 ' . $count . ' 个用户');

        back();

        return true;
    }
}