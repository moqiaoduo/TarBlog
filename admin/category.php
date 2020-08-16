<?php
/**
 * Created by TarBlog.
 * Date: 2018/9/5
 * Time: 18:51
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

Common::setTitle('文章分类');

Auth::check('category');

function getCategories($select = -1, $parent = 0, $deep = 0)
{
    $categories = DB::table('metas')->where('type', 'category')
        ->where('parent', $parent)->get();
    $html = '';
    foreach ($categories as $category) {
        $html .= '<option value="' . $category['mid'] . '"' . ($select == $category['mid'] ? ' selected' : '') . '>';
        for ($i = 0; $i < $deep; $i++) $html .= '__';
        $html .= $category['name'];
        $html .= "</option>";
        $html .= getCategories($select, $category['mid'], $deep + 1);
    }
    return $html;
}

if ($mid = $request->get('edit')) {
    $add_title = "编辑分类";
    $category = DB::table('metas')->where('type', 'category')->where('mid', $mid)->first();
    if (is_null($category)) showErrorPage('您欲编辑的分类不存在', 404);
    $name = $category['name'];
    $slug = $category['slug'];
    $parent = $category['parent'];
    $description = $category['description'];
} else {
    $add_title = "添加新分类";
    $name = '';
    $slug = '';
    $parent = $request->get('parent', 0);
    $description = '';
}

$data = DB::table('metas')->where('type', 'category')->where('parent', $parent)
    ->when($search = $request->get('s'), function ($query) use ($search) {
        $query->where('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%");
    }, true)->orderByDesc('mid')->paginate($request->get('page', 1), 10);

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
    <link rel="stylesheet" href="assets/css/category.css">
<div class="category">
    <div class="form">
        <form method="post" class="form-container" action="do.php?a=Admin/Category/Save">
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
                    分类别名用于创建友好的链接形式, 建议使用字母, 数字, 下划线和横杠.
                </div>
            </div>
            <div class="form-group">
                <div class="form-inline">
                    <label class="form-label" for="parent">父级分类</label>
                    <select name="parent" id="parent" class="form-control">
                        <option value="0">不选择</option>
                        <?php echo getCategories($parent) ?>
                    </select>
                </div>
                <div class="form-description">
                    此分类将归档在您选择的父级分类下.
                </div>
            </div>
            <div class="form-group">
                <div class="form-inline">
                    <label class="form-label" for="description">分类描述</label>
                    <textarea name="description" class="form-control" rows="5" id="description"
                    ><?php echo htmlspecialchars($description) ?></textarea>
                </div>
                <div class="form-description">
                    此文字用于描述分类, 在有的主题中它会被显示.
                </div>
            </div>
            <div class="form-submit-group" style="text-align: right;">
                <?php if ($mid): ?>
                    <input type="hidden" name="id" value="<?php echo $mid ?>">
                    <a href="?parent=<?php echo $parent ?>" class="btn">取消</a>
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
                                onclick="return confirm('确定要删除所选分类吗？') || event.stopImmediatePropagation()">
                            <i class="czs-trash"></i>
                        </button>
                    </div>
                    <a href="do.php?a=Admin/Category/Sync" class="btn btn-sm btn-success">同步计数</a>
                    <?php if ($parent): ?>
                        <a style="margin-left: 10px;" href="?parent=<?php echo DB::table('metas')->where('mid', $parent)
                            ->first()['parent'] ?>">« 返回父级分类</a>
                    <?php endif ?>
                    <?php if ($search): ?>
                        <b style="margin-left: 10px;">“<?php echo $search ?>”的搜索结果</b>
                    <?php endif ?>
                </div>
                <div>
                    <div class="search">
                        <input type="text" name="s" placeholder="搜索分类" value="<?php echo $search ?>">
                        <button type="submit"><i class="czs-search-l"></i></button>
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
                        <th>子分类</th>
                        <th>描述</th>
                        <th></th>
                        <th>别名</th>
                        <th>总数</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $default = $options->get('defaultCategory', 1);
                    foreach ($data as $val): ?>
                        <tr>
                            <td><?php if ($default == $val['mid']):
                                    $defaultName = $val['name'];
                                else:
                                    ?>
                                    <input type="checkbox" class="form-checkbox item-checkbox" name="ids[]"
                                           value="<?php echo $val['mid'] ?>">
                                <?php endif ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $val['mid'] ?>"><?php echo $val['name'] ?></a>
                            </td>
                            <td>
                                <?php $subCate = DB::table('metas')->where('parent', $val['mid'])
                                    ->where('type', 'category')->count();
                                if ($subCate) echo '<a href="?parent=' . $val['mid'] . '">' . $subCate . '个分类</a>';
                                else echo '无';
                                ?>
                            </td>
                            <td><?php echo $val['description'] ?></td>
                            <td>
                                <?php if ($default == $val['mid']):echo "默认"; else: ?>
                                    <a href="?a=Admin/Category/SetDefault&default=<?php echo $val['mid'] ?>">设为默认</a>
                                <?php endif ?>
                            </td>
                            <td><?php echo $val['slug'] ?></td>
                            <td>
                                <a href="post.php?category_id=<?php echo $val['id'] ?>">
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
        <p style="margin-top: 10px;">注：<br>
            删除一个分类不会删除分类中的文章。然而，仅属于被删除分类的文章将被归档至 <b>
                <?php echo $defaultName ?? DB::table('metas')
                        ->where('mid', $default)->first()['name'] ?></b> 分类。<br>
            若被删除的分类下有子分类，则会将其子分类移至与被删除分类同级目录。
        </p>
    </div>
</div>
<?php
Common::selectAllJS();
Common::buttonPostJS('Admin/Category/Delete', 'category-remove');
include "footer.php";
