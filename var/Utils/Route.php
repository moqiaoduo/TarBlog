<?php
/**
 * Created by tarblog.
 * Date: 2020/5/30
 * Time: 0:15
 */

namespace Utils;

use Core\Facade;
use Models\Category;
use Models\Post;

/**
 * @method static \Core\Routing\Route addRoute(\Core\Routing\Route $route)
 * @method static \Core\Routing\Route add($uri, $action)
 * @method static \Core\Routing\Route|null getRouteByName($name)
 */
class Route extends Facade
{
    const YEAR_PATTERN = '^\d{4,4}$';
    const MONTH_DAY_PATTERN = '^\d{1,2}$';

    /**
     * @inheritDoc
     */
    protected static function getFacadeInstanceAlias()
    {
        return 'router';
    }

    public static function fillPostParams(Post $post)
    {
        $data = [];

        foreach (Route::getRouteByName('post')->getParamsFromPatternUri() as $param) {
            switch ($param) {
                case 'cid':
                    $data['cid'] = $post->cid;
                    break;
                case 'slug':
                    $data['slug'] = $post->slug;
                    break;
                case 'category':
                    $data['category'] = $post->getFirstCategory()->slug;
                    break;
                case 'directory':
                    $data['directory'] = self::getCategoryDirectory($post->getFirstCategory());
                    break;
                case 'year':
                    $data['year'] = date('Y', $post->created_at);
                    break;
                case 'month':
                    $data['month'] = date('m', $post->created_at);
                    break;
                case 'day':
                    $data['day'] = date('d', $post->created_at);
                    break;
            }
        }

        return $data;
    }

    public static function fillPageParams($content)
    {
        $data = [];

        foreach (Route::getRouteByName('page')->getParamsFromPatternUri() as $param) {
            switch ($param) {
                case 'cid':
                    $data['cid'] = $content['cid'];
                    break;
                case 'slug':
                    $data['slug'] = $content['slug'];
                    break;
            }
        }

        return $data;
    }

    public static function getCategoryDirectory(?Category $category)
    {
        $uri = '';

        $parents = [];

        $current = $category;

        while ($current && $current->parent > 0) {
            $parent_category = DB::table('metas')->where('type', 'category')
                ->where('mid', $current->parent)->firstWithModel(Category::class);
            if (is_null($parent_category)) break;
            $parents[] = $current = $parent_category->slug;
        }

        foreach ($parents as $parent) {
            $uri .= $parent . '/';
        }

        $uri .= $category->slug;

        return $uri;
    }

    public static function fillCategoryParams(Category $category)
    {
        $data = [];

        foreach (Route::getRouteByName('category')->getParamsFromPatternUri() as $param) {
            switch ($param) {
                case 'mid':
                    $data['cid'] = $category->mid;
                    break;
                case 'slug':
                    $data['slug'] = $category->slug;
                    break;
                case 'directory':
                    $data['directory'] = self::getCategoryDirectory($category);
                    break;
            }
        }

        return $data;
    }

    public static function verifyDirectory($directories)
    {
        $last_cate = null;
        $cate = null;
        foreach (Arr::wrap($directories) as $directory) {
            $cate = DB::table('metas')->where('type', 'category')
                ->where('slug', $directory)->first();
            if (is_null($cate)) return false;
            if (!is_null($last_cate) && $cate['parent'] != $last_cate['mid']) return false;
            $last_cate = $cate;
        }
        if (is_null($cate)) return false;

        return new Category($cate);
    }
}