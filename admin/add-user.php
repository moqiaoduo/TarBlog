<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/12
 * Time: 15:13
 *
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Helper\User;
use Utils\Auth;

require "init.php";

Common::setTitle('添加用户');

Auth::check('admin-level');

include "header.php";
Common::loadAdminSettingStyle(100);
User::loadCSS();
Common::loadErrorAlert($errors->first());
?>
    <form method="post" class="form-container" action="do.php?a=Admin/User/Create">
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="username">用户名 *</label>
                <input type="text" id="username" name="username" required
                       autocomplete="off" class="form-control" value="<?php old('username')?>">
            </div>
            <div class="form-description">
                此用户名将作为用户登录时所用的名称.<br>
                请不要与系统中现有的用户名重复.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="email">电子邮箱地址 *</label>
                <input type="email" id="email" name="email" required
                       autocomplete="off" class="form-control" value="<?php old('email')?>">
            </div>
            <div class="form-description">
                电子邮箱地址将作为此用户的主要联系方式.<br>
                请不要与系统中现有的电子邮箱地址重复.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="name">用户昵称</label>
                <input type="text" id="name" name="name" value="<?php old('name')?>"
                       autocomplete="off" class="form-control">
            </div>
            <div class="form-description">
                用户昵称可以与用户名不同, 用于前台显示.<br>
                如果你将此项留空, 将默认使用用户名.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="password">用户密码 *</label>
                <input type="password" id="password" name="password" required
                       autocomplete="off" class="form-control">
            </div>
            <div class="form-description">
                为此用户分配一个密码.<br>
                建议使用特殊字符与字母、数字的混编样式,以增加系统安全性.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="confirm-password">用户密码确认 *</label>
                <input type="password" id="confirm-password" name="confirm_password" required
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
                       autocomplete="off" class="form-control" value="<?php old('url')?>">
            </div>
            <div class="form-description">
                用户的个人主页地址, 请用 http://(或https://) 开头.
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="identity">用户组</label>
                <?php Common::buildSelect(['reader' => '读者', 'poster' => '投稿者', 'writer' => '写手',
                    'editor' => '编辑', 'admin' => '管理员'], ['id' => 'identity', 'name' => 'identity',
                    'class' => 'form-control', 'value' => app('session')->get('inputs')['identity'] ?? null]) ?>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">添加用户</button>
        </div>
    </form>
<?php
User::loadJS();
include "footer.php"?>