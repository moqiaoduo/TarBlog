<?php
/**
 * Created by TarBlog.
 * Date: 2018/8/23
 * Time: 23:18
 *
 * @var \Core\Options $options
 * @var \Core\Plugin\Manager $plugin
 */

use Helper\Common;
use Utils\Auth;

if (!Auth::hasLogin()) redirect(__ADMIN_DIR__ . 'login.php');
?>
<!DOCTYPE html>
<html lang="zh-Hans">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php $options->title() ?> | <?php Common::title(); ?></title>
        <link rel="stylesheet" href="assets/css/app.css" />
        <link rel="stylesheet" href="assets/plugins/strawberry/style.css" />
    </head>
<body>
    <div class="top-bar">
        <div class="logo">TarBlog 后台</div>
        <div class="menu-collapse">
            <button><i class="czs-menu-l"></i></button>
        </div>
        <ul class="nav left-nav">
            <li class="nav-item"><a target="_blank" href="<?php $options->siteUrl() ?>">回到前台</a></li>
        </ul>
        <ul class="nav right-nav">
            <li class="nav-item">
                <a href="javascript:;">
                    <img src="https://secure.gravatar.com/avatar/<?php echo md5(Auth::user()->email) ?>?s=30"
                         class="avatar">
                    <?php echo Auth::user()->name ?: Auth::user()->username ?>
                </a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./user-editor.php">修改信息</a></li>
                        <li class="nav-item"><a href="./logout.php">登出</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    <div class="left-side">
        <ul class="nav">
        <?php if (Auth::check('dashboard', false)): ?>
            <li class="nav-item"><a href="./">仪表盘</a></li>
        <?php endif;
        if (Auth::check('post-base', false)): ?>
            <li class="nav-item">
                <a href="./post.php">文章</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./post.php">所有文章</a></li>
                        <li class="nav-item"><a href="./write-post.php">写文章</a></li>
                    <?php if (Auth::check('category', false)): ?>
                        <li class="nav-item"><a href="./category.php">文章分类</a></li>
                    <?php endif;
                    if (Auth::check('tag', false)): ?>
                        <li class="nav-item"><a href="./tag.php">标签</a></li>
                    <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif;
        if (Auth::check('page', false)): ?>
            <li class="nav-item">
                <a href="./page.php">页面</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./page.php">全部页面</a></li>
                        <li class="nav-item"><a href="./write-page.php">新建页面</a></li>
                    </ul>
                </div>
            </li>
        <?php endif;
        if (Auth::check('attachment', false)): ?>
            <li class="nav-item"><a href="attachment.php">附件</a></li>
        <?php endif;
        if (Auth::check('comment', false)): ?>
            <li class="nav-item"><a href="./comments.php">评论</a></li>
        <?php endif;
        if (Auth::check('admin-level', false)): ?>
            <li class="nav-item">
                <a href="./theme.php">主题</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./theme.php">所有主题</a></li>
                        <li class="nav-item"><a href="./add-theme.php">安装主题</a></li>
                        <li class="nav-item"><a href="./theme-editor.php">编辑</a></li>
                    <?php if (file_exists(__ROOT_DIR__ . __THEME_DIR__ . '/' .
                        $options->get('theme', 'default') . '/setting.php')): ?>
                        <li class="nav-item"><a href="./theme.php?page=settings">设置</a></li>
                    <?php endif ?>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="./plugin.php">插件</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./plugin.php">已安装的插件</a></li>
                        <li class="nav-item"><a href="./add-plugin.php">安装插件</a></li>
                    <?php if (\Utils\Dir::hasDirs(__PLUGIN_DIR__)): ?>
                        <li class="nav-item"><a href="./plugin-editor.php">编辑</a></li>
                    <?php endif ?>
                    </ul>
                </div>
            </li>
        <?php endif;
        if (Auth::user()->isAdmin()): ?>
            <li class="nav-item">
                <a href="./user.php">用户</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./user.php">所有用户</a></li>
                        <li class="nav-item"><a href="./add-user.php">添加用户</a></li>
                        <li class="nav-item"><a href="./user-editor.php">我的个人资料</a></li>
                    </ul>
                </div>
            </li>
        <?php else: ?>
            <li class="nav-item"><a href="./user-editor.php">我的个人资料</a></li>
        <?php endif;
        if (Auth::check('admin-level', false)): ?>
            <li class="nav-item">
                <a href="./tool.php">工具</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./tool.php">可用工具</a></li>
                        <li class="nav-item"><a href="./tool.php?p=import">导入</a></li>
                        <li class="nav-item"><a href="./tool.php?p=export">导出</a></li>
                    <!-- hook -->
                    <?php foreach ($plugin->tool() as $tool):
                        if (isset($tool['menu']) && $tool['menu']):?>
                            <li class="nav-item">
                                <a href="./tool.php?p=<?php echo $tool['p'] ?>">
                                    <?php echo $tool['name'] ?>
                                </a>
                            </li>
                        <?php endif;endforeach; ?>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="./setting.php?p=general">设置</a>
                <div class="expand-nav">
                    <ul class="nav">
                        <li class="nav-item"><a href="./setting.php?p=general">常规</a></li>
                        <li class="nav-item"><a href="./setting.php?p=reading">阅读</a></li>
                        <li class="nav-item"><a href="./setting.php?p=comment">评论</a></li>
                        <li class="nav-item"><a href="./setting.php?p=url">固定链接</a></li>
                        <li class="nav-item"><a href="./setting.php?p=html_purifier">HTML Purifier</a></li>
                    <!-- hook -->
                    <?php foreach ($plugin->setting() as $setting):
                        if (isset($setting['show']) && $setting['show']):?>
                            <li class="nav-item">
                                <a href="./setting.php?p=<?php echo $setting['p'] ?>">
                                    <?php echo $setting['name'] ?>
                                </a>
                            </li>
                        <?php endif;endforeach; ?>
                    </ul>
                </div>
            </li>
            <li class="nav-item"><a href="./upgrade.php">升级程序</a></li>
        <?php endif ?>
        </ul>
    </div>

<div class="main">
    <div class="container">
    <div class="title">
        <h1>
            <?php Common::title(); ?>
            <small><?php Common::description(); ?></small>
        </h1>
    </div>
