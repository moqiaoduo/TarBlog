<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/16
 * Time: 19:32
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Utils\Auth;
use Utils\DB;

require "init.php";

Common::setTitle('标签');

Auth::check('tag');

if ($mid = $request->get('edit')) {
    $add_title = "编辑标签";
    $tag = DB::table('metas')->where('type', 'tag')->where('mid', $mid)->first();
    if (is_null($tag)) showErrorPage('您欲编辑的标签不存在', 404);
    $name = $tag['name'];
    $slug = $tag['slug'];
    $description = $tag['description'];
} else {
    $add_title = "添加新标签";
    $name = '';
    $slug = '';
    $description = '';
}

$data = DB::table('metas')->when($search = $request->get('s'), function ($query) use ($search) {
    $query->where('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%");
}, true)->where('type','tag')->orderByDesc('mid')
    ->paginate($request->get('page', 1), 10);

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
    <link rel="stylesheet" href="assets/css/category.css">
    <div class="category">
        <div class="form">
            <form method="post" class="form-container" action="do.php?a=Admin/Tag/Save">
                <h4><b><?php echo $add_title ?></b></h4>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="name">名称</label>
                        <input type="text" id="name" name="name" required value="<?php echo $name ?>"
                               autocomplete="off" class="form-control" placeholder="必填项">
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="slug">别名</label>
                        <input type="text" id="slug" name="slug" value="<?php echo $slug ?>"
                               autocomplete="off" class="form-control">
                    </div>
                    <div class="form-description">
                        标签别名用于创建友好的链接形式, 建议使用字母, 数字, 下划线和横杠.
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="description">分类描述</label>
                        <textarea name="description" class="form-control" rows="5" id="description"
                        ><?php echo htmlspecialchars($description) ?></textarea>
                    </div>
                    <div class="form-description">
                        此文字用于描述标签, 在有的主题中它会被显示.
                    </div>
                </div>
                <div class="form-submit-group" style="text-align: right;">
                    <?php if ($mid): ?>
                        <input type="hidden" name="id" value="<?php echo $mid ?>">
                        <a href="tag.php" class="btn">取消</a>
                    <?php endif ?>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
        <div class="list">
            <div class="box no-margin-bottom">
                <form class="box-header with-border flex justify-between mobile-no-flex" method="get">
                    <?php if (!empty($parent)): ?>
                        <input type="hidden" name="parent" value="<?php echo $parent ?>">
                    <?php endif ?>
                    <div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm" title="删除" id="category-remove"
                                    onclick="return confirm('确定要删除所选标签吗？') || event.stopImmediatePropagation()">
                                <i class="czs-trash"></i>
                            </button>
                        </div>
                        <a class="btn btn-sm btn-danger" href="do.php?a=Admin/Tag/Clean"
                           onclick="return confirm('这个操作将会删除无文章引用的标签，且无法复原，您确定吗？')">
                            清理空闲标签
                        </a>
                        <a href="do.php?a=Admin/Tag/Sync" class="btn btn-sm btn-success">同步计数</a>
                        <?php if ($search): ?>
                            <b style="margin-left: 10px;">“<?php echo $search ?>”的搜索结果</b>
                        <?php endif ?>
                    </div>
                    <div>
                        <div class="search">
                            <input type="text" name="s" placeholder="搜索标签" value="<?php echo $search ?>">
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
                            <th>名称</th>
                            <th>描述</th>
                            <th>别名</th>
                            <th>总数</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $default = $options->get('defaultCategory', 1);
                        foreach ($data as $val): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-checkbox item-checkbox" name="ids[]"
                                           value="<?php echo $val['mid'] ?>">
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $val['mid'] ?>"><?php echo $val['name'] ?></a>
                                </td>
                                <td><?php echo $val['description'] ?></td>
                                <td>
                                    <?php if ($default == $val['mid']):echo "默认"; else: ?>
                                        <a href="?a=default&default=<?php echo $val['mid'] ?>">设为默认</a>
                                    <?php endif ?>
                                </td>
                                <td><?php echo $val['slug'] ?></td>
                                <td>
                                    <a href="post.php?tag_id=<?php echo $val['id'] ?>">
                                        <?php echo $val['count'] ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach ?>
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
        </div>
    </div>
<?php
Common::selectAllJS();
Common::buttonPostJS('Admin/Category/Delete', 'category-remove');
include "footer.php";
