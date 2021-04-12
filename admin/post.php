<?php
/**
 * Created by TarBlog.
 * Date: 2018/9/5
 * Time: 18:50
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Helper\Content;
use Models\Post;
use Utils\Auth;
use Utils\URLGenerator;

require "init.php";

Common::setTitle('文章');
Common::setDescription(($search = $request->get('s')) ? '“' . $search . '”的搜索结果' : '列表');

Auth::check('post-base');

$isAdmin = Auth::check('post-premium', false);

$allPost = $request->get('allPost', 'off');

if ($isAdmin) $uid = $request->get('uid');
else $uid = null;

$category_id = $request->get('category_id');

$tag_id = $request->get('tag_id');

$data = Content::getPosts(['status' => $post_status = $request->get('post_status'),
        'page' => $request->get('page', 1), 'showAll' => $showAll = $isAdmin && ($allPost === 'on')]
    + compact('search', 'uid', 'tag_id', 'category_id'));

$types = ['publish' => "已发布", "draft" => "草稿", "waiting" => "待审核", "trash" => "回收站"];

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
    <div class="box no-margin-bottom">
        <form class="box-header with-border flex justify-between mobile-no-flex" method="get">
            <?php if (!empty($uid)): ?>
                <input type="hidden" name="uid" value="<?php echo $uid ?>">
            <?php endif; ?>
            <input type="hidden" name="allPost" value="<?php echo $allPost ?>">
            <div>
                <div class="btn-group">
                    <button class="btn btn-sm icon-btn" type="button" id="post-remove"
                            title="<?php echo $post_status == 'trash' ? "永久删除": "移至回收站" ?>"
                            <?php if ($post_status == 'trash')
                                echo 'onclick="return confirm(\'您确定永久删除选中的文章吗？\') || ' .
                                    'event.stopImmediatePropagation()"' ?>>
                        <i class="czs-trash"></i>
                    </button>
                    <?php if ($post_status == 'trash'): ?>
                        <button type="button" class="btn btn-sm icon-btn" id="post-restore"
                                title="还原"><i class="czs-renew"></i></button>
                    <?php endif ?>
                </div>
                <a class="btn btn-sm btn-success" href="./write-post.php">写文章</a>
                <select class="form-control tool-select" name="post_status" onchange="submit()">
                    <?php if ($category_id): ?>
                        <option disabled selected>*分类*</option>
                    <?php endif ?>
                    <?php if ($tag_id): ?>
                        <option disabled selected>*标签*</option>
                    <?php endif ?>
                    <option value="">全部文章(<?php Content::getStatusCount(['type' => 'post', 'return' => false]
                            + compact('search', 'showAll', 'uid')) ?>)
                    </option>
                    <?php foreach ($types as $value => $type):
                        if (empty($num = Content::getStatusCount(['type' => 'post', 'status' => $value]
                            + compact('search', 'showAll', 'uid')))) {
                            if ($post_status == $value) redirect('post.php' . URLGenerator::array2query(compact(
                                    'category_id', 'tag_id', 'allPost') + ['s' => $search], '?'));
                            continue;
                        } ?>
                        <option value="<?php echo $value ?>"<?php if ($value == $post_status) echo ' selected' ?>>
                            <?php echo $type . '(' . $num . ')' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <?php if (Auth::check('post-premium', false)): ?>
                <div class="btn-group">
                    <a href="?<?php echo URLGenerator::array2query(compact('category_id', 'post_status',
                            'tag_id') + ['allPost' => 'on', 's' => $search]); ?>"
                       class="btn btn-sm<?php if ($allPost == 'on') echo ' active' ?>">所有</a>
                    <a href="?<?php echo URLGenerator::array2query(compact('category_id', 'post_status',
                            'tag_id') + ['allPost' => 'off', 's' => $search]); ?>"
                       class="btn btn-sm<?php if ($allPost == 'off') echo ' active' ?>">我的</a>
                </div>
                <?php endif ?>
                <div class="search">
                    <input type="text" name="s" placeholder="搜索文章" value="<?php echo $search ?>">
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
                    <th>标题</th>
                    <th>作者</th>
                    <th>分类</th>
                    <th>标签</th>
                    <th width="50">评论</th>
                    <th width="80">日期</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $val): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-checkbox item-checkbox" name="ids[]"
                                   value="<?php echo $val['cid'] ?>">
                        </td>
                        <td>
                            <?php $title = $val['title'];
                            $draft_tip = '';
                            if ($post_status == 'trash'):
                                echo $title;
                            else:
                                if (in_array($val['cid'], Content::getDraftIds())) {
                                    if ($draft = Content::getContentById('post_draft', $val['cid']))
                                        $title = $draft['title'];
                                    $draft_tip = '<i>草稿</i>';
                                } ?>
                                <a href="write-post.php?edit=<?php echo $val['cid'] ?>">
                                    <?php echo $title ?>
                                </a> <?php echo $draft_tip ?>
                            <?php endif ?>
                        </td>
                        <td><?php echo ($author = ($model = new Post($val))->author()) ?
                                ($author['name'] ?: $author['username']) : '已删除的用户' ?>
                        </td>
                        <td>
                            <?php foreach ($categories = $model->getCategories() as $category): ?>
                                <a href="?category_id=<?php echo $category['mid'];
                                if ($search) echo "&s=$search" ?>">
                                    <?php echo $category['name'] ?>
                                </a>
                                <?php if ($category != end($categories)) echo "、";endforeach; ?>
                        </td>
                        <td>
                            <?php foreach ($tags = $model->getTags() as $t): ?>
                                <a href="?tag_id=<?php echo $t['mid'] ?>"><?php echo $t['name'] ?></a>
                                <?php if ($t != end($tags)) echo "、";endforeach; ?>
                        </td>
                        <td>
                            <?php if ($cc = $val['commentsNum']): ?>
                                <a href="comments.php?cid=<?php echo $val['cid'] ?>"><?php echo $cc ?></a>
                            <?php else: echo $cc;endif ?>
                        </td>
                        <td><?php if ($val['status'] == 'waiting') echo '待审核'; else echo '已发布' ?><br>
                            <abbr title="<?php echo $val['created_at'] ?>">
                                <?php echo dateX(2, $val['created_at']) ?></abbr>
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
Common::selectAllJS();
Common::buttonPostJS('Admin/Post/' . ($post_status == 'trash' ? 'Destroy' : 'Trash'), 'post-remove');
Common::buttonPostJS('Admin/Post/Restore', 'post-restore');
include "footer.php";
