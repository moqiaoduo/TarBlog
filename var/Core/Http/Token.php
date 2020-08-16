<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 22:44
 */

namespace Core\Http;

use Utils\Str;

class Token
{
    private static $token;

    /**
     * 生成token并记录到session中
     *
     * @return string
     */
    public static function generate()
    {
        // 缓存token 本次请求都用它 不重复生成
        if (!is_null(self::$token))
            return self::$token;

        // 下次请求过后token将失效，以防止被重复使用
        app('request')->session()->flash('tarblog_csrf_token', $token = Str::random(32));

        return self::$token = $token;
    }

    /**
     * 验证token
     *
     * @param $token
     * @return bool
     */
    public static function verify($token)
    {
        if (is_null(self::$token))
            self::$token = $verify = app('request')->session('tarblog_csrf_token');
        else
            $verify = self::$token;

        return $verify == $token;
    }
}