<?php
/**
 * Created by tarblog.
 * Date: 2020/5/25
 * Time: 0:17
 */

$config_example = <<<'PHP'
<?php
/**
 * TarBlog Config
 */

// 根目录
use Core\Database\Manager as Database;

define('__ROOT_DIR__', dirname(__FILE__));

// 插件目录(相对路径)
define('__PLUGIN_DIR__', '/usr/plugin');

// 模板目录(相对路径)
define('__THEME_DIR__', '/usr/theme');

// 后台路径(相对路径)
define('__ADMIN_DIR__', '/admin/');

// 调试模式
define('__DEBUG__', true);

// 错误页面显示详情
define('__SHOW_ERROR__', false);

require_once __ROOT_DIR__ . "/var/bootstrapper.php";

// 数据库设置
$db = new Database([
    'host' => '{{db_host}}',
    'user' => '{{db_user}}',
    'password' => '{{db_pass}}',
    'charset' => 'utf8',
    'port' => '{{db_port}}',
    'database' => '{{db_name}}',
]);
$db->init();
$app->bidingInstance('db', $db);

PHP;
