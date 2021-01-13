<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/13
 * Time: 23:36
 *
 * @var \Core\Options $options
 */

require 'init.php';

$db_version = $options->get('version', 'v0.2.2'); // 因为之前没有这个option，所以默认为v0.2.2

$core_version = \Core\Upgrade::$newest_version;

if ($db_version < $core_version) {
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width">
        <meta name="robots" content="noindex,follow">
        <title>网站需要更新</title>
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
                font-size: 14px;
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
    <p>检测到当前数据库版本 <?php echo $db_version ?> 低于当前程序版本 <?php echo $core_version ?>
        ，可能有部分功能无法正常使用，请及时执行更新操作。</p>
    <p>注意事项：</p>
    <ol>
        <li>更新之前最好做一下备份（包括网站文件和数据库），出了问题可以很快回退。</li>
        <li>请查看版本 <?php echo $core_version ?> 的
            <a href="https://github.com/moqiaoduo/TarBlog/release" target="_blank">更新说明</a> 再进行操作，
            否则可能会导致数据丢失或程序损坏。</li>
    </ol>
    <form method="post" action="do.php?a=Admin/Upgrade">
        <button type="submit" class="button" onclick="return confirm('请确认您已经仔细阅读更新说明，' +
         '若不按说明操作造成数据丢失，作者概不负责')">执行更新</button>
    </form>
    </body>
    </html>
    <?php
    die();
}

echo "无升级任务";