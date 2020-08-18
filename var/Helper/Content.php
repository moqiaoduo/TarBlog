<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 16:18
 */

namespace Helper;

use Core\Paginator;
use Core\Plugin\Manager as PluginManager;
use Models\Attachment;
use Models\Page;
use Models\Post;
use Models\Tag;
use Utils\Auth;
use Utils\DB;
use Utils\Route;

class Content
{
    /**
     * 缓存而已
     *
     * @var array
     */
    private static $draft_ids;

    /**
     * 获取内容
     * $params接收参数：
     * type,page,search,status,category_id,tag_id,showAll,uid
     * 支持文章、页面、附件
     *
     * @param array $params
     *
     * @return Paginator
     */
    public static function getContents($params = [])
    {
        $search = ($params['search'] ?? null);

        if ($params['status'] == 'trash') {
            $content = DB::table('contents')->whereNotNull('deleted_at');
        } elseif ($params['status'] == 'draft') {
            $content = DB::table('contents')->whereIn('cid', self::getDraftIds($params));
        } else {
            if (!empty($params['category_id'])) {
                $meta = DB::table('metas')->where('type', 'category')
                    ->where('mid', $params['category_id'])->first();
                $r = DB::table('relationships')->where('mid', $meta['mid'])->pluck('cid');
                if (empty($r)) return new Paginator();
                $content = DB::table('contents')->whereIn('cid', $r);
            } elseif (!empty($params['tag_id'])) {
                $meta = DB::table('metas')->where('type', 'tag')
                    ->where('mid', $params['tag_id'])->firstWithModel(Tag::class);
                $r = DB::table('relationships')->where('mid', $meta['mid'])->pluck('cid');
                if (empty($r)) return new Paginator();
                $content = DB::table('contents')->whereIn('cid', $r);
            } else {
                $content = DB::table('contents');
            }
            $content->when(!empty($params['status']), function ($query) use ($params) {
                $query->where('status', $params['status']);
            })->whereNull('deleted_at');
        }

        $type = $params['type'];

        $uid = $params['uid'] ?? null;

        return $content->when($search, function ($query) use ($type, $search) {
            $query->where('title', 'like', "%$search%");
            if ($type != 'attachment') $query->orWhere('content', 'like', "%$search%");
        }, true)->where(function ($query) use ($type) {
            if ($type == 'attachment') $query->where('type', $type);
            else $query->where('type', $type)->orWhere('type', $type . '_draft')->where('parent', 0);
        })->when(!$uid && (!$params['showAll'] ?? false), function ($query) {
            $query->where('uid', Auth::id());
        })->when($uid, function ($query) use ($uid) {
            $query->where('uid', $uid);
        })->orderByDesc('created_at')->paginate($params['page']);
    }

    public static function getPosts($params = [])
    {
        $params['type'] = 'post';

        return self::getContents($params);
    }

    public static function getPages($params = [])
    {
        $params['type'] = 'page';

        return self::getContents($params);
    }

    public static function getAttachments($params = [])
    {
        $params['type'] = 'attachment';

        return self::getContents($params);
    }

    public static function getAttachmentsByParent($parent)
    {
        return DB::table('contents')->where('type', 'attachment')
            ->whereNull('deleted_at')->where('parent', $parent)->get();
    }

    public static function getAttachmentById($id, $model = false)
    {
        return self::getContentById('attachment', $id, $model ? Attachment::class : null);
    }

    public static function getContentById($type, $id, $model = null)
    {
        $query = DB::table('contents')->where('cid', $id)->where('type', $type)
            ->whereNull('deleted_at');

        if ($model)
            return $query->firstWithModel($model);

        return $query->first();
    }

    public static function getDraftByParent($type, $parent)
    {
        return DB::table('contents')->where('type', $type . '_draft')
            ->whereNull('deleted_at')->where('parent', $parent)->first();
    }

    /**
     * 通过id查询文章
     *
     * @param $id
     * @return Post|null
     */
    public static function getPostById($id)
    {
        return DB::table('contents')->where('cid', $id)->where(function ($query) {
            $query->where('type', 'post')->orWhere('type', 'post_draft')->where('parent', 0);
        })->whereNull('deleted_at')->firstWithModel(Post::class);
    }

    /**
     * 通过id查询文章
     *
     * @param $id
     * @return Page|null
     */
    public static function getPageById($id)
    {
        return DB::table('contents')->where('cid', $id)->where(function ($query) {
            $query->where('type', 'page')->orWhere('type', 'page_draft')->where('parent', 0);
        })->whereNull('deleted_at')->firstWithModel(Page::class);
    }

    public static function getPostDraft($id)
    {
        return self::getDraftByParent('post', $id);
    }

    public static function getPageDraft($id)
    {
        return self::getDraftByParent('page', $id);
    }

    /**
     * 支持参数：
     * type,search,showAll,uid
     *
     * @param array $params
     * @return array
     */
    public static function getDraftIds($params = [])
    {
        if (!is_null(self::$draft_ids)) {
            return self::$draft_ids;
        }

        // 没有发布过的文章的draft的id
        $only_draft = self::draftQuery($params)->where('parent', 0)->pluck('cid');

        // 文章所保存的draft的parent
        $save_draft = self::draftQuery($params)->where('parent', '>', 0)->pluck('parent');

        return self::$draft_ids = array_merge($only_draft, $save_draft);
    }

    /**
     * 支持参数：
     * type,search,status,showAll,uid
     *
     * @param array $params
     * @return mixed|void
     */
    public static function getStatusCount($params = [])
    {
        switch ($status = $params['status'] ?? null) {
            case 'trash':
                $content = DB::table('contents')->whereNotNull('deleted_at');
                break;
            case 'draft':
                return count(self::getDraftIds($params));
            default:
                $content = DB::table('contents')->when($status,function ($query) use ($status) {
                    $query->where('status', $status);
                })->whereNull('deleted_at');
        }

        $uid = $params['uid'] ?? null;

        $count = $content->where(function ($query) use ($params) {
            $type = $params['type'];
            if ($type == 'attachment') $query->where('type', $type);
            else $query->where('type', $type)->orWhere('type', $type . '_draft')->where('parent', 0);
        })->when($search = ($params['search'] ?? null), function ($query) use ($search) {
            $query->where('title', 'like', "%$search%")->orWhere('content', 'like', "%$search%");
        }, true)->when(!$uid && (!$params['showAll'] ?? false), function ($query) {
            $query->where('uid', Auth::id());
        })->when($uid, function ($query) use ($uid) {
            $query->where('uid', $uid);
        })->count();

        if ($params['return'] ?? true)
            return $count;

        echo $count;
    }

    private static function draftQuery($params = [])
    {
        $type = $params['type'];

        $search = ($params['search'] ?? null);

        $showAll = ($params['showAll'] ?? false);

        $uid = ($params['uid'] ?? null); // 先说明一下，只有post-premium及以上权限才能激活这个选项，其他人永远都是没有的

        return DB::table('contents')->where('type', $type . '_draft')
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%$search%")->orWhere('content', 'like', "%$search%");
            }, true)->when(!$showAll, function ($query) {
                $query->where('uid', Auth::id());
            })->when($uid, function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
    }

    public static function saveContent(\Models\Content $content, $update = false)
    {
        return DB::saveWithModel('contents', $content, 'cid', $update);
    }

    /**
     * @param array $p
     * @param string $type
     * @param PluginManager $plugin
     * @param \Models\Content $model
     * @return string
     */
    public static function slug($p, $type, $plugin, $model)
    {
        if (empty($p['slug'])) {
            $result = $plugin->trigger($plugged)->generate_slug_article($model);

            if ($plugged) {
                $slug = $result[0];
            } else {
                if ($type == 'post')
                    $slug = $model->cid;
                else
                    $slug = $model->title;
            }
        } else {
            $slug = $p['slug'];
        }

        return generate_unique_slug($slug, $type, $model->cid);
    }

    public static function link2P($id, $parent)
    {
        $query = DB::table('contents')->where('type', 'attachment')
            ->whereNull('deleted_at')->where('uid', Auth::id());

        if (is_array($id))
            $query->whereIn('cid', $id);
        else
            $query->where('cid', $id);

        $query->update(['parent' => $parent]);
    }

    public static function getTagsId($cid, $list)
    {
        if (empty($list)) return [];

        $tags = [];

        foreach ($list as $tag) {
            $data = DB::table('metas')->where('name', $tag)->first();
            if (is_null($data)) {
                DB::table('metas')->insert(['name' => $tag, 'type' => 'tag',
                    'slug' => generate_unique_slug($tag, 'tag')]);
                $tags[] = DB::lastInsertId();
            } else {
                $tags[] = $data['mid'];
            }
        }

        return $tags;
    }
}