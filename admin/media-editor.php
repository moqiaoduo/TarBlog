<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/21
 * Time: 10:54
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 */

use Helper\Common;
use Helper\Content;
use Utils\Auth;

require "init.php";

Common::setTitle('编辑附件');

Auth::check('attachment');

if (!($cid = $request->get('cid'))) redirect('attachment.php');

// TODO 做一个兼容第三方储存平台的方案

define('UPLOAD_DIR', __ROOT_DIR__ . '/usr/upload/');
define('UPLOAD_URL', get_option('siteUrl') . '/usr/upload/');

$attachment = Content::getAttachmentById($cid);

if (is_null($attachment)) showErrorPage('您欲编辑的附件不存在', 404);

$content = unserialize($attachment['content']);

$filename = $content['path'];

if (!file_exists(UPLOAD_DIR . $filename))
    back(with_error('文件不存在'));

include "header.php";
Common::loadSuccessAlert($session->get('success'));
?>
<style>
    img, video, audio {
        max-width: 100%;
    }

    @media screen and (min-width: 768px) {
        .row {
            display: flex;
        }
        .column-left {
            flex: 1;
        }
        .column-right {
            width: 350px;
            margin-left: 20px;
        }
        .form-label {
            width: 60px !important;
        }
        .form-description {
            margin-left: 70px;
        }
    }

</style>
<div class="row">
    <div class="column-left">
        <p>
            <?php
            switch ($content['type']) {
                case 'image':
                    echo '<img src="' . UPLOAD_URL . $filename . '">';
                    $icon = 'czs-image';
                    break;
                case "video":
                    echo '<video src="' . UPLOAD_URL . $filename . '"></video>';
                    $icon = 'czs-video-file';
                    break;
                case "audio":
                    echo '<audio src="' . UPLOAD_URL . $filename . '"></audio>';
                    $icon = 'czs-music-file';
                    break;
                default:
                    $icon = 'czs-file';
            }
            ?>
        </p>
        <p style="margin: 10px 0;"><a href="<?php echo UPLOAD_URL . $filename ?>" target="_blank">
                <i class="<?php echo $icon ?>"></i> <?php echo $attachment['title'] ?></a>
            <?php echo $content['size'] ?>
        </p>
        <p><input readonly class="form-control input-block" value="<?php echo UPLOAD_URL . $filename ?>"></p>
    </div>
    <div class="column-right">
        <form method="post" class="form-container" action="do.php?a=Admin/Attachment/Modify&cid=<?php echo $cid ?>">
            <div class="form-group">
                <div class="form-inline">
                    <label class="form-label" for="title">标题 *</label>
                    <input type="text" name="title" id="title" required class="form-control"
                           autocomplete="off"  value="<?php echo $attachment['title'] ?>">
                </div>
            </div>
            <div class="form-group">
                <div class="form-inline">
                    <label class="form-label" for="slug">别名</label>
                    <input type="text" name="slug" id="slug" required class="form-control"
                           autocomplete="off" value="<?php echo $attachment['slug'] ?>">
                </div>
                <div class="form-description">
                    别名用于创建友好的链接形式, 建议使用字母, 数字, 下划线和横杠.
                </div>
            </div>
            <div class="form-group">
                <div class="form-inline">
                    <label class="form-label" for="description">分类描述</label>
                    <textarea id="description" name="description" class="form-control" rows="5"
                    ><?php echo htmlspecialchars($content['description'] ?? '') ?></textarea>
                </div>
                <div class="form-description">
                    此文字用于描述分类, 在有的主题中它会被显示.
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">提交修改</button>
                <button type="reset" class="btn">重置</button>
            </div>
        </form>
    </div>
</div>
<?php include 'footer.php' ?>
