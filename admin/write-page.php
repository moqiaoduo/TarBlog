<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 12:05
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 * @var \Core\Plugin\Manager $plugin
 */

use Helper\Common;
use Helper\Content;
use Utils\Auth;
use Utils\DB;
use Utils\PHPComment;

require "init.php";

Common::setTitle(($cid = $request->get('edit')) ? '编辑页面' : '创建新页面');

Auth::check('page');

$title = '';
$slug = '';
$content = '';
$show_cid = '{cid}';
$publish_time = '';
$updated_at = '';
$order = '';
$attachments = [];

if ($cid) {
    $page = Content::getPageById($cid);

    if (is_null($page))
        showErrorPage('您欲编辑的页面不存在', 404);

    $show_cid = $cid;
    $author = $page->author();
    if (empty($author['name'])) $author['name'] = $author['username'];
    $title = $page->title;
    $content = $page->content;
    $order = $page->order;
    $slug = $page->slug;
    $publish_time = $page['created_at'];
    $updated_at = $page['updated_at'];
    $attachments = Content::getAttachmentsByParent($cid);
    $draft = Content::getPageDraft($cid);
    if ($draft) {
        $title = $draft['title'];
        $content = $draft['content'];
        $publish_time = $draft['created_at'];
        $updated_at = $draft['updated_at'];
    }
}

$types = ['publish' => '公开', 'hidden' => '隐藏', 'password' => '密码保护', 'private' => '私密'];
$templates = [];
foreach (glob(__ROOT_DIR__ . __THEME_DIR__ . '/' . $options->get('theme', 'default') .
    '/' . 'page-*.php') as $file) {
    $info = PHPComment::parseFromFile($file);
    $file = basename($file);
    $key = substr($file, 5, strlen($file) - 9);
    if (isset($info['template'])) $name = $info['template'];
    else $name = $key;
    $templates[$key] = $name;
}
include "header.php";
$plugin->new_editor_css();
Common::loadArticleCss();
?>
<meta name="allow-ext" content="<?php echo implode('|', unserialize($options->get('allowFileExt', 'a:0:{}'))) ?>">

<?php if ($success = $request->session('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <span><?php echo $success ?></span>
    </div>
<?php endif ?>

<?php if (!empty($draft)): ?>
    <p>
        <i>
            你正在编辑的是保存于 <?php echo friendly_datetime($draft['updated_at']) ?> 的草稿,
            你也可以<a href="do.php?a=Admin/Page/DeleteDraft&id=<?php echo $page->cid ?>"
                   onclick="return confirm('您确定删除这份草稿吗？')" style="color: red;">删除它</a>
        </i>
    </p>
<?php endif ?>

<form class="form-container post-form" method="post" action="do.php?a=Admin/Page/Save">
    <input type="hidden" name="id" value="<?php echo $request->get('edit') ?>">
    <div class="post-left">
        <div class="form-group">
            <input type="text" name="title" class="form-control input-block" placeholder="标题"
                   style="font-weight: bold;"
                   autocomplete="off" value="<?php echo $title ?>">
        </div>
        <div class="form-group url-slug mono">
            <?php echo route('page', ['slug' => Common::slugInput($slug), 'cid' => $show_cid]) ?>
        </div>
        <div class="form-group">
            <?php
            $plugin->trigger($plugged)->new_editor($content);
            if (!$plugged): ?>
                <textarea name="content"><?php echo htmlspecialchars($content) ?></textarea>
            <?php endif ?>
        </div>
        <div class="form-group post-save">
            <button type="submit" name="type" value="page_draft" class="btn">保存草稿</button>
            <button type="submit" name="type" value="page" class="btn btn-primary">发布页面</button>
        </div>
    </div>
    <div class="post-right">
        <div class="tab">
            <div class="tab-header">
                <div class="tab-header-item active" data-tab="options">选项</div>
                <div class="tab-header-item" data-tab="attach">附件</div>
            </div>
            <div class="tab-content">
                <div class="tab-item active" id="options">
                    <section class="tarblog-post-option">
                        <label for="publish-date">发布日期</label>
                        <input type="text" class="form-control input-block" name="created_at" id="publish-date"
                               autocomplete="off" value="<?php echo $publish_time ?>">
                    </section>
                    <section class="tarblog-post-option">
                        <label for="order">页面顺序</label>
                        <input type="text" class="form-control input-block" id="order" name="order" autocomplete="off"
                               value="<?php echo $order ?>">
                        <span class="description">为你的自定义页面设定一个序列值以后, 能够使得它们按此值从小到大排列</span>
                    </section>
                    <section class="tarblog-post-option">
                        <label for="template">自定义模板</label>
                        <select id="template" name="template" class="form-control input-block">
                            <option value="">不选择</option>
                            <?php foreach ($templates as $key => $template): ?>
                                <option value=<?php echo '"' . $key . '"';
                                if (isset($page) && $key == $page->template) echo "selected" ?>><?php echo $template ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="description">如果你为此页面选择了一个自定义模板, 系统将按照你选择的模板文件展现它</span>
                    </section>
                    <div class="collapse">
                        <div class="collapse-item">
                            <div class="collapse-header">
                                <span>高级选项</span>
                            </div>
                            <div class="collapse-content">
                                <section class="tarblog-post-option">
                                    <label for="visibility">公开度</label>
                                    <?php Common::buildSelect($types, ['id' => 'visibility', 'name' => 'visibility',
                                        'class' => 'form-control', 'value' => $page_status = (isset($page) ? $page->status : null)]) ?>
                                </section>
                                <section class="tarblog-post-option">
                                    <input id="password" name="password" class="form-control input-block"
                                        <?php if ($page_status != 'password') echo 'style="display: none;"' ?>
                                           placeholder="内容密码" value="<?php echo isset($page) ? $page->password : null ?>">
                                </section>
                                <section class="tarblog-post-option allow-option">
                                    <label>权限控制</label>
                                    <ul>
                                        <li>
                                            <input type="checkbox" name="allowComment" id="allow-comment"
                                                   class="form-checkbox"
                                                <?php if (!isset($page) || $page->allowComment) echo "checked" ?>>
                                            <label for="allow-comment">允许评论</label>
                                        </li>
                                    </ul>
                                </section>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($author)): ?>
                        <section class="tarblog-post-option">
                            <p class="description">
                                ---<br>
                                本页面由
                                <a href="user-editor.php?id=<?php echo $author['id'] ?>"><?php echo $author['name'] ?></a>
                                撰写<br>
                                最后更新于 <?php echo $updated_at ?>
                            </p>
                        </section>
                    <?php endif ?>
                </div>
                <div class="tab-item" id="attach">
                    <div id="upload-panel">
                        <div class="upload-area" draggable="true" style="position: relative;">点击该区域选择文件<br>或者拖放文件到这里上传
                        </div>
                        <ul id="file-list">
                            <?php foreach ($attachments as $attachment):
                                $file_info = unserialize($attachment['content']);
                                if ($file_info['type'] === 'image') $icon = 'image-l';
                                else $icon = 'file-l'; ?>
                                <li id="attachment-<?php echo $attachment['cid'] ?>">
                                    <i class="czs-<?php echo $icon ?>"></i>
                                    <a class="insert-file <?php echo $file_info['type'] ?>"
                                       href="javascript:;" title="点击插入文件"><?php echo $attachment['title'] ?></a>
                                    <input type="hidden" class="attachment-url" value="<?php
                                    $plugin->trigger($plugged)->attachment_url($file_info['path']);
                                    if (!$plugged) {
                                        echo siteUrl('usr/upload/' . $file_info['path']);
                                    }
                                    ?>">
                                    <div class="info">
                                        <?php echo $file_info['size'] ?>
                                        <a class="file" target="_blank" title="编辑"
                                           href="media-editor.php?cid=<?php echo $attachment['cid'] ?>">
                                            <i class="czs-pen"></i>
                                        </a>
                                        <a class="file" href="javascript:;" title="删除">
                                            <i class="czs-trash-l"></i>
                                        </a>
                                        <input type="hidden" name="attachment[]" class="attachment-id"
                                               value="<?php echo $attachment['cid'] ?>">
                                    </div>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<ul id="tag-input-dropdown"></ul>
<input type="file" style="display: none;" id="file-select" multiple>
<form class="modal modal-close-by-out modal-close-by-esc modal-center" id="insert-link-form">
    <div class="modal-header">
        <h4>插入附件</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
            ×
        </button>
    </div>
    <div class="modal-content">
        <div class="form-group">
            <div class="form-inline">
                <label for="insert-link-name" class="form-label">链接名称</label>
                <input type="text" id="insert-link-name" name="name" required autocomplete="off"
                       class="form-control">
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label for="insert-link-url" class="form-label">链接地址</label>
                <input type="text" id="insert-link-url" name="url" required autocomplete="off" class="form-control">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="form-submit-group">
            <button type="submit" class="btn btn-primary">插入链接</button>
            <button type="button" class="btn" id="switch-insert-pic">作为图片插入</button>
        </div>
    </div>
</form>
<form class="modal modal-close-by-out modal-close-by-esc modal-center" id="insert-pic-form">
    <div class="modal-header">
        <h4>插入图片</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
            ×
        </button>
    </div>
    <div class="modal-content">
        <div class="form-group">
            <img src="" id="insert-pic-img" alt="">
        </div>
    </div>
    <div class="modal-footer">
        <div class="form-submit-group">
            <button type="submit" class="btn btn-primary">插入图片</button>
            <button type="button" class="btn" id="switch-insert-link">作为链接插入</button>
            <input type="hidden" name="name" id="insert-pic-name">
            <input type="hidden" name="url" id="insert-pic-url">
        </div>
    </div>
</form>
<?php
Common::loadArticleJS();

include "footer.php";
