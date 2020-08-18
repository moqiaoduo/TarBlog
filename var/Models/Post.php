<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 16:45
 */

namespace Models;

use Utils\DB;

class Post extends Content
{
    /**
     * 获取文章第一个绑定的分类
     *
     * @return Category|null
     */
    public function getFirstCategory()
    {
        $ids = DB::table('relationships')->where('cid', $this->cid)
            ->select('mid')->pluck('mid');

        if (empty($ids)) return null;

        foreach ($ids as $mid) {
            $cate = DB::table('metas')->where('type', 'category')
                ->where('mid', $mid)->firstWithModel(Category::class);

            if (!is_null($cate)) return $cate;
        }
        return null;
    }

    public function getMeta($type = '', $params = [])
    {
        $model = $params['model'] ?? false;
        $pluck = $params['pluck'] ?? null;

        $ids = DB::table('relationships')->where('cid', $this->cid)
            ->select('mid')->pluck('mid');

        if (empty($ids)) return [];

        $query = DB::table('metas')->when($type, function ($query) use ($type) {
            $query->where('type', $type);
        })->whereIn('mid', $ids);

        if (is_string($pluck)) $data = $query->pluck($pluck);
        elseif (is_array($pluck)) $data = $query->pluck($pluck[0], $pluck[1]);
        else $data = $query->get();

        if (!$model) return $data;

        $models = [];

        foreach ($data as $item)
            $models[] = new Category($item);

        return $models;
    }

    public function getCategories($params = [])
    {
        if ($params['model']) $params['model'] = Category::class;

        return $this->getMeta('category', $params);
    }

    public function getTags($params = [])
    {
        if ($params['model']) $params['model'] = Tag::class;

        return $this->getMeta('tag', $params);
    }

    public function prev()
    {
        return DB::table('contents')->where('type', 'post')->whereNull('deleted_at')
            ->where('created_at' , '<', $this->created_at)->whereIn('status', ['publish', 'password'])
            ->orderBy('created_at', 'desc')->firstWithModel(static::class);
    }

    public function next()
    {
        return DB::table('contents')->where('type', 'post')->whereNull('deleted_at')
            ->where('created_at' , '>', $this->created_at)->whereIn('status', ['publish', 'password'])
            ->orderBy('created_at', 'asc')->firstWithModel(static::class);
    }
}