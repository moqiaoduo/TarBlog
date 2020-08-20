<?php
/**
 * Created by tarblog.
 * Date: 2020/5/25
 * Time: 0:17
 *
 * @var \Core\Container\Manager $app
 * @var \Core\Http\Request $request
 * @var \Core\Http\Session $session
 * @var \Core\Options $options
 */

use Core\Database\Manager as Database;
use Utils\DB;

$config_example = <<<'PHP'
<?php
/**
 * TarBlog Config
 */

use Core\Database\Manager as Database;

// 根目录
define('__ROOT_DIR__', dirname(__FILE__));

// 插件目录(相对路径)
define('__PLUGIN_DIR__', '/usr/plugin');

// 模板目录(相对路径)
define('__THEME_DIR__', '/usr/theme');

// 后台路径(相对路径)
define('__ADMIN_DIR__', '/admin/');

// 调试模式
define('__DEBUG__', false);

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

date_default_timezone_set($app->make('options')->get('timezone', 'Asia/Shanghai'));

PHP;

define('__ROOT_DIR__', dirname(__FILE__));

define('__DEBUG__', false); // 这个是调试用的，一般人请不要打开

define('__SHOW_ERROR__', false); // 同上

require_once __ROOT_DIR__ . "/var/bootstrapper.php";

$session = $app->make('session');

if (!__DEBUG__ && !$session->get('install_finished') && file_exists('config.inc.php')) { // 已安装就别来捣乱了
    header('Location: index.php');
    exit;
}

$request = $app->make('request');

if ($request->isMethod('post')) {
    switch ($request->get('a')) {
        case 'db':
            saveData('db', 'host', $host = $request->post('host') ?: '127.0.0.1');
            saveData('db', 'username', $user = $request->post('username') ?: 'root');
            saveData('db', 'password', $password = $request->post('password'));
            saveData('db', 'port', $port = $request->post('port') ?: 3306);
            saveData('db', 'name', $name = $request->post('name') ?: 'tarblog');
            try {
                $db = new Database([
                    'host' => $host,
                    'user' => $user,
                    'password' => $password,
                    'charset' => 'utf8',
                    'port' => $port,
                    'database' => $name,
                ]);
                $db->init();
            } catch (Exception $e) {
                back(with_error($e->getMessage()));
            }
            redirect('install.php?step=4');
            break;
        case 'admin':
            saveData('admin');
            redirect('install.php?step=5');
            break;
        case 'site':
            saveData('site');
            redirect('install.php?step=6');
            break;
        case 'install':
            try {
                // 开始安装
                $install = $session->get('install');
                if (empty($install)) redirect('install.php');
                $db_params = ['{{db_host}}' => $host = $install['db']['host'],
                    '{{db_user}}' => $username = $install['db']['username'],
                    '{{db_pass}}' => $password = $install['db']['password'],
                    '{{db_port}}' => $port = $install['db']['port'],
                    '{{db_name}}' => $dbname = $install['db']['name']];
                $db = new Database([
                    'host' => $host,
                    'user' => $username,
                    'password' => $password,
                    'charset' => 'utf8',
                    'port' => $port,
                    'database' => $dbname,
                ]);
                $db->init();
                $app->bidingInstance('db', $db);

                // 创建表
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `authorId` int(11) NOT NULL DEFAULT '0',
  `ownerId` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `content` longtext,
  `status` varchar(255) NOT NULL DEFAULT 'approved',
  `parent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `comments_created_at_index` (`created_at`) USING BTREE,
  KEY `comments_cid_index` (`cid`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `contents` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `content` longtext,
  `order` int(10) unsigned DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `template` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'post',
  `status` varchar(255) NOT NULL DEFAULT 'publish',
  `password` varchar(255) DEFAULT NULL,
  `commentsNum` int(10) unsigned NOT NULL DEFAULT '0',
  `allowComment` tinyint(1) NOT NULL DEFAULT '1',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`) USING BTREE,
  UNIQUE KEY `contents_slug_unique` (`slug`) USING BTREE,
  KEY `contents_created_at_index` (`created_at`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `fields` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(8) DEFAULT 'str',
  `str_value` text,
  `int_value` int(11) DEFAULT NULL,
  `float_value` float DEFAULT NULL,
  PRIMARY KEY (`cid`,`name`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `metas` (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`mid`) USING BTREE,
  KEY `metas_slug_index` (`slug`) USING BTREE,
  KEY `metas_created_at_index` (`created_at`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `options` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `value` longtext,
  PRIMARY KEY (`name`,`user`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `relationships` (
  `cid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cid`,`mid`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `identity` varchar(255) NOT NULL DEFAULT 'reader',
  `auth_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `users_username_unique` (`username`) USING BTREE,
  UNIQUE KEY `users_email_unique` (`email`) USING BTREE
) DEFAULT CHARACTER SET = utf8;
SQL
                );
                // 写入基本选项
                $options = $app->make('options');
                $options->set('siteName', $install['site']['siteName']);
                $options->set('siteUrl', $install['site']['siteUrl']);
                $options->set('allowFileExt', serialize(['png', 'jpg', 'jpeg', 'gif']));
                $options->set('theme', 'default');

                $options->set('commentsShowUrl', '1');
                $options->set('commentsUrlNofollow', '1');
                $options->set('commentsAvatar', '1');
                $options->set('commentsAvatarRating', 'G');
                $options->set('commentsPageBreak', '1');
                $options->set('commentsPageSize', '10');
                $options->set('commentsPageDisplay', 'first');
                $options->set('commentsThreaded', '1');
                $options->set('commentsOrder', 'DESC');

                $options->set('commentsRequireModeration', '1');
                $options->set('commentsWhitelist', '0');
                $options->set('commentsRequireMail', '1');
                $options->set('commentsRequireURL', '0');
                $options->set('commentsCheckReferer', '1');
                $options->set('commentsPostIntervalEnable', '0');
                $options->set('commentsPostInterval', '3');

                $options->set('defaultCategory', '1');
                $options->set('pageSize', '10');

                $options->set('postUrl', '/archives/{cid}');
                $options->set('pageUrl', '/{slug}');
                $options->set('categoryUrl', '/{slug}');
                $options->set('showArticleList', 1);
                $options->set('articleListUrl', '/article');

                $options->set('version', \Core\Upgrade::$newest_version); // 这个必须写入，不然到时候会被判断为要更新

                DB::table('contents')->insert([
                    ['title' => '您的第一篇博文', 'content' => '<p>该博文由系统自动生成。</p>',
                        "slug" => "the-first-post", "uid" => 1] + auto_fill_time(),
                    ["title" => "关于", "content" => "该博客由TarBlog驱动。", "slug" => "about",
                        "uid" => 1, 'type' => "page"] + auto_fill_time()
                ], true);

                DB::table('metas')->insert(['name' => '默认分类', 'slug' => 'default', 'type' => 'category',
                    'description' => '系统自动生成的文章分类', 'count' => 1]);

                DB::table('relationships')->insert(['cid' => 1, 'mid' => 1]);

                \Utils\Auth::register($install['admin']['username'], $install['admin']['password'],
                    $install['admin']['email'], ['identity' => 'admin']);

                // 写入配置
                file_put_contents(__ROOT_DIR__ . DIRECTORY_SEPARATOR . 'config.inc.php',
                    str_replace(array_keys($db_params), array_values($db_params), $config_example));

                // 显示结束提示
                $session->flash('install_finished', 1);
                redirect('install.php?step=7');
            } catch (Throwable $t) {
                $content = '<p>出现了不可预知的错误！</p><p>' . $t->getMessage() . '</p>';
                $action = 'err';
                goto start;
            }
            break;
        default:
            back();
    }
}

$notOK = false;

$defaultUrl = ($request->isHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];

function passOrNot($condition)
{
    global $notOK;
    echo $condition ? 'OK' : 'ERROR';
    if (!$condition) $notOK = true;
}

function saveData($page, $key = null, $val = null)
{
    global $session;
    if (is_null($key)) {
        foreach ($_POST as $k => $v) {
            saveData($page, $k, $v);
        }
    } else {
        $install = $session->get('install', []);
        $install[$page][$key] = $val;
        $session->set('install', $install);
    }
}

function showSaveData($page, $key, $default = null)
{
    global $session;
    $install = $session->get('install', []);
    echo $install[$page][$key] ?? $default;
}

$step = $request->get('step', 1);

$errors = new \Core\Errors($session->get('errors'));

switch ($step):
    case 1:
        ob_start();
        ?>
        <p>Welcome To TarBlog</p>
        <p>程序版本：<?php echo __VERSION__ ?></p>
        <p>友情提醒：TarBlog 遵循 Apache License 2.0 协议。</p>
        <?php
        $content = ob_get_contents();
        $action = null;
        ob_clean();
        break;
    case 2:
        ob_start();
        ?>
        <p>现在，让我们检查一下网站环境是否符合要求……</p>
        <table class="table">
            <thead>
            <tr>
                <th>项目</th>
                <th>要求</th>
                <th>环境</th>
                <th>是否通过</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>PHP版本</td>
                <td>&gt;=7.1</td>
                <td><?php echo PHP_VERSION ?></td>
                <td><?php passOrNot(PHP_VERSION >= '7.1') ?></td>
            </tr>
            <tr>
                <td>PDO扩展</td>
                <td>支持</td>
                <td><?php echo class_exists('PDO') ? '支持' : '不支持' ?></td>
                <td><?php passOrNot(class_exists('PDO')) ?></td>
            </tr>
            <tr>
                <td>mb_string扩展</td>
                <td>支持</td>
                <td><?php echo function_exists('mb_strlen') ? '支持' : '不支持' ?></td>
                <td><?php passOrNot(function_exists('mb_strlen')) ?></td>
            </tr>
            <tr>
                <td>目录权限(/)</td>
                <td>0755</td>
                <td><?php echo $root_auth = substr(sprintf("%o", fileperms(__ROOT_DIR__)), -4); ?></td>
                <td><?php passOrNot($root_auth >= 0755) ?></td>
            </tr>
            <tr>
                <td>目录权限(/usr)</td>
                <td>0755</td>
                <td><?php echo $usr_auth = substr(sprintf("%o", fileperms(__ROOT_DIR__ . '/usr')), -4); ?></td>
                <td><?php passOrNot($usr_auth >= 0755) ?></td>
            </tr>
            </tbody>
        </table>
        <?php
        $content = ob_get_contents();
        $action = null;
        ob_clean();
        break;
    case 3:
        ob_start();
        ?>
        <?php if ($err = $errors->first()): ?>
        <p style="color: red;font-weight: bold;">
            无法连接数据库：<?php echo $err ?>
        </p>
    <?php endif ?>
        <p>请输入数据库信息：</p>
        <style>
            td:first-child {
                text-align: right;
            }

            td:last-child {
                padding-left: 20px;
            }
        </style>
        <table>
            <tr>
                <td><label for="host">数据库地址</label></td>
                <td><input type="text" name="host" id="host" class="input" placeholder="127.0.0.1"
                           value="<?php showSaveData('db', 'host') ?>"></td>
            </tr>
            <tr>
                <td><label for="port">数据库端口</label></td>
                <td><input type="text" name="port" id="port" class="input" placeholder="3306"
                           value="<?php showSaveData('db', 'port') ?>"></td>
            </tr>
            <tr>
                <td><label for="username">数据库用户名</label></td>
                <td><input type="text" name="username" id="username" class="input" placeholder="root"
                           value="<?php showSaveData('db', 'username') ?>"></td>
            </tr>
            <tr>
                <td><label for="password">数据库密码</label></td>
                <td><input type="password" name="password" id="password" class="input"
                           value="<?php showSaveData('db', 'password') ?>"></td>
            </tr>
            <tr>
                <td><label for="name">数据库名称</label></td>
                <td><input type="text" name="name" id="name" class="input" placeholder="tarblog"
                           value="<?php showSaveData('db', 'name') ?>"></td>
            </tr>
        </table>
        <?php
        $content = ob_get_contents();
        $action = 'db';
        ob_clean();
        break;
    case 4:
        ob_start();
        ?>
        <style>
            td:first-child {
                text-align: right;
            }

            td:last-child {
                padding-left: 20px;
            }
        </style>
        <p>请输入管理员信息：</p>
        <table>
            <tr>
                <td><label for="username">管理员用户名</label></td>
                <td><input type="text" name="username" id="username" class="input" required
                           value="<?php showSaveData('admin', 'username') ?>"></td>
            </tr>
            <tr>
                <td><label for="password">管理员密码</label></td>
                <td><input type="password" name="password" id="password" class="input" required
                           value="<?php showSaveData('admin', 'password') ?>"></td>
            </tr>
            <tr>
                <td><label for="email">管理员邮箱</label></td>
                <td><input type="email" name="email" id="email" class="input" required
                           value="<?php showSaveData('admin', 'email') ?>"></td>
            </tr>
        </table>
        <?php
        $content = ob_get_contents();
        $action = 'admin';
        ob_clean();
        break;
    case 5:
        ob_start();
        ?>
        <style>
            td:first-child {
                text-align: right;
            }

            td:last-child {
                padding-left: 20px;
            }
        </style>
        <p>请输入网站信息：</p>
        <table>
            <tr>
                <td><label for="siteName">网站名称</label></td>
                <td><input type="text" name="siteName" id="siteName" class="input" required
                           value="<?php showSaveData('site', 'siteName', 'TarBlog') ?>"></td>
            </tr>
            <tr>
                <td><label for="siteUrl">网站URL</label></td>
                <td><input type="url" name="siteUrl" id="siteUrl" class="input" required
                           value="<?php showSaveData('admin', 'siteUrl', $defaultUrl) ?>"></td>
            </tr>
        </table>
        <p>提示：若非识别错误，不建议更改网站URL，可能会导致网站无法正常访问</p>
        <?php
        $content = ob_get_contents();
        $action = 'site';
        ob_clean();
        break;
    case 6:
        ob_start();
        ?>
        <style>
            td:first-child {
                text-align: right;
            }

            td:last-child {
                padding-left: 20px;
            }
        </style>
        <p>确认您填写的信息是否准确无误：</p>
        <table>
            <tr>
                <td>数据库主机</td>
                <td><?php showSaveData('db', 'host') ?></td>
            </tr>
            <tr>
                <td>数据库端口</td>
                <td><?php showSaveData('db', 'port') ?></td>
            </tr>
            <tr>
                <td>数据库用户名</td>
                <td><?php showSaveData('db', 'username') ?></td>
            </tr>
            <tr>
                <td>数据库密码</td>
                <td><span id="db-pass">******</span> <a href="javascript:;" onclick="show_hide('db-pass')">显示</a></td>
            </tr>
            <tr>
                <td>数据库名</td>
                <td><?php showSaveData('db', 'name') ?></td>
            </tr>
            <tr>
                <td>管理员用户名</td>
                <td><?php showSaveData('admin', 'username') ?></td>
            </tr>
            <tr>
                <td>管理员密码</td>
                <td><span id="admin-pass">******</span> <a href="javascript:;" onclick="show_hide('admin-pass')">显示</a>
                </td>
            </tr>
            <tr>
                <td>管理员邮箱</td>
                <td><?php showSaveData('admin', 'email') ?></td>
            </tr>
            <tr>
                <td>网站名称</td>
                <td><?php showSaveData('site', 'siteName') ?></td>
            </tr>
            <tr>
                <td>网站URL</td>
                <td><?php showSaveData('site', 'siteUrl') ?></td>
            </tr>
        </table>
        <script>
            var admin_pass = "<?php showSaveData('admin', 'password') ?>";
            var db_pass = "<?php showSaveData('db', 'password') ?>";

            function show_hide(id) {
                var obj = document.getElementById(id);
                var e = window.event || arguments.callee.caller.arguments[0];

                if (obj.innerText === '******') {
                    if (id === 'admin-pass')
                        obj.innerText = admin_pass;
                    else if (id === 'db-pass')
                        obj.innerText = db_pass;
                    e.target.innerText = '隐藏';
                } else {
                    obj.innerText = '******';
                    e.target.innerText = '显示';
                }
            }
        </script>
        <?php
        $content = ob_get_contents();
        $action = 'install';
        ob_clean();
        break;
    case 7:
        ob_start();
        ?>
        <p>恭喜您！安装已经完成，点击下面的按钮，跳转到想要的界面吧！</p>
        <p>程序版本：<?php echo __VERSION__ ?></p>
        <p><a class="button" href="./">到首页</a> <a class="button" href="/admin/">到管理页</a></p>
        <?php
        $content = ob_get_contents();
        $action = 'install';
        ob_clean();
        $action = 'err';
        break;
    default:
        $content = '<p>在？搁这捣乱呢？</p>';
        $action = 'err';
endswitch;
start: ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <meta name="robots" content="noindex,follow">
    <title>安装 TarBlog</title>
    <style type="text/css">
        html {
            background: #f1f1f1;
        }

        body {
            background: #fff;
            color: #444;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 2em auto;
            padding: 1em 2em;
            max-width: 700px;
            -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
        }

        h1 {
            border-bottom: 1px solid #dadada;
            clear: both;
            color: #666;
            font-size: 24px;
            margin: 30px 0 0 0;
            padding: 0;
            padding-bottom: 7px;
        }

        ul li {
            margin-bottom: 10px;
            font-size: 14px;
        }

        a {
            color: #0073aa;
        }

        a:hover,
        a:active {
            color: #00a0d2;
        }

        a:focus {
            color: #124964;
            -webkit-box-shadow: 0 0 0 1px #5b9dd9,
            0 0 2px 1px rgba(30, 140, 190, .8);
            box-shadow: 0 0 0 1px #5b9dd9,
            0 0 2px 1px rgba(30, 140, 190, .8);
            outline: none;
        }

        .button {
            background: #f7f7f7;
            border: 1px solid #ccc;
            color: #555;
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 26px;
            height: 28px;
            margin: 0;
            padding: 0 10px 1px;
            cursor: pointer;
            -webkit-border-radius: 3px;
            -webkit-appearance: none;
            border-radius: 3px;
            white-space: nowrap;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;

            -webkit-box-shadow: 0 1px 0 #ccc;
            box-shadow: 0 1px 0 #ccc;
            vertical-align: top;
        }

        .button.button-large {
            height: 30px;
            line-height: 28px;
            padding: 0 12px 2px;
        }

        .button:hover,
        .button:focus {
            background: #fafafa;
            border-color: #999;
            color: #23282d;
        }

        .button:focus {
            border-color: #5b9dd9;
            -webkit-box-shadow: 0 0 3px rgba(0, 115, 170, .8);
            box-shadow: 0 0 3px rgba(0, 115, 170, .8);
            outline: none;
        }

        .button:active {
            background: #eee;
            border-color: #999;
            -webkit-box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);
            box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);
            -webkit-transform: translateY(1px);
            -ms-transform: translateY(1px);
            transform: translateY(1px);
        }

        .table {
            text-align: center;
            border-collapse: collapse;
            width: 100%;
        }

        .table td, .table th {
            border: 1px solid #999;
        }

        .input {
            border: 1px solid #ccc;
            color: #555;
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 26px;
            height: 28px;
            margin: 0;
            padding: 0 10px 1px;
            -webkit-border-radius: 3px;
            -webkit-appearance: none;
            border-radius: 3px;
            white-space: nowrap;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            vertical-align: top;
        }

        .input:hover,
        .input:focus {
            border-color: #999;
        }

        .input:focus {
            border-color: #5b9dd9;
            -webkit-box-shadow: 0 0 3px rgba(0, 115, 170, .8);
            box-shadow: 0 0 3px rgba(0, 115, 170, .8);
            outline: none;
        }

    </style>
</head>
<body>
<h1>TarBlog 安装程序 <small>第<?php echo $step ?>步</small></h1>
<form action="install.php?a=<?php echo $action ?>" method="post">
    <?php echo $content ?>
    <p>
        <?php if ($action != 'err'):
            if ($step > 1): ?>
                <a href="install.php?step=<?php echo $step - 1 ?>" class="button">上一步</a>
            <?php endif;

            if (!$notOK):
                if ($action): ?>
                    <button type="submit" class="button">
                        <?php echo $action == 'install' ? '开始安装' : '下一步' ?>
                    </button>
                <?php else: ?>
                    <a href="install.php?step=<?php echo $step + 1 ?>" class="button">下一步</a>
                <?php endif;
            endif;
        endif ?>
    </p>
</form>
</body>
</html>