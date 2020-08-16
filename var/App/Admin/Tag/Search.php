<?php
/**
 * Created by tarblog.
 * Date: 2020/8/4
 * Time: 10:32
 */

namespace App\Admin\Tag;

use App\NoRender;
use Utils\DB;

class Search extends NoRender
{
    public function execute(): bool
    {
        json($this->db->table('metas')->where('type', 'tag')
            ->where('name', 'like', '%' . $this->request->get('search') . '%')
            ->pluck('name'));

        return true;
    }
}