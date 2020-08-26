<?php
/**
 * Created by TarBlog.
 * Date: 2018/8/23
 * Time: 20:54
 *
 * @var \Core\Container\Manager $app
 */

if (!defined('__ROOT_DIR__') && !@include_once 'config.inc.php') {
    file_exists('./install.php') ? header('Location: install.php') : print('Missing Config File');
    exit;
}

require __ROOT_DIR__ . '/var/routes.php';

$app->bidingInstance('plugin', new \Core\Plugin\Manager()); // 提前初始化插件，使插件可以参与定义路由

$app->make('router')->refreshRoutesNameList();

ob_start();

$app->make('router')->dispatch();