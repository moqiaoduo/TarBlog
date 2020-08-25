<?php
/**
 * Created by tarblog.
 * Date: 2020/5/27
 * Time: 1:31
 */

namespace App\Archive;

use App\Archive;

class Index extends Archive
{
    public function execute() : bool
    {
        if ($search = $this->search) {
            $this->type = 'search';
            $this->_archiveTitle = $search;
        }

        $this->paginator = $this->db->table('contents')->whereNull('deleted_at')
            ->where('type', 'post')->when($search, function ($query) use ($search) { // 只搜索标题和正文
                $query->where('title', 'like', "%$search%")->orWhere('content', 'like', "%$search%");
            }, true)->whereIn('status', ['publish', 'password']) // 只有已发布和加密状态的能出现在列表
            ->orderByDesc('created_at')->paginate($this->request->get('page', 1),
                $this->options->get('pageSize', 10));

        $this->queue = $this->paginator->getData();

        return true;
    }
}