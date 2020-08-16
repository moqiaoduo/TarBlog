<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 11:27
 */

namespace Core\Routing;

use App\Base;
use Core\Container\Manager as App;
use Core\Http\Request;

class Router
{
    /**
     * 容器管理器
     *
     * @var App
     */
    private $app;

    /**
     * 路由数组
     *
     * @var Route[]
     */
    protected $routes;

    /**
     * 带名字路由数组
     *
     * @var Route[]
     */
    protected $nameRoutes;

    /**
     * 请求对象
     *
     * @var Request
     */
    protected $request;

    /**
     * path_info
     *
     * @var string
     */
    protected $pathInfo = null;

    /**
     * 初始化路由器
     * @param App $app
     * @param array $routes
     */
    public function __construct(App $app, $routes = [])
    {
        $this->routes = $routes;

        $this->app = $app;

        $this->request = $app->make('request');
    }

    /**
     * 获取路由
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * 设置路由
     *
     * @param Route[] $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;

        return $route;
    }

    public function add($uri, $action)
    {
        return $this->addRoute(new Route($uri, $action));
    }

    public function getPathInfo()
    {
        if (!is_null($this->pathInfo)) {
            return $this->pathInfo;
        }

        $options = $this->app->make('options');

        if (defined('__PATHINFO_ENCODING__')) {
            $pathInfo = $this->request->getPathInfo(__PATHINFO_ENCODING__, $options->charset);
        } else {
            $pathInfo = $this->request->getPathInfo();
        }

        return ($this->pathInfo = $pathInfo);
    }

    public function dispatch()
    {
        $success = false;

        $options = $this->app->make('options');

        $theme = $options->get('theme', 'default');

        $themeDir = __ROOT_DIR__ . __THEME_DIR__ . DIRECTORY_SEPARATOR . $theme;

        foreach ($this->routes as $route) {
            $params = $this->uriMatch($route, $this->getPathInfo());

            if ($params !== false) {
                $class = $route->getAction();
                $app = new $class($this->app, $theme, $themeDir, $params, $route);
                /* @var Base $app */
                $success = $app->execute();
                if ($success) {
                    $app->render();
                    break;
                }
            }
        }

        if (!$success) {
            http_response_code(404); // 设置HttpCode=404
            // 调用404页面
            if (file_exists($err_page = $themeDir . DIRECTORY_SEPARATOR . '404.php'))
                include $err_page;
            else
                showErrorPage("页面不存在");
        }
    }

    protected function uriMatch(Route $route, $uri)
    {
        // 去除uri首尾/
        $out_uri = substr($uri, 0, 1) == '/' ? substr($uri, 1) : $uri;
        $out_uri = substr($out_uri, -1) == '/' ? substr($out_uri, 0, strlen($out_uri) - 1) : $out_uri;

        $params = $route->getParamsFromPatternUri();

        // 先去除路由uri开头的/
        $in_uri = substr($route->getUri(), 0, 1) == '/' ?
            substr($route->getUri(), 1) : $route->getUri();
        // 再去除路由uri末尾的/
        $in_uri = substr($in_uri, -1) == '/' ? substr($in_uri, 0, strlen($in_uri) - 1) : $in_uri;
        // 再将.替换为\. （.在正则中匹配任意一个字符，为了匹配.，必须换为\.）
        $in_uri = str_replace('.', '\.', $in_uri);

        $all_pattern = $in_uri;

        foreach ($params as $param) {
            $optional = substr($param, 0, 1) == '?';
            $pattern = $optional ? '.*?' : '.+?';
            if ($optional)
                $all_pattern = str_replace('/{' . $param . '}', '/?(' . $pattern . ')', $all_pattern);
            $all_pattern = str_replace('{' . $param . '}', '(' . $pattern . ')', $all_pattern);
        }

        // 匹配uri并获取参数
        preg_match("`^$all_pattern$`", $out_uri, $matches);

        // 未匹配到时，返回false
        if (count($matches) == 0) return false;

        // 去除第一个结果，第一个结果是uri本身
        array_shift($matches);

        for ($i = 0; $i < count($matches); $i++) {
            // 判断路由是否符合正则表达式，之前考虑第一遍匹配就过滤的，但是出了点问题，所以改为二次匹配
            if (!$route->ifParamsMatch($params[$i], $matches[$i])) return false;

            // 多级目录参数，按 / 分割成数组
            if ($isMultiDivParam = $route->isMultiDivParam($params[$i])) {
                $matches[$i] = explode("/", $matches[$i]);
            }

            // 假如匹配到的参数中间有/，但是不是多级目录{directory}的话，理应为不匹配的路由
            if (strpos($matches[$i], "/") !== false && !$isMultiDivParam) return false;

        }

        return array_combine($params, $matches);
    }

    public function refreshRoutesNameList()
    {
        $this->nameRoutes = [];

        foreach ($this->routes as $route) {
            $name = $route->getName();
            if (empty($name)) continue;
            $this->nameRoutes[$name] = $route;
        }

        return $this->nameRoutes;
    }

    public function getRouteByName($name)
    {
        return $this->nameRoutes[$name] ?? null;
    }
}