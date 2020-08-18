<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 18:42
 */

namespace Models;

use App\Base;
use Core\Database\Model;
use Utils\Auth;
use Utils\DB;

/**
 * content表对应总模型，包括文章、页面、附件
 *
 * @property $cid
 * @property $title
 * @property $slug
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $content
 * @property $order
 * @property $uid
 * @property $template
 * @property $type
 * @property $status
 * @property $password 页面密码
 * @property $commentsNum 评论数
 * @property $allowComment 是否允许评论
 * @property $parent
 */
class Content extends Model
{
    public function author()
    {
        return DB::table('users')->where('id', $this->uid)->first();
    }

    public function getTopLevelCommentPaginate($page, $pageSize)
    {
        $query = DB::table('comments')->where('cid', $this->cid)->where('parent', 0)
            ->when(!(Auth::id() && Auth::user()->isAdmin()), function ($query) {
                $query->where('status', 'approved')->orWhere('status', 'pending')->when(Auth::id(), function ($query) {
                    $query->where('authorId', Auth::id())->orWhere('ownerId', Auth::id());
                }, true)->when(!Auth::id(), function ($query) {
                    $query->where('name', Base::remember('author', true))
                        ->where('email', Base::remember('mail', true)); // URL不参与判断
                });
            }, true)->orderBy('created_at', get_option('commentsOrder', 'DESC'));

        // 启用分页，才用paginate
        if (get_option('commentsPageBreak')) {
            return $query->paginate($page, $pageSize, 'cp');
        }

        return $query->get();
    }
}