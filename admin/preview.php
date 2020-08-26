<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/26
 * Time: 10:58
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Container\Manager $app
 */

require "init.php";

\Utils\Auth::check('post-base');

$theme = $options->get('theme', 'default');

$themeDir = __ROOT_DIR__ . __THEME_DIR__ . DIRECTORY_SEPARATOR . $theme;

$action = new \App\Admin\Preview($app, $theme, $themeDir);
$action->execute();
$action->render();
