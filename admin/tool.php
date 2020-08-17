<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/16
 * Time: 23:17
 *
 * @var \Core\Http\Request $request
 * @var \Core\Plugin\Manager $plugin
 * @var \Core\Http\Session $session
 */

use Helper\Common;
use Utils\Auth;

require "init.php";

Common::setTitle('可用工具');

Auth::check('admin-level');

$page = $request->get('p');

$tools = $plugin->tool();

if ($page) {
    foreach ($tools as $tool) {
        if ($tool['p'] === $page) {
            Common::setTitle('工具');
            Common::setDescription($tool['name']);
            include "header.php";
            Common::loadToolCSS();
            include __ROOT_DIR__ . __PLUGIN_DIR__ . '/' . $tool['php'];
            goto footer;
        }
    }

    if (file_exists($file = __ROOT_DIR__ . '/admin/tool/' . $page . '.php')) {
        $trans = array('export' => "导出", 'import' => "导入");
        Common::setDescription($trans[$page] ?? '');
        include "header.php";
        Common::loadToolCSS();
        include $file;
        goto footer;
    }

    showErrorPage('工具页面不存在', 404);
}

include "header.php";
Common::loadToolCSS();
if (empty($tools)): ?>
<p>暂无工具</p>
<?php else: ?>
    <table class="table table-hover" style="width: auto;">
        <?php foreach ($tools as $tool): ?>
            <tr class="tool-item">
                <td class="tool-name"><?php echo $tool['name'] ?><br><a
                            href="?p=<?php echo $tool['p'] ?>">进入</a></td>
                <td class="tool-desc"><?php echo isset($tool['description']) ? $tool['description'] : '暂无说明' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif ?>
<?php footer:
include "footer.php" ?>