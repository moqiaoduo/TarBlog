<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 17:09
 */

namespace Collection;

use App\Base;
use Core\DataContainer;
use Core\Dynamic;
use Core\Paginator;
use Core\Plugin\Manager as PluginManager;
use Models\Content;
use Utils\Auth;
use Utils\DB;

class Comments extends DataContainer
{
    use Dynamic;

    /**
     * 文章/页面模型
     *
     * @var Content
     */
    protected $content;

    /**
     * 评论分页对象
     *
     * @var Paginator
     */
    protected $comments;

    /**
     * 评论设置
     *
     * @var array
     */
    protected $options;

    /**
     * 当前渲染评论层
     *
     * @var int
     */
    private $levels = 0;

    /**
     * 当前队列指针顺序值,从1开始
     *
     * @var integer
     */
    private $sequence = 0;

    /**
     * 当前评论回复
     *
     * @var mixed
     */
    private $children;

    /**
     * 插件管理器
     *
     * @var PluginManager
     */
    private $plugin;

    /**
     * 构造函数，需传入文章/页面模型
     *
     * @param Content|null $model
     */
    public function __construct($model = null)
    {
        if (is_null($model)) {
            $this->comments = [];
        } else {
            $this->content = $model;

            $this->comments = $this->content->getTopLevelCommentPaginate(app('request')->get('page') ?:
                get_option('commentsPageDisplay', 'first'), get_option('commentsPageSize'));
        }

        if ($this->comments instanceof Paginator)
            $this->setQueue($this->comments->getData());
        else
            $this->setQueue($this->comments);

        $this->plugin = app('plugin'); // 因为没有做什么注入处理，只能这样获取了
    }

    /**
     * 显示评论分页
     *
     * @param string $prev
     * @param string $next
     */
    public function pageNav($prev = '&laquo;', $next = '&raquo;')
    {
        // 不是分页器肯定显示不了分页啊
        if ($this->comments instanceof Paginator)
            $this->comments->view(['back' => $prev, 'forward' => $next]);
    }

    /**
     * 列出评论
     *
     * @param mixed $singleCommentOptions 单个评论自定义选项
     * @return void
     */
    public function listComments($singleCommentOptions = [])
    {
        //初始化一些变量
        $defaultOptions = [
            'before' => '<ol class="comment-list">',
            'after' => '</ol>',
            'beforeAuthor' => '',
            'afterAuthor' => '',
            'beforeDate' => '',
            'afterDate' => '',
            'replyWord' => '回复',
            'commentStatus' => '您的评论正等待审核！',
            'avatarSize' => 32,
            'defaultAvatar' => NULL
        ];

        $this->options = $options = array_merge($defaultOptions, (array)$singleCommentOptions);

        $this->plugin->trigger($plugged)->list_comments($this->options, $this);

        if (!$plugged) {
            if ($this->have()) {
                echo $this->options['before'];

                while ($this->next()) {
                    $this->children = $this->getChildrenByParentId($this->row['id']);
                    $this->threadedCommentsCallback();
                }

                echo $this->options['after'];
            }
        }
    }

    /**
     * 读取数据库中评论的回复
     *
     * @param int $id
     * @return mixed|null
     */
    public function getChildrenByParentId($id)
    {
        return DB::table('comments')->where('parent', $id)
            ->when(!(Auth::id() && Auth::user()->isAdmin()), function ($query) {
                $query->where('status', 'approved')->orWhere('status', 'pending')->when(Auth::id(), function ($query) {
                    $query->where('authorId', Auth::id())->orWhere('ownerId', Auth::id());
                }, true)->when(!Auth::id(), function ($query) {
                    $query->where('name', Base::remember('author', true))
                        ->where('email', Base::remember('mail', true)); // URL不参与判断
                });
            }, true)->orderBy('created_at', get_option('commentsOrder', 'DESC'))->get();
    }

    /**
     * 外部获取当前评论回复
     *
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * 外部判断是否有评论回复
     *
     * @return bool
     */
    public function hasChildren()
    {
        return is_array($this->children) && count($this->children) > 0;
    }

    /**
     * 评论回调函数
     *
     * @return void
     */
    private function threadedCommentsCallback()
    {
        if (func_num_args() > 0) {
            $singleCommentOptions = func_get_arg(0);
        } else {
            $singleCommentOptions = $this->options;
        }

        if (function_exists('threadedComments')) {
            return threadedComments($this, $singleCommentOptions);
        }

        $commentClass = '';
        if ($this->row['authorId']) {
            if ($this->row['authorId'] == $this->row['ownerId']) {
                $commentClass .= ' comment-by-author';
            } else {
                $commentClass .= ' comment-by-user';
            }
        }
        ?>
        <li id="<?php $this->theId(); ?>" class="comment-body<?php
        if ($this->levels > 0) {
            echo ' comment-child';
            $this->levelsAlt(' comment-level-odd', ' comment-level-even');
        } else {
            echo ' comment-parent';
        }
        $this->alt(' comment-odd', ' comment-even');
        echo $commentClass;
        ?>">
            <div class="comment-author" itemprop="creator">
                <span itemprop="image"><?php $this->gravatar($singleCommentOptions['avatarSize'], $singleCommentOptions['defaultAvatar']); ?></span>
                <cite class="fn" itemprop="name"><?php echo $singleCommentOptions['beforeAuthor'];
                    $this->author();
                    echo $singleCommentOptions['afterAuthor']; ?></cite>
            </div>
            <div class="comment-meta">
                <a href="<?php $this->permalink(); ?>">
                    <time itemprop="commentTime" datetime="<?php $this->date(); ?>">
                        <?php echo $singleCommentOptions['beforeDate'];
                        $this->date();
                        echo $singleCommentOptions['afterDate']; ?>
                    </time>
                </a>
                <?php if ($this->status('pending') && $this->isAuthor()) : ?>
                    <em class="comment-awaiting-moderation"><?php echo $singleCommentOptions['commentStatus']; ?></em>
                <?php endif ?>
            </div>
            <div class="comment-content" itemprop="commentText">
                <?php $this->content(); ?>
            </div>
            <div class="comment-reply">
                <?php $this->reply($singleCommentOptions['replyWord']); ?>
            </div>
            <?php if ($this->children) { ?>
                <div class="comment-children" itemprop="discusses">
                    <?php $this->levels++;
                    $this->threadedComments();
                    $this->levels--; ?>
                </div>
            <?php } ?>
        </li>
        <?php
    }

    /**
     * 递归输出评论
     *
     * @return void
     */
    public function threadedComments()
    {
        $children = $this->children;
        if ($children) {
            //缓存变量便于还原
            $tmp = $this->row;
            $this->sequence++;

            //在子评论之前输出
            echo $this->options['before'];

            foreach ($children as $child) {
                $this->row = $child;
                $this->children = $this->getChildrenByParentId($this->row['id']);
                $this->threadedCommentsCallback(...func_get_args());
            }

            $this->row = $tmp; // 结束以后再赋值，减少执行时间

            //在子评论之后输出
            echo $this->options['after'];

            $this->sequence--;
        }
    }

    /**
     * 根据规则选择参数输出
     *
     * @return void
     */
    public function alt()
    {
        $args = func_get_args();
        $num = func_num_args();

        $sequence = $this->levels <= 0 ? $this->sequence : (isset($this->queue) ? count($this->queue) + 1 : 1);

        $split = $sequence % $num;
        echo $args[(0 == $split ? $num : $split) - 1];
    }

    /**
     * 根据深度余数输出
     *
     * @return void
     */
    public function levelsAlt()
    {
        $args = func_get_args();
        $num = func_num_args();
        $split = $this->levels % $num;
        echo $args[(0 == $split ? $num : $split) - 1];
    }

    /**
     * 显示评论作者
     */
    public function author()
    {
        if (get_option('commentsShowUrl') && $this->row['url']) {
            echo '<a href="' . $this->row['url'] . '" target="_blank"' .
                (get_option('commentsUrlNofollow') ? ' rel="nofollow"' : '') . '>' . $this->row['name'] . '</a>';
        } else {
            echo $this->row['name'];
        }
    }

    /**
     * 调用gravatar输出用户头像
     *
     * @param integer $size 头像尺寸
     * @param string|null $default 默认输出头像
     * @return void
     */
    public function gravatar($size = 32, $default = NULL)
    {
        if (get_option('commentsAvatar')) {
            $rating = get_option('commentsAvatarRating', 'G');

            $this->plugin->trigger($plugged)->avatar($name = $this->row['name'],
                $email = $this->row['email'], $default);

            if (!$plugged) {
                $md5 = md5($email);

                $url = "https://secure.gravatar.com/avatar/$md5?s=$size&r=$rating&d=$default";

                echo '<img class="avatar" src="' . $url . '" alt="' .
                    $name . '" width="' . $size . '" height="' . $size . '" />';
            }
        }
    }

    /**
     * 评论回复链接
     *
     * @param string $word 回复链接文字
     * @return void
     */
    public function reply($word = '')
    {
        if (get_option('commentsThreaded') && $this->content->allowComment) {
            $word = $word ?: '回复';

            $this->plugin->trigger($plugged)->reply($word, $this);

            if (!$plugged) {
                echo '<a href="#' . $this->theId . '" rel="nofollow" onclick="return TarBlogComment.reply(\'' .
                    $this->theId . '\', ' . $this->id . ');">' . $word . '</a>';
            }
        }
    }

    /**
     * 取消评论回复链接
     *
     * @param string $word 取消回复链接文字
     * @return void
     */
    public function cancelReply($word = '')
    {
        if (get_option('commentsThreaded')) {
            $word = $word ?: '取消回复';

            $this->plugin->trigger($plugged)->cancelReply($word, $this);

            if (!$plugged) {
                echo '<a id="cancel-comment-reply-link" href="#" rel="nofollow" style="display: none;"
onclick="return TarBlogComment.cancelReply();">' . $word . '</a>';
            }
        }
    }

    /**
     * 判断状态
     *
     * @param $status
     * @return bool
     */
    public function status($status)
    {
        return $this->row['status'] == $status;
    }

    /**
     * 判断是否为作者
     *
     * @return bool
     */
    public function isAuthor()
    {
        return $this->row->authorId == Auth::id() || (
                $this->row->name == Base::remember('author', true) &&
                $this->row->email == Base::remember('mail', true)
            );
    }

    public function _permalink()
    {
        return siteUrl(app('request')->getRequestUri() . '#' . $this->theId);
    }

    /**
     * element id
     *
     * @return string|void
     */
    public function _theId()
    {
        return 'comment-' . $this->id;
    }

    /**
     * 评论id
     *
     * @return mixed
     */
    public function _id()
    {
        return $this->row['id'];
    }

    /**
     * 获取评论日期
     *
     * @return false|string
     */
    public function _date()
    {
        return dateX(1, $this->row['created_at']);
    }

    /**
     * 评论内容
     *
     * @return mixed
     */
    public function _content()
    {
        return $this->row['content'];
    }
}