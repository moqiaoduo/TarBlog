<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/4
 * Time: 0:05
 */

namespace App;

use Collection\Categories;
use Collection\Pages;
use Core\Container\Manager as App;
use Core\Database\Manager as Database;
use Core\Dynamic;
use Core\Http\Cookie;
use Core\Http\Request;
use Core\Http\Token;
use Core\Options;
use Core\Plugin\Manager as PluginManager;
use Core\Routing\Route;
use Core\User;
use Utils\URLGenerator;

abstract class Base
{
    use Dynamic;

    /**
     * 主题目录，初始化时提供
     *
     * @var string
     */
    protected $_themeDir;

    /**
     * 主题名称，初始化时提供
     *
     * @var string
     */
    protected $_theme;

    protected $_archiveTitle;

    /**
     * 数据库管理器，初始化时提供
     *
     * @var Database
     */
    protected $db;

    /**
     * 插件管理器，初始化时提供
     *
     * @var PluginManager
     */
    protected $plugin;

    /**
     * 请求
     *
     * @var Request
     */
    protected $request;

    /**
     * 设置项
     *
     * @var Options
     */
    protected $options;

    /**
     * Auth
     *
     * @var User
     */
    protected $user;

    /**
     * 路由
     *
     * @var Route
     */
    protected $route;

    /**
     * 路由参数
     *
     * @var array
     */
    protected $routeParams;

    /**
     * 是否输出评论js
     *
     * @var bool
     */
    protected $enaCommentJS = false;

    /**
     * 页面类型，目前支持：
     * archive
     * search
     * category
     * post
     * page
     *
     * @var string
     */
    protected $type = 'archive';

    /**
     * 这里放各种业务逻辑，例如数据库读写，表单提交处理
     * 若不返回true，则将会作404处理；其他错误请使用throw抛出
     *
     * @return bool
     */
    public abstract function execute() : bool ;

    /**
     * 用于渲染页面，直接include主题文件即可
     *
     * @return void
     */
    public abstract function render();

    /**
     * 初始化
     *
     * @param App $app
     * @param string|null $theme
     * @param string|null $themeDir
     * @param array $routeParams
     * @param Route|null $route
     */
    public function __construct($app, $theme = null, $themeDir = null, $routeParams = [], $route = null)
    {
        $this->_theme = $theme;
        $this->_themeDir = $themeDir;
        $this->db = $app->make('db');
        $this->plugin = $app->make('plugin');
        $this->request = $app->make('request');
        $this->options = $app->make('options');
        $this->user = new User($app->make('auth'));
        $this->routeParams = $routeParams;
        $this->route = $route;
    }

    /**
     * 独立页面
     *
     * @return Pages
     */
    public function page()
    {
        return new Pages();
    }

    /**
     * 分类
     *
     * @return Categories
     */
    public function category()
    {
        return new Categories();
    }

    /**
     * 引用所需文件
     *
     * @param $fileName
     */
    public function need($fileName)
    {
        if (file_exists($path = $this->_themeDir . DIRECTORY_SEPARATOR . $fileName)) {
            include_once $path;
        }
    }

    /**
     * 显示资源文件URL
     *
     * @param $uri
     */
    public function asset($uri)
    {
        echo URLGenerator::asset($uri, $this->_theme);
    }

    /**
     * 头部
     */
    public function header()
    {
        $keywords = $this->options->get('keyword');
        $description = $this->options->get('description');
        echo <<<EOF
<meta name="keywords" content="$keywords">
<meta name="generator" content="TarBlog">
<meta name="description" content="$description">
EOF;

        $this->plugin->header();
    }

    /**
     * 脚部
     */
    public function footer()
    {
        if ($this->enaCommentJS)
            $this->commentsJS();

        $this->plugin->footer();
    }

    /**
     * 评论 js
     */
    public function commentsJS()
    {
        echo <<<EOF
<script type="text/javascript">
(function () {
    window.TarBlogComment = {
        dom : function (id) {
            return document.getElementById(id);
        },
    
        create : function (tag, attr) {
            var el = document.createElement(tag);
        
            for (var key in attr) {
                el.setAttribute(key, attr[key]);
            }
        
            return el;
        },

        reply : function (cid, coid) {
            var comment = this.dom(cid), parent = comment.parentNode,
                response = this.dom('{$this->respondId}'), input = this.dom('comment-parent'),
                form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
                textarea = response.getElementsByTagName('textarea')[0];

            if (null == input) {
                input = this.create('input', {
                    'type' : 'hidden',
                    'name' : 'parent',
                    'id'   : 'comment-parent'
                });

                form.appendChild(input);
            }

            input.setAttribute('value', coid);

            if (null == this.dom('comment-form-place-holder')) {
                var holder = this.create('div', {
                    'id' : 'comment-form-place-holder'
                });

                response.parentNode.insertBefore(holder, response);
            }

            comment.appendChild(response);
            this.dom('cancel-comment-reply-link').style.display = '';

            if (null != textarea && 'text' == textarea.name) {
                textarea.focus();
            }

            return false;
        },

        cancelReply : function () {
            var response = this.dom('{$this->respondId}'),
            holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');

            if (null != input) {
                input.parentNode.removeChild(input);
            }

            if (null == holder) {
                return true;
            }

            this.dom('cancel-comment-reply-link').style.display = 'none';
            holder.parentNode.insertBefore(response, holder);
            return false;
        }
    };
})();
</script>
EOF;
    }

    /**
     * 输出cookie记忆内容
     *
     * @param string $cookieName 已经记忆的cookie名称
     * @param boolean $return 是否返回
     * @return string|void
     */
    public static function remember($cookieName, $return = false)
    {
        $cookieName = strtolower($cookieName);

        if (!in_array($cookieName, ['author', 'mail', 'url'])) {
            return '';
        }

        $value = Cookie::get('_tarblog_remember_' . $cookieName);

        if ($return) {
            return $value;
        } else {
            echo htmlspecialchars($value);
        }
    }

    /**
     * 判断页面类型
     *
     * @param $type
     * @return bool
     */
    public function is($type)
    {
        return $this->type === $type;
    }

    /**
     * 显示 token 表单项
     */
    public function csrf_field()
    {
        echo '<input type="hidden" name="_token" value="' . $this->_token() . '">';
    }

    /**
     * 页面标题
     *
     * @param array|null $defines
     * @param string $before
     * @param string $end
     */
    public function archiveTitle($defines = NULL, $before = ' &raquo; ', $end = '')
    {
        if ($this->_archiveTitle) {
            $define = '%s';
            if (is_array($defines) && !empty($defines[$this->type])) {
                $define = $defines[$this->type];
            }

            echo $before . sprintf($define, $this->_archiveTitle) . $end;
        }
    }

    /**
     * 关键词
     *
     * @return mixed|null
     */
    public function _search()
    {
        return $this->request->get('s');
    }

    /**
     * 一是防止跨站请求伪造，二是防止重复提交
     *
     * @return string
     */
    public function _token()
    {
        return Token::generate();
    }
}