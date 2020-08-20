<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/12
 * Time: 15:13
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

Common::setTitle('所有用户');
Common::setDescription(($search = $request->get('s')) ? '“' . $search . '”的搜索结果' : '列表');

Auth::check('admin-level');

$data = DB::table('users')->when($search,function ($query) use ($search){
    $query->where('username','like',"%$search%")
        ->orWhere('email','like',"%$search%")
        ->orWhere('name','like',"%$search%");
}, true)->orderByDesc('created_at')->orderByDesc('id')
    ->paginate($request->get('page',1));

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
    <div class="alert alert-info">
        原则上，需要留有至少一个管理员账号，否则网站功能会有所缺失<br>
        虽然删除时并不会对管理员账号数量进行判断，但是删除账号需要管理也权限，而且无法删除当前登录的账号，因此理论上不会删除最后一个管理员账号
    </div>
    <div class="box no-margin-bottom">
        <form class="box-header with-border flex justify-between mobile-no-flex" method="get">
            <div>
                <div class="btn-group">
                    <button class="btn btn-sm icon-btn" type="button" id="user-remove" title="删除用户"
                            onclick="return confirm('确定要删除所选用户吗？\n注：删除用户不会删除其他数据，包括文章、页面等。')
                            || event.stopImmediatePropagation()">
                        <i class="czs-trash"></i>
                    </button>
                </div>
                <a class="btn btn-sm btn-success" href="./add-user.php">添加用户</a>
            </div>
            <div>
                <div class="search">
                    <input type="text" name="s" placeholder="搜索用户" value="<?php echo $search ?>">
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
                    <th>用户名</th>
                    <th>昵称</th>
                    <th>电子邮件</th>
                    <th>用户组</th>
                    <th width="80">文章总数</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $val): ?>
                    <tr>
                        <td>
                            <?php if ($val['id'] != Auth::id()) : ?>
                            <input type="checkbox" class="form-checkbox item-checkbox" name="ids[]"
                                   value="<?php echo $val['id'] ?>">
                            <?php endif ?>
                        </td>
                        <td>
                            <a href="user-editor.php?id=<?php echo $val['id'] ?>">
                                <?php echo $val['username'] ?>
                            </a>
                        </td>
                        <td><?php echo $val['name']?></td>
                        <td><a href="mailto:<?php echo $val['email']?>"><?php echo $val['email']?></a></td>
                        <td><?php
                            switch ($val['identity']) {
                                case 'reader':
                                    echo '读者';
                                    break;
                                case 'poster':
                                    echo '投稿者';
                                    break;
                                case 'writer':
                                    echo '贡献者';
                                    break;
                                case 'editor':
                                    echo '编辑';
                                    break;
                                case 'admin':
                                    echo '管理员';
                                    break;
                            }
                            ?></td>
                        <td>
                            <a href="post.php<?php echo $val['id'] == Auth::id() ? '' : '?allPost=on&uid=' . $val['id'] ?>">
                                <?php echo (new \Models\User($val))->postCount()?>
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
<?php
Common::selectAllJS();
Common::buttonPostJS('Admin/User/Delete', 'user-remove');
include "footer.php"?>