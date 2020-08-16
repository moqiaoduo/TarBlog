<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/18
 * Time: 19:26
 *
 * @var \Core\Http\Request $request
 * @var \Core\Plugin\Manager $plugin
 */

use Helper\CodeMirror;
use Helper\Common;
use Utils\Auth;

require "init.php";

Common::setTitle('编辑插件');

Auth::check('admin-level');

$plugins = $plugin->scanAllPlugins();

if (empty($plugins))
    showErrorPage('No Plugin', 403);

$pg = $request->get('plugin', current($plugins)['package']);

include "header.php";
CodeMirror::loadHead();
?>
    <div class="row">
        <div class="column-left">
            <h4 id="title">Loading...</h4>
        </div>
        <div class="column-right">
            <form method="get" class="layui-form" action="theme-editor.php" id="tools">
                <label for="theme" class="form-label">选择要编辑的插件：</label>
                <select id="theme" name="theme" class="form-control input-block">
                    <?php foreach ($plugins as $val):
                        $display = $val['display'] ?? $val['package'];
                        ?>
                        <option value="<?php echo $val['package'] ?>"
                            <?php if ($val['package'] == $pg) echo "selected" ?>><?php echo $display ?></option>
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
            <h4>插件文件</h4>
            <ul id="fileTree" class="ztree outline"></ul>
        </div>
    </div>
<?php
CodeMirror::js($pg, 'plugin');

include "footer.php" ?>