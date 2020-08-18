<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/17
 * Time: 0:45
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Helper\Content;
use Models\Attachment;
use Utils\Auth;

require "init.php";

Common::setTitle('附件管理');
Common::setDescription(($search = $request->get('s')) ? '“' . $search . '”的搜索结果' : '');

Auth::check('attachment');

$data = Content::getAttachments(['page' => $request->get('page', 1), 'search' => $search]);

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
<div class="box no-margin-bottom">
    <form class="box-header with-border flex justify-between mobile-no-flex" method="get">
        <div>
            <div class="btn-group">
                <button class="btn btn-sm icon-btn" type="button" id="attach-remove" title="删除"
                        onclick="return confirm('确定要删除所选附件吗？') || event.stopImmediatePropagation()">
                    <i class="czs-trash"></i>
                </button>
            </div>
            <a class="btn btn-sm btn-danger" href="do.php?a=Admin/Attachment/Clean"
               onclick="return confirm('这个操作将会删除不在文章中的附件，且无法复原，您确定吗？')">清理未归档文件</a>
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
        <input type="hidden" name="batch" value="1">
        <table class="table table-hover">
            <thead>
            <tr>
                <th width="10"><input type="checkbox" id="select-all" class="form-checkbox"></th>
                <th>文件名</th>
                <th>上传者</th>
                <th>所属文章</th>
                <th width="110">上传日期</th>
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
                        <a href="media-editor.php?cid=<?php echo $val['cid'] ?>">
                            <?php echo $val['title'] ?>
                        </a>
                    </td>
                    <td><?php echo ($author = ($model = new Attachment($val))->author()) ?
                            ($author['name'] ?: $author['username']) : '已删除的用户' ?>
                    </td>
                    <td>
                        <?php
                        if (empty($val['parent'])) {
                            echo '无';
                        } else {
                            $post = \Utils\DB::table('contents')->whereNull('deleted_at')
                                ->where('cid', $val['parent'])->firstWithModel(\Models\Content::class);
                            if (is_null($post)) echo '已删除的文章';
                            else echo '<a href="write-' . $post->type . '.php?edit=' .
                                $post->cid . '">' . $post->title . '</a>';
                        }
                        ?>
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
Common::buttonPostJS('Admin/Attachment/Delete', 'attach-remove');
include "footer.php";
