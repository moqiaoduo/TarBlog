<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 11:48
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Comment;
use Helper\Common;
use Models\Post;
use Utils\Auth;
use Utils\DB;
use Utils\URLGenerator;

require "init.php";

Common::setTitle('评论');
if ($search = $request->get('s'))
    Common::setDescription('“' . $search . '”的搜索结果');

Auth::check('comment');

$isAdmin = Auth::check('post-premium', false);

$allComment = $request->get('allComment', 'off');

$types = ['approved' => "已通过", "pending" => "待审核", "spam" => "垃圾"];

$data = Comment::paginate(['status' => $status = $request->get('status', 'approved'),
    'showAll' => $showAll = $isAdmin && ($allComment === 'on'),
    'search' => $search, 'cid' => $cid = $request->get('cid'),
    'page' => $request->get('page', 1)
]);

function generate_select_link($id, $status)
{
    global $allComment;
    $types = ['approved' => "通过", "pending" => "待审核", "spam" => "垃圾"];
    foreach ($types as $key => $type) {
        if ($key === $status) echo $type . "\n";
        else echo '<a href="do.php?a=Admin/Comment/Status&id=' . $id . '&status=' . $key .
            '&allComment=' . $allComment . '">' . $type . '</a> ';
    }
}

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
<style>
    .comment-avatar {
        height: 100%;
        float: left;
        margin: 10px;
        width: 40px;
    }

    .comment-author {
        float: left;
        width: 150px;
        margin: 10px;
        word-break: break-all;
        word-wrap: break-word;
    }

    textarea {
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
    }
</style>
<div class="box no-margin-bottom">
    <form class="box-header with-border flex justify-between mobile-no-flex" method="get">
        <?php if (!empty($uid)): ?>
            <input type="hidden" name="uid" value="<?php echo $uid ?>">
        <?php endif; ?>
        <input type="hidden" name="allComment" value="<?php echo $allComment ?>">
        <div>
            <div class="btn-group">
                <button class="btn btn-sm icon-btn" type="button" id="comment-remove" title="删除"
                        onclick="return confirm('确定要删除所选评论吗？其所有回复也会一并删除。') || event.stopImmediatePropagation()">
                    <i class="czs-trash"></i>
                </button>
            </div>
            <select class="form-control tool-select" name="status" onchange="submit()">
                <?php foreach ($types as $value => $type): ?>
                    <option value="<?php echo $value ?>"<?php if ($value == $status) echo ' selected' ?>>
                        <?php echo $type . '(' . Comment::count(['status' => $value] +
                        compact('search', 'showAll', 'cid')) . ')' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="btn-group">
                <?php if ($status !== 'approved'): ?>
                <button class="btn btn-success btn-sm" type="button" id="comment-approve">
                    设为通过
                </button>
                <?php endif;if ($status !== 'pending'): ?>
                <button class="btn btn-info btn-sm" type="button" id="comment-pending">
                    设为待审核
                </button>
                <?php endif;if ($status !== 'spam'): ?>
                <button class="btn btn-warning btn-sm" type="button" id="comment-spam">
                    设为垃圾
                </button>
                <?php endif ?>
            </div>
        </div>
        <div>
            <?php if (Auth::check('post-premium', false)): ?>
            <div class="btn-group">
                <a href="?<?php echo URLGenerator::array2query(['allComment' => 'on', 's' => $search, 'status' => $status]); ?>"
                   class="btn btn-sm<?php if ($allComment == 'on') echo ' active' ?>">所有</a>
                <a href="?<?php echo URLGenerator::array2query(['allComment' => 'off', 's' => $search, 'status' => $status]); ?>"
                   class="btn btn-sm<?php if ($allComment == 'off') echo ' active' ?>">我的</a>
            </div>
            <?php endif ?>
            <div class="search">
                <input type="text" name="s" placeholder="搜索评论" value="<?php echo $search ?>">
                <button type="submit">
                    <i class="czs-search-l"></i>
                </button>
            </div>
        </div>
    </form>

    <form class="box-body table-responsive no-padding" method="post" action="do.php" id="list-form">
        <input type="hidden" name="a" id="list-action">
        <table class="table table-hover">
            <thead>
            <tr>
                <th width="10"><input type="checkbox" id="select-all" class="form-checkbox"></th>
                <th width="250">作者</th>
                <th>内容</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $val): ?>
                <tr data-id="<?php echo $id = $val['id'] ?>">
                    <td>
                        <input type="checkbox" class="form-checkbox item-checkbox" name="ids[]"
                               value="<?php echo $id ?>">
                    </td>
                    <td>
                        <div class="comment-avatar">
                            <img class="avatar"
                                 src="https://secure.gravatar.com/avatar/<?php echo md5($val['email']) ?>?s=40"
                                 alt="<?php echo md5($val['name']) ?>" width="40" height="40">
                        </div>
                        <div class="comment-author">
                            <label class="author">
                                <?php if ($url = $val['url']): ?>
                                    <a href="<?php echo $url ?>" target="_blank"><?php echo $val['name'] ?></a>
                                <?php else: ?>
                                    <?php echo $val['name'] ?>
                                <?php endif ?>
                            </label><br>
                            <span class="email"><?php echo $val['email'] ?></span><br>
                            <span class="ip"><?php echo $val['ip'] ?></span>
                        </div>
                    </td>
                    <td><p><?php echo $val['created_at'] ? dateX(2, $val['created_at']) : '' ?>
                            于 <?php
                            $article = DB::table('contents')->where('cid', $val['cid'])
                                ->whereNull('deleted_at')->first();
                            if (is_null($article)) {
                                echo "已删除的文章";
                            } else {
                                echo '<a href="' . route($article['type'], \Utils\Route::fillPostParams(
                                        new Post($article))) . '" target="_blank">' . $article['title'] . '</a>';
                            }
                            ?></p>
                        <p class="comment-content"><?php echo $val['content'] ?></p>
                        <p>
                            <?php generate_select_link($id, $status); ?>
                            <a href="javascript:;" data-function="edit">编辑</a>
                            <a href="javascript:;" data-function="reply">回复</a>
                        </p>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </form>

    <div class="box-footer flex justify-between">
        <div>共<?php echo $data->getTotal() ?>条</div>
        <div>
            <?php $data->view(['custom_tpl' => 'var/App/Admin/pagination.php']); ?>
        </div>
    </div>
</div>

<?php
Common::addJSFile('assets/js/comment.js');
Common::selectAllJS();
Common::buttonPostJS('Admin/Comment/Delete', 'comment-remove');
Common::buttonPostJS('Admin/Comment/ApproveBatch', 'comment-approve');
Common::buttonPostJS('Admin/Comment/PendBatch', 'comment-pending');
Common::buttonPostJS('Admin/Comment/BanBatch', 'comment-spam');
include "footer.php" ?>
