<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 15:33
 */

if (defined('__BOOTED__')) exit();

require_once __DIR__ . "/helpers.php";

define('__BOOTED__', true);

define('__VERSION__', 'v0.3.0'); // 真正定义程序版本的地方

spl_autoload_register(function ($class) {
    if (substr($class, -6) == 'Plugin') {
        if (!class_exists('\Core\Plugin\Plugin'))
            include_once __ROOT_DIR__ . "/var/Core/Plugin/Plugin.php";

            $dir = \Utils\Str::toUnderline(substr($class, 0, strlen($class) - 6));

            if (file_exists($file = __ROOT_DIR__ . __PLUGIN_DIR__ . DIRECTORY_SEPARATOR . $dir . '/Plugin.php'))
                include_once $file;
    } elseif (file_exists($file = __ROOT_DIR__ . "/var/" .
        str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php')) {
        include_once $file;
    }
    // 考虑到插件可能不会存在，所以就不丢出错误
});

ini_set("display_errors", "Off");

set_error_handler(function ($code, $message, $file, $line) {
    if (!$code) return;

    $data = compact('code', 'message', 'file', 'line');

    switch ($code) {
        case E_ERROR:
        case E_USER_ERROR:
            $data['type'] = 'Fatal error';
            $data['color'] = '#CC3333';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $data['type'] = 'Warning';
            $data['color'] = '#FFCC00';
            break;
        default:
            $data['type'] = 'Error';
            $data['color'] = '#888888';
            break;
    }

    pageOrDebug($data);
}, E_ALL ^ E_NOTICE);

set_exception_handler(function (Throwable $e) {
    pageOrDebug(['code' => $e->getCode(),
        'type' => basename(get_class($e)),
        'message' => $e->getMessage(),
        'color' => '#000000',
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTrace()
    ]);
});

register_shutdown_function(function () {
    global $app;
    // 接下来处理一些脚本结束后的事情
    $app->make('session')->save();
    $app->make('options')->save();
});

$app = \Core\Container\Manager::getInstance();
$app->addContainerBinding('plugin', 'Core\Plugin\Manager');
$app->addContainerBinding('db', 'Core\Database\Manager');
$app->addContainerBinding('options', 'Core\Options');
$app->addContainerBinding('session', 'Core\Http\Session');
$app->addContainerBinding('request', 'Core\Http\Request');
$app->addContainerBinding('router', 'Core\Routing\Router');
$app->addContainerBinding('auth', 'Core\Auth');
$app->addContainerBinding('dir', 'Core\Dir');
$app->addContainerBinding('validate', 'Core\Validate');

/**
 * 显示错误页面或调试页面
 *
 * @param $data
 */
function pageOrDebug($data)
{
    ob_end_clean();
    ob_start();
    http_response_code(500);
    if (__DEBUG__) {
        $trace = $data['trace'];
        if (is_array($trace)) {
            $trace = '';
            foreach ($data['trace'] as $item) {
                $function = isset($item['class']) ? $item['class'] . $item['type'] . $item['function'] : $item['function'];
                $args = print_r($item['args'] ?? [], true);
                $trace .= <<<HTML
<div class="trace">
<span>File: {$item['file']}</span>
<span>Line: {$item['line']}</span>
<span>Call: {$function}</span>
<span>Args: <br>{$args}</span>
</div>
HTML;
            }
            $data['trace'] = $trace;
        }
        debug($data);
    } else {
        if (__SHOW_ERROR__)
            $text = <<<HTML
<span style="color: {$data['color']};font-weight: bold;">{$data['type']}: </span>
{$data['message']}</p>
<p>{$data['file']}: <b>{$data['line']}</b>
HTML;
        else
            $text = '应用程序出现错误，请联系网站管理员';
        showErrorPage($text);
    }
}

/**
 * 调试页面
 *
 * @param $data
 */
function debug($data)
{
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <title>应用程序出现错误</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width">
        <meta name="robots" content="noindex,follow">
        <style>
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

            #error-page {
                margin-top: 50px;
            }

            #error-page p {
                font-size: 14px;
                line-height: 1.5;
                margin: 25px 0 20px;
            }

            #error-page code {
                font-family: Consolas, Monaco, monospace;
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

            pre {
                word-break: break-word;
                word-wrap: break-word;
            }
        </style>
    </head>
    <body id="error-page">
    <p>
        <span style="color: <?php echo $data['color'] ?>;font-weight: bold;"><?php echo $data['type'] ?>: </span>
        <?php echo $data['message'] ?>
    </p>
    <p><?php echo $data['file'] ?>: <b><?php echo $data['line'] ?></b></p>
    <?php if ($data['trace']): ?>
        <p>BackTrace:</p>
        <pre><code><?php echo $data['trace'] ?></code></pre>
    <?php endif ?>
    </body>
    </html>
    <?php
    die();
}

/**
 * 显示错误页面
 *
 * @param $text
 * @param int|null $withHttpCode
 */
function showErrorPage($text, $withHttpCode = null)
{
    if (!is_null($withHttpCode)) {
        ob_clean();
        http_response_code($withHttpCode);
    }
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width">
        <meta name="robots" content="noindex,follow">
        <title>应用程序出现错误</title>
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

            #error-page {
                margin-top: 50px;
            }

            #error-page p {
                font-size: 14px;
                line-height: 1.5;
                margin: 25px 0 20px;
            }

            #error-page code {
                font-family: Consolas, Monaco, monospace;
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

        </style>
    </head>
    <body id="error-page">
    <p><?php echo $text ?></p>
    </body>
    </html>
    <?php
    die();
}