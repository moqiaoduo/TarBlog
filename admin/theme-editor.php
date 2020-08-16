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

use Helper\CodeMirror;
use Helper\Common;
use Utils\Auth;
use Utils\Dir;
use Utils\PHPComment;

require "init.php";

Common::setTitle('编辑主题');

Auth::check('admin-level');

$theme = $request->get('theme', $options->get('theme', 'default'));

$checkTheme = false;

$dir = __ROOT_DIR__ . __THEME_DIR__ . '/';

$themes = [];

foreach (Dir::getAllDirs(__THEME_DIR__) as $value) {
    if (!is_dir($dir . $value)) continue;
    $page_info = PHPComment::parseFromFile($dir . $value . '/index.php');
    if (isset($page_info['package'])) $theme_name = $page_info['package'];
    else $theme_name = $value;
    $themes[] = ['value' => $value, 'text' => $theme_name];
    if ($value == $theme) $checkTheme = true;
}

if (!$checkTheme)
    showErrorPage('Invalid Theme', 403);

include "header.php";
CodeMirror::loadHead();
?>
<div class="row">
    <div class="column-left">
        <h4 id="title">Loading...</h4>
    </div>
    <div class="column-right">
        <form method="get" class="layui-form" action="theme-editor.php" id="tools">
            <label for="theme" class="form-label">选择要编辑的主题：</label>
            <select id="theme" name="theme" class="form-control input-block">
                <?php foreach ($themes as $val): ?>
                    <option value="<?php echo $val['value'] ?>"<?php if ($val['value'] == $theme) echo " selected" ?>>
                        <?php echo $val['text'] ?>
                    </option>
                <?php endforeach ?>
            </select>
        </form>
    </div>
</div>
<div class="row">
    <div class="column-left">
        <h4>选择的文件内容:</h4>
        <div id="loading">Loading...</div>
        <form id="editor-form" class="form-container">
            <div class="form-group">
                <textarea class="form-control" id="editor" name="code"></textarea>
            </div>
            <div class="form-group" style="text-align: right;">
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </form>
    </div>
    <div class="column-right">
        <h4>主题文件</h4>
        <ul id="fileTree" class="ztree outline"></ul>
    </div>
</div>

<?php
CodeMirror::js($theme);

include "footer.php" ?>