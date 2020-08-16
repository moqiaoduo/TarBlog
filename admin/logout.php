<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/13
 * Time: 20:11
 */

if (!defined('__ROOT_DIR__') && !@include_once '../config.inc.php') {
    file_exists('./install.php') ? header('Location: install.php') : print('Missing Config File');
    exit;
}

\Utils\Auth::logout();

to_homepage();