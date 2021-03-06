<?php
/**
 * 助手函数
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 10:54
 */

use Core\Container\Manager as App;
use Utils\URLGenerator;

if (!function_exists('app')) {
    /**
     * 调用App(Container Manager)
     *
     * @param mixed|null $name
     * @param mixed ...$params
     * @return App|mixed|null
     */
    function app($name = null, ...$params)
    {
        if (is_null($name))
            return App::getInstance();

        return App::getInstance()->make($name, ...$params);
    }
}

if (!function_exists('get_option')) {
    /**
     * 获取option
     *
     * @param $key
     * @param null $default
     * @param int $uid
     * @return mixed
     */
    function get_option($key, $default = null, $uid = 0)
    {
        return app('options')->get($key, $default, $uid);
    }
}

if (!function_exists('set_option')) {
    /**
     * 设置option
     *
     * @param $key
     * @param $value
     * @param int $uid
     */
    function set_option($key, $value, $uid = 0)
    {
        app('options')->set($key, $value, $uid);
    }
}

if (!function_exists('route')) {
    /**
     * 获取路由对应URL
     *
     * @param $name
     * @param null $params
     * @return string
     */
    function route($name, $params = null)
    {
        return URLGenerator::route($name, $params);
    }
}

if (!function_exists('siteUrl')) {
    /**
     * 获取网站URL
     *
     * @param string $ext
     * @return string
     */
    function siteUrl($ext = '')
    {
        return URLGenerator::getFullUrl($ext);
    }
}

if (!function_exists('dateX')) {
    /**
     * 时间按格式显示
     *
     * @param int $format
     * @param null $time
     * @return false|string
     */
    function dateX($format = 0, $time = null)
    {
        if (is_null($time)) $time = time();

        // 预置格式
        if (is_numeric($format)) {
            switch ($format) {
                case 0:
                    $format = 'Y-m-d H:i:s';
                    break;
                case 1:
                    $format = 'Y-m-d H:i';
                    break;
                case 2:
                    $format = 'Y-m-d';
                    break;
            }
        }

        return is_int($time) ? date($format, $time) : date($format, strtotime($time));
    }
}

if (!function_exists('auto_fill_time')) {
    /**
     * 自动填充created_at和updated_at的数组
     * 数据库用
     *
     * @param string $created_at
     * @param string $updated_at
     * @return array
     */
    function auto_fill_time($created_at = 'created_at', $updated_at = 'updated_at')
    {
        if (!is_null($created_at)) {
            $data[$created_at] = dateX();
        }

        if (!is_null($updated_at)) {
            $data[$updated_at] = dateX();
        }

        return $data ?? [];
    }
}

if (!function_exists('friendly_datetime')) {
    /**
     * 友好时间显示
     *
     * @param $datetime
     * @return string
     */
    function friendly_datetime($datetime)
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60)
            return $diff . '秒前';
        elseif ($diff >= 60 && $diff < 3600)
            return floor($diff / 60) . '分钟前';
        elseif ($diff >= 3600 && $diff < 86400)
            return floor($diff / 3600) . '小时前';
        else
            return $datetime;
    }
}

if (!function_exists('format_size')) {
    /**
     * 字节转友好大小显示
     *
     * @param int $size
     * @param bool $return
     * @return string|void
     */
    function format_size($size, $return = false)
    {
        $i = 0;

        while ($size >= 1024) {
            $size /= 1024;
            if (++$i > 4) break;
        }

        $names = ['byte', 'KB', 'MB', 'GB', 'TB'];

        $unit = $i == 0 && $size > 1 ? $names[$i] . 's' : $names[$i];

        $show = sprintf("%.2f", $size) . $unit;

        if ($return)
            return $show;

        echo $show;
    }
}

if (!function_exists('redirect')) {
    /**
     * 重定向
     *
     * @param string $url
     * @param callable|null $with 跳转前执行
     */
    function redirect($url, $with = null)
    {
        ob_end_clean();
        ob_start();
        if (is_callable($with)) call_user_func($with);
        header('location:' . $url);
        die();
    }
}

if (!function_exists('back')) {
    /**
     * 返回上一页
     *
     * @param callable|null $with 跳转前执行
     */
    function back($with = null)
    {
        redirect(getenv("HTTP_REFERER"), $with);
    }
}

if (!function_exists('to_homepage')) {
    /**
     * 跳转到首页
     *
     * @param string|null $with
     */
    function to_homepage($with = null)
    {
        redirect(siteUrl(), $with);
    }
}

if (!function_exists('flash')) {
    /**
     * 闪存数据，下一次请求后清空
     *
     * @param $key
     * @param $value
     */
    function flash($key, $value)
    {
        app('session')->flash($key, $value);
    }
}

if (!function_exists('with_error')) {
    /**
     * 闪存错误数据
     *
     * @param array $errors
     */
    function with_error($errors = [])
    {
        $old_errors = app('session')->get('errors', []);

        $errors = array_merge($old_errors, \Utils\Arr::wrap($errors));

        flash('errors', $errors);
    }
}

if (!function_exists('with_input')) {
    /**
     * 闪存表单数据，用old()来取
     *
     * @param array $inputs
     */
    function with_input($inputs = [])
    {
        $old_inputs = app('session')->get('inputs', []);

        if (empty($inputs)) $inputs = array_filter(app('request')->post(), function ($v, $k) {
            return !in_array($k, ['_method', '_token']); // 去除非表单内容
        }, ARRAY_FILTER_USE_BOTH);

        $inputs = array_merge($old_inputs, $inputs);

        flash('inputs', $inputs);
    }
}

if (!function_exists('old')) {
    /**
     * 显示缓存到inputs的内容
     *
     * @param $key
     * @param null $default
     */
    function old($key, $default = null)
    {
        $inputs = app('session')->get('inputs', []);

        echo array_key_exists($key, $inputs) ? $inputs[$key] : $default;
    }
}

if (!function_exists('dump')) {
    /**
     * 调试用
     *
     * @param mixed ...$exp
     */
    function dump(...$exp)
    {
        echo '<pre>';
        var_dump(...$exp);
        echo '</pre>';
    }
}

if (!function_exists('dd')) {
    /**
     * 调试用
     *
     * @param mixed ...$exp
     */
    function dd(...$exp)
    {
        dump(...$exp);
        exit();
    }
}

if (!function_exists('json')) {
    /**
     * 编码数组为json文本并显示
     *
     * @param $data
     */
    function json($data)
    {
        echo json_encode($data);
    }
}

if (!function_exists('generate_unique_slug')) {
    /**
     * 生成一个未被占用的slug
     *
     * @param $info
     * @param $type
     * @param $id
     * @param string $raw
     * @param int $index
     * @return string
     */
    function generate_unique_slug($info, $type, $id = 0, $raw = '', $index = 0)
    {
        return \Utils\Str::generateUniqueSlug($info, $type, $id, $raw, $index);
    }
}

if (!function_exists('get_ip')) {
    /**
     * 获取客户端IP
     *
     * @return mixed|string
     */
    function get_ip()
    {
        //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $res = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
        return $res;
    }
}

if (!function_exists('del_dir')) {
    /**
     * 递归删除目录
     *
     * @param string $directory 目录
     */
    function del_dir($directory)
    {
        if (file_exists($directory)) {//判断目录是否存在，如果不存在rmdir()函数会出错
            if ($dir_handle = @opendir($directory)) {//打开目录返回目录资源，并判断是否成功
                while ($filename = readdir($dir_handle)) {//遍历目录，读出目录中的文件或文件夹
                    if ($filename != '.' && $filename != '..') {//一定要排除两个特殊的目录
                        $subFile = $directory . "/" . $filename;//将目录下的文件与当前目录相连
                        if (is_dir($subFile)) {//如果是目录条件则成了
                            del_dir($subFile);//递归调用自己删除子目录
                        }
                        if (is_file($subFile)) {//如果是文件条件则成立
                            unlink($subFile);//直接删除这个文件
                        }
                    }
                }
                closedir($dir_handle);//关闭目录资源
                rmdir($directory);//删除空目录
            }
        }
    }
}

if (!function_exists('copy_dir')) {
    /**
     * 复制文件夹
     * @param $source
     * @param $dest
     */
    function copy_dir($source, $dest)
    {
        if (!file_exists($dest)) mkdir($dest);
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_source = $source . '/' . $item;
            $_dest = $dest . '/' . $item;
            if (is_file($_source)) copy($_source, $_dest);
            if (is_dir($_source)) copy_dir($_source, $_dest);
        }
        closedir($handle);
    }
}

