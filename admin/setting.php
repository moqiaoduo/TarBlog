<?php
/**
 * Created by TarBlog.
 * Date: 2019/4/10
 * Time: 23:17
 *
 * @var \Core\Http\Request $request
 * @var \Core\Plugin\Manager $plugin
 * @var \Core\Http\Session $session
 */

use Helper\Common;
use Utils\Auth;

require "init.php";

Common::setTitle('设置');

Auth::check('admin-level');

$page = $request->get('p', 'general');

foreach ($plugin->setting() as $setting) {
    if ($setting['p'] === $page) {
        Common::setTitle($setting['name']);
        include "header.php";
        Common::loadSuccessAlert($session->get('success'));
        goto footer;
    }
}

if (file_exists($file = __ROOT_DIR__ . __ADMIN_DIR__ . 'setting/' . $page . '.php')) {
    $trans = ["comment" => "评论设置", "general" => "常规设置", "reading" => "阅读设置", "url" => "固定链接设置"];
    Common::setTitle($trans[$page]);
    include "header.php";
    Common::loadSuccessAlert($session->get('success'));
    include $file;
    goto footer;
}

showErrorPage('设置页面不存在', 404);

footer:
include "footer.php";