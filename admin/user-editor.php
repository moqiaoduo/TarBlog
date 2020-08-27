<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/12
 * Time: 15:13
 *
 * @var \Core\Errors $errors
 * @var \Core\Http\Request $request
 * @var \Core\Http\Session $session
 */

use Helper\Common;
use Helper\User;
use Utils\Auth;
use Utils\DB;

require "init.php";

Common::setTitle('用户个人资料');

$uid = $request->get('id', Auth::id());
if ($uid <= 0) to_homepage(); // 为了防止反复横跳，所以跳转到首页算了
if ($uid != Auth::id()) Auth::check('admin-level');

$user = DB::table('users')->where('id', $uid)->first();

if (is_null($user)) showErrorPage('用户不存在', 404);

include "header.php";
Common::loadAdminSettingStyle(100);
User::loadCSS();
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
    <form method="post" class="form-container" action="do.php?a=Admin/User/Update">
        <input type="hidden" name="id" value="<?php echo $uid ?>">
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="username">用户名 *</label>
                <input type="text" id="username" name="username" required disabled
                       autocomplete="off" class="form-control" value="<?php echo $user['username']?>">
            </div>
            <div class="form-description">
                此用户名将作为用户登录时所用的名称.<br>
                该项无法修改.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="email">电子邮箱地址 *</label>
                <input type="email" id="email" name="email" required
                       autocomplete="off" class="form-control" value="<?php old('email', $user['email'])?>">
            </div>
            <div class="form-description">
                电子邮箱地址将作为此用户的主要联系方式.<br>
                请不要与系统中现有的电子邮箱地址重复.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="name">用户昵称</label>
                <input type="text" id="name" name="name" value="<?php old('name', $user['name'])?>"
                       autocomplete="off" class="form-control">
            </div>
            <div class="form-description">
                用户昵称可以与用户名不同, 用于前台显示.<br>
                如果你将此项留空, 将默认使用用户名.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="password">用户密码</label>
                <input type="password" id="password" name="password"
                       autocomplete="off" class="form-control">
            </div>
            <div class="form-description">
                为此用户分配一个密码.<br>
                建议使用特殊字符与字母、数字的混编样式,以增加系统安全性.<br>
                此处留空，则不修改用户的密码.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="confirm-password">用户密码确认</label>
                <input type="password" id="confirm-password" name="confirm_password"
                       autocomplete="off" class="form-control">
            </div>
            <div class="form-description">
                请确认你的密码, 与上面输入的密码保持一致.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="url">个人主页地址</label>
                <input type="url" id="url" name="url"
                       autocomplete="off" class="form-control" value="<?php old('url', $user['url'])?>">
            </div>
            <div class="form-description">
                用户的个人主页地址, 请用 http://(或https://) 开头.
            </div>
        </div>
        <?php if (Auth::check('admin-level', false) && $user['id'] != Auth::id()):
            // 非管理员以及自己不能修改 ?>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="identity">用户组</label>
                <?php Common::buildSelect(['reader' => '读者', 'poster' => '投稿者', 'writer' => '写手', 'admin' => '管理员',
                    'editor' => '编辑'], ['id' => 'identity', 'class' => 'form-control', 'name' => 'identity',
                    'value' => app('session')->get('inputs')['identity'] ?? $user['identity']]) ?>
            </div>
        </div>
        <?php endif ?>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="url">登出其他设备</label>
                <div style="height: 38px;padding-top: 4px;">
                    <a href="do.php?a=Admin/Logout&other=1" class="btn btn-sm">登出</a>
                </div>
            </div>
            <div class="form-description">
                点击按钮后，除当前登录设备外的设备将会被强制登出。
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">保存</button>
            <button type="reset" class="btn">重置</button>
        </div>
    </form>
<?php
User::loadJS(true);
include "footer.php"?>