<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/18
 * Time: 19:26
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Utils\Auth;
use Utils\Dir;
use Utils\PHPComment;

require "init.php";

Common::setTitle('主题');
Common::setDescription('列表');

Auth::check('admin-level');

include "header.php";

if ($request->get('page') == 'settings') {
    // 设置页面
    goto footer;
}

$dir = __ROOT_DIR__ . __THEME_DIR__ . '/';
$now_theme = $options->get('theme', 'default');
$themes = Dir::getAllDirs(__THEME_DIR__);

Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
<div class="table-responsive">
    <table class="table table-hover">
        <colgroup>
            <col width="360">
            <col>
        </colgroup>
        <thead>
        <tr>
            <th>截图</th>
            <th>详情</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($themes as $theme):
            if (!file_exists($img = $dir . $theme . '/screen.png')) $img = 'assets/img/noscreen.png';
            if (!file_exists($name = $dir . $theme . '/index.php')) continue;
            $info = PHPComment::parseFromFile($name) ?>
            <tr class="theme-info">
                <td><img src="<?php echo $img ?>" alt="截图"></td>
                <td>
                    <h3><?php echo $name = isset($info['package']) ? $info['package'] : $theme ?></h3>
                    <p>
                        <span class="author">
                            作者:<?php $author = $info['author'] ?? null;
                            if (isset($info['link'])):?>
                                <a target="_blank" href="<?php echo $info['link'] ?>"><?php echo $author ?></a>
                            <?php else: echo $author;endif ?>
                        </span>
                        <?php if (isset($info['version'])): ?>
                            <span class="version">
                            版本:<?php echo $info['version'] ?>
                            </span>
                        <?php endif ?>
                    </p>
                    <p><?php echo $info['description'] ?></p>
                    <?php if ($now_theme !== $theme): ?>
                        <p>
                            <a href="theme-editor.php?theme=<?php echo $theme ?>">编辑</a>
                            <a href="do.php?a=Admin/Theme/Change&theme=<?php echo $theme ?>">启用</a>
                            <a href="?a=Admin/Theme/Delete&theme=<?php echo $theme ?>"
                               onclick="return confirm('是否确定删除主题<?php echo $name ?>？')">删除</a>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
footer:
include "footer.php" ?>