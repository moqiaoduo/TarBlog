<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 12:05
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Helper\Content;
use Models\Page;
use Utils\Auth;
use Utils\URLGenerator;

require "init.php";

Common::setTitle('页面');
Common::setDescription(($search = $request->get('s')) ? '“' . $search . '”的搜索结果' : '列表');

Auth::check('page');

$data = Content::getPages(['status' => $page_status = $request->get('page_status'),
    'page' => $request->get('page', 1), 'search' => $search]);

$types = ['publish' => "已发布", "draft" => "草稿", "trash" => "回收站"];

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
<div class="box no-margin-bottom">
    <form class="box-header with-border flex justify-between mobile-no-flex" method="get">
        <?php if (!empty($uid)): ?>
            <input type="hidden" name="uid" value="<?php echo $uid ?>">
        <?php endif; ?>
        <div>
            <div class="btn-group">
                <button class="btn btn-sm icon-btn" type="button" id="page-remove"
                        title="<?php echo $page_status == 'trash' ? "永久删除" : "移至回收站" ?>"
                    <?php if ($page_status == 'trash')
                        echo 'onclick="return confirm(\'您确定永久删除选中的页面吗？\') || ' .
                            'event.stopImmediatePropagation()"' ?>>
                    <i class="czs-trash"></i>
                </button>
                <?php if ($page_status == 'trash'): ?>
                    <button type="button" class="btn btn-sm icon-btn" id="page-restore"
                            title="还原"><i class="czs-renew"></i></button>
                <?php endif ?>
            </div>
            <a class="btn btn-sm btn-success" href="./write-page.php">新建页面</a>
            <select class="form-control tool-select" name="page_status" onchange="submit()">
                <option value="">
                    全部页面(<?php Content::getStatusCount(['type' => 'page', 'return' => false, 'search' => $search]) ?>)
                </option>
                <?php foreach ($types as $value => $type):
                    if (empty($num = Content::getStatusCount(['type' => 'page', 'status' => $value,
                        'search' => $search]))) {
                        if ($page_status == $value) redirect('page.php' . URLGenerator::array2query(
                                ['search' => $search], '?'));
                        continue;
                    } ?>
                    <option value="<?php echo $value ?>"<?php if ($value == $page_status) echo ' selected' ?>>
                        <?php echo $type . '(' . $num . ')' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
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
                <th width="50">评论</th>
                <th width="110">日期</th>
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
                        if ($page_status == 'trash'):
                            echo $title;
                        else:
                            if (in_array($val['cid'], Content::getDraftIds())) {
                                if ($draft = Content::getContentById('page_draft', $val['cid']))
                                    $title = $draft['title'];
                                $draft_tip = '<i>草稿</i>';
                            } ?>
                            <a href="write-page.php?edit=<?php echo $val['cid'] ?>">
                                <?php echo $title ?>
                            </a> <?php echo $draft_tip ?>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php echo ($author = ($model = new Page($val))->author()) ?
                            ($author['name'] ?: $author['username']) : '已删除的用户' ?>
                    </td>
                    <td>
                        <?php if ($cc = $val['commentsNum']): ?>
                            <a href="comments.php?cid=<?php echo $val['cid'] ?>"><?php echo $cc ?></a>
                        <?php else: echo $cc;endif ?>
                    </td>
                    <td>
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
Common::buttonPostJS('Admin/Page/' . ($page_status == 'trash' ? 'Destroy' : 'Trash'), 'page-remove');
Common::buttonPostJS('Admin/Page/Restore', 'page-restore');
include "footer.php";
