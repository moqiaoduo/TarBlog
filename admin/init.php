<?php
/**
 * Created by TarBlog.
 * Date: 2019/5/26
 * Time: 9:31
 *
 * @var \Core\Container\Manager $app
 */

if (!defined('__ROOT_DIR__') && !@include_once '../config.inc.php') {
    file_exists('./install.php') ? header('Location: install.php') : print('Missing Config File');
    exit;
}

$options = $app->make('options');

$request = $app->make('request');

$session = $app->make('session'); /* @var \Core\Http\Session $session */

$errors = new \Core\Errors($session->get('errors'));

$plugin = $app->make('plugin');

require __ROOT_DIR__ . '/var/routes.php';

$app->make('router')->refreshRoutesNameList();

ob_start();