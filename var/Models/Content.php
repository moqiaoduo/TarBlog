<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 18:42
 */

namespace Models;

use Core\Database\Model;
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
    public function getTopLevelCommentPaginate($page, $pageSize)
    {
        return DB::table('comments')->where('cid', $this->cid)->where('parent', 0)
            ->orderBy('created_at', get_option('commentsOrder', 'DESC'))
            ->paginate($page, $pageSize, 'cp');
    }
}