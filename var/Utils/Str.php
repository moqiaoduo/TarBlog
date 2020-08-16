<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 12:48
 */

namespace Utils;

class Str
{
    public static function limit($content, $num = 150, $symbol = '...')
    {
        $content = strip_tags($content);

        if (mb_strlen($content) > $num) {
            $content = mb_substr($content, 0, $num) . $symbol;
        }

        return $content;
    }

    public static function toCamel($str, $separator = '_')
    {
        $str = $separator . str_replace($separator, " ", strtolower($str));
        return ltrim(str_replace(" ", "", ucwords($str)), $separator);
    }

    public static function toUnderline($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    public static function random($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $password;
    }

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
    public static function generateUniqueSlug($info, $type, $id = 0, $raw = '', $index = 0)
    {
        $slug = empty($raw) ? ($raw = str_replace(['.', ' ', '/', '\\'], '-', $info)) : $raw . '-' . $index;
        switch ($type) {
            case 'attachment':
            case 'page':
            case 'post':
                $exists = DB::table('contents')->where('slug', $slug)
                    ->where('cid', '<>', $id)->exists();
                break;
            case 'tag':
            case 'category':
                $exists = DB::table('metas')->where('slug', $slug)->where('type', $type)
                    ->where('mid', '<>', $id)->exists();
                break;
            default:
                $exists = false;
        }
        if ($exists) $slug = self::generateUniqueSlug(null, $type, $id, $raw, $index + 1);
        return $slug;
    }
}