<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 11:32
 */

namespace Utils;

use Core\Container\Manager as App;

class URLGenerator
{
    public static function withPrefix($path, $prefix)
    {
        $path = (0 === strpos($path, './')) ? substr($path, 2) : $path;
        return rtrim($prefix, '/') . '/' . str_replace('//', '/', ltrim($path, '/'));
    }

    public static function asset($request, $uri, $theme)
    {
        return self::getFullUrl(__THEME_DIR__ . '/' . $theme . '/' . $uri);
    }

    public static function route($name, $params = null)
    {
        $route = Route::getRouteByName($name);

        $uri = $route->getUri();

        if (empty($params))
            return siteUrl($uri);

        if (!is_array($params)) {
            $params = [$route->getParamsFromPatternUri()[0] => $params];
        }

        $keys = array_map(function ($item) {
            return '{' . $item . '}';
        }, array_keys($params));

        $values = array_values($params);

        // 假如没有开启地址重写，则加上index.php
        if (!get_option('rewrite')) $prefix = 'index.php';
        else $prefix = '';

        return self::getFullUrl($prefix . str_replace($keys, $values, $uri));
    }

    public static function getFullUrl($uri, $prefix = null)
    {
        $app = App::getInstance();

        if (is_null($prefix)) $prefix = $app->make('options')->get('siteUrl', '/');

        return $prefix . (substr($uri, 0, 1) == '/' || substr($prefix, -1) == '/' ? $uri : '/' . $uri);
    }

    /**
     * 数组转query
     *
     * @param array $array
     * @param string $prefix 前缀默认开启
     * @param string $suffix 后缀只在不为空的情况下存在
     * @return string
     */
    public static function array2query(array $array, $prefix = '', $suffix = '')
    {
        $query = '';

        foreach ($array as $key => $val) {
            if (empty($val)) continue;
            if (empty($query)) $query = $key . '=' . $val;
            else $query .= '&' . $key . '=' . $val;
        }

        if (!empty($query)) $query .= $suffix;

        return $prefix . $query;
    }
}