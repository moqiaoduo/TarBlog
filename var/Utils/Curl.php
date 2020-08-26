<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/19
 * Time: 18:26
 */

namespace Utils;

class Curl
{
    public static function get($url, $headers = [])
    {
        $header = array_merge([
            'Accept: application/json',
            'User-Agent: TarBlog-App'
        ], $headers);

        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $data = curl_exec($curl);

        curl_close($curl);


        return $data;
    }

    public static function post($url, $data = [], $headers = [])
    {
        $ch = curl_init();

        $header = array_merge([
            'Accept: application/json',
            'User-Agent: TarBlog-App'
        ], $headers);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // 设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }
}