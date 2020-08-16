<?php
/**
 * 一个处理post请求的专用入口文件
 * Created by tarblog.
 * Date: 2020/7/2
 * Time: 16:33
 *
 * @var \Core\Http\Request $request
 */

require "init.php";

$action = 'App\\' . str_replace('/', '\\', $request->input('a'));

if (!class_exists($action)) showErrorPage("ACTION NOT FOUND", 404);

(new $action($app))->execute(); // 由于不需要渲染，所以不提供后面的参数了
