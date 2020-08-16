<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 11:44
 */

namespace App\Admin\Tag;


use Utils\DB;

class Clean extends \App\NoRender
{

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $count = $this->db->table('metas')->where('type', 'tag')
            ->where('count', 0)->delete(true);

        $this->request->session()->flash('success', '已清理 ' . $count . '个标签');

        back();

        return true;
    }
}