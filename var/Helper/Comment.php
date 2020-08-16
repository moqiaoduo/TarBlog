<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 17:09
 */

namespace Helper;

use Core\Options;
use Models\Page;
use Models\Post;
use Models\User;
use Utils\Auth;
use Utils\DB;

class Comment
{
    /**
     * 接受参数：status,showAll,search,cid,page
     *
     * @param array $params
     * @return \Core\Paginator
     */
    public static function paginate($params = [])
    {
        return self::query($params)->orderByDesc('created_at')->paginate($params['page']);
    }

    /**
     * 接受参数：status,showAll,search,cid,return
     *
     * @param array $params
     * @return int|void
     */
    public static function count($params = [])
    {
        $count = self::query($params)->count();

        if ($params['return'] ?? true)
            return $count;

        echo $count;
    }

    private static function query($params)
    {
        return DB::table('comments')->where('status', $params['status'])
            ->when(!$params['showAll'], function ($query) {
                $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
            }, true)->when($search = $params['search'], function ($query) use ($search) {
                $query->where('content', 'like', "%$search%");
            })->when($cid = $params['cid'], function ($query) use ($cid) {
                $query->where('cid', $cid);
            });
    }

    /**
     * 判断评论是否发送过快
     *
     * 功能说明
     * 0.需要开启该功能
     * 1.管理员和文章所有者不受此限制
     * 2.已登录用户用用户本身最后发送时间判断
     * 3.未登录用户用ip最后发送时间来判断
     *
     * @param User $user
     * @param Page|Post $article
     * @param Options $options
     * @return bool 为true时，说明发送频率太快了，要阻止
     */
    public static function checkTooFast($user, $article, $options)
    {
        if (!$options->commentsPostIntervalEnable) return false; // 未开启该功能时不继续判断

        $limit = dateX(0, '-' . $options->commentsPostInterval . ' minute');

        $query = DB::table('comments');

        if ($user) {
            if ($user->id == $article->uid || $user->isAdmin()) return false; // 管理员和文章拥有者不受限

            $query->where('authorId', $user->id);
        } else {
            $query->where('ip', get_ip());
        }

        return $query->where('created_at', '>', $limit)->exists();
    }

    public static function checkNeedPending($user, $article, $options, $author, $mail)
    {
        if ($user && ($user->id == $article->uid || $user->isAdmin())) return false;

        if ($options->commentsRequireModeration) return true;

        return $options->commentsWhitelist && !DB::table('comments')->where('name', $author)
                ->where('email', $mail)->where('status', 'approved')->exists();
    }
}