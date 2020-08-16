<?php
/**
 * Created by tarblog.
 * Date: 2020/5/27
 * Time: 1:32
 */

namespace App\Archive;

use App\Archive;
use Models\Category as Model;

class Category extends Archive
{
    protected $type = 'category';

    public function execute() : bool
    {
        if (isset($this->routeParams['directory'])) {
            $directories = $this->routeParams['directory'];
            $last_cate = null;
            $cate = null;
            foreach ($directories as $directory) {
                $cate = $this->db->table('metas')->where('type', 'category')
                    ->where('slug', $directory)->first();
                if (is_null($cate)) return false;
                if (!is_null($last_cate) && $cate['parent'] != $last_cate['mid']) return false;
                $last_cate = $cate;
            }
            if (is_null($cate)) return false;
            $category = new Model($cate);
        } elseif (isset($this->routeParams['slug'])) {
            $category = $this->db->table('metas')->where('type', 'category')
                ->where('slug', $this->routeParams['slug'])->firstWithModel(Model::class);
            if (is_null($category)) return false;
        } elseif (isset($this->routeParams['mid'])) {
            $category = $this->db->table('metas')->where('type', 'category')
                ->where('mid', $this->routeParams['mid'])->firstWithModel(Model::class);
            if (is_null($category)) return false;
        } else {
            return false;
        }

        $this->paginator = $category->getPostsPaginate($this->request->get('page', 1));

        $this->queue = $this->paginator->getData();

        $this->_archiveTitle = $category->name;

        return true;
    }
}