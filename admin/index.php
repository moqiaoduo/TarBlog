<?php
/**
 * Created by TarBlog.
 * Date: 2018/8/23
 * Time: 23:18
 */

use Helper\Common;
use Models\Post;
use Utils\Auth;
use Utils\DB;
use Utils\Route;

require "init.php";

Common::setTitle('首页');
Common::setDescription('仪表盘');

include "header.php";

if (!Auth::check('dashboard', false))
    redirect(siteUrl(__ADMIN_DIR__ . 'user-editor.php'));

$recent_articles = DB::table('contents')->where('type', 'post')
    ->where('uid', Auth::id())->orderByDesc('created_at')->limit(10)->get();

$recent_comments = DB::table('comments')->where('ownerId', Auth::id())
    ->orderByDesc('created_at')->limit(10)->get();

$articles_num = DB::table('contents')->where('type', 'post')
    ->where('uid', Auth::id())->count();

$comments_num = DB::table('comments')->where('status', 'approved')->where(function ($query) {
    $query->where('ownerId', Auth::id())->orWhere('authorId', Auth::id());
})->count();

$pending_comments_num = DB::table('comments')->where('ownerId', Auth::id())
    ->where('status', 'pending')->count();

$official_logs = json_encode(\Utils\Curl::get('https://tarblog.cn/logs'));
?>
<style>
    @media screen and (max-width: 768px) {
        .panel {
            width: 100%;
        }
    }
    .panel {
        border: 1px solid #aaa;
        border-radius: 10px;
        text-align: center;
        min-width: 300px;
    }
    .panel-body {
        padding: 20px;
        background-color: #eee;
        border-bottom: 1px solid #aaa;
    }
    .panel-title {
        font-size: 40px;
    }
    .panel-content {
        font-size: 30px;
    }
    .panel-footer {
        padding: 10px;
    }
    .list {
        width: 300px;
        list-style: none;
        padding: 0;
    }
    .list > li span {
        display: inline-block;
        width: 45px;
        text-align: right;
        padding-bottom: 5px;
        padding-right: 10px;
        vertical-align: top;
    }
    .list > li div {
        display: inline-block;
        padding-left: 10px;
        padding-bottom: 5px;
        border-left: 1px solid #eee;
        word-break: break-word;
        word-wrap: break-word;
        width: 225px;
    }
</style>
<div class="flex justify-around mobile-no-flex">
    <div class="panel">
        <div class="panel-body">
            <div class="panel-title">
                文章
            </div>
            <div class="panel-content">
                <?php echo $articles_num ?>
            </div>
        </div>
        <div class="panel-footer">
            <a href="./post.php"><i class="czs-come-l"></i> More</a>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <div class="panel-title">
                评论
            </div>
            <div class="panel-content">
                <?php echo $comments_num ?>
            </div>
        </div>
        <div class="panel-footer">
            <a href="./comments.php"><i class="czs-come-l"></i> More</a>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <div class="panel-title">
                待审核评论
            </div>
            <div class="panel-content">
                <?php echo $pending_comments_num ?>
            </div>
        </div>
        <div class="panel-footer">
            <a href="./comments.php?status=pending"><i class="czs-come-l"></i> More</a>
        </div>
    </div>
</div>
<div class="flex mobile-no-flex justify-around" style="margin-top: 20px;">
    <div>
        <h3>最近发布的文章</h3>
        <ul class="list">
            <?php if (empty($recent_articles)): ?>
                <li><i>暂无文章</i></li>
            <?php else:
            foreach ($recent_articles as $article): ?>
            <li>
                <span><?php echo dateX('d/M', $article['created_at']) ?></span>
                <div>
                    <a target="_blank" href="<?php echo route('post', Route::fillPostParams(new Post($article))) ?>">
                        <?php echo $article['title'] ?>
                    </a>
                </div>
            </li>
            <?php endforeach;endif ?>
        </ul>
    </div>
    <div>
        <h3>最近收到的评论</h3>
        <ul class="list">
            <?php if (empty($recent_comments)): ?>
                <li><i>暂无评论</i></li>
            <?php else:
            foreach ($recent_comments as $comment):
                if ($comment['url']) {
                    $url = $comment['url'];
                } else {
                    $content = DB::table('contents')->where('cid', $comment['cid'])->first();
                    if (is_null($content)) {
                        $url = 'javascript:;';
                    } else {
                        $url = route($content['type'].'.comment', Route::fillPostParams(new Post($comment)));
                    }
                }
                ?>
                <li>
                    <span><?php echo dateX('d/M', $comment['created_at']) ?></span>
                    <div>
                        <a target="_blank" href="<?php echo $url ?>"><?php echo $comment['name'] ?></a>:
                        <?php echo $comment['content'] ?>
                    </div>
                </li>
            <?php endforeach;endif ?>
        </ul>
    </div>
    <div>
        <h3>官方动态</h3>
        <ul class="list">
            <?php if (is_array($official_logs)):
                foreach ($official_logs as $log): ?>
                    <li>
                        <span><?php echo dateX('d/M', $log['date']) ?></span>
                        <div>
                            <a target="_blank" href="<?php echo $log['url'] ?>">
                                <?php echo $log['title'] ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach;
                else: ?>
            <li><i>暂无动态</i></li>
            <?php endif ?>
        </ul>
    </div>
</div>
<?php include "footer.php" ?>
