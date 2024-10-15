<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 16:20
 */

namespace Core;

use Utils\DB;

class Validate
{
    private $data;

    private $nullable_fields = [];

    private $messages = [
        'required' => ':name 不能为空',
        'equal' => ':name 必须等于 :value',
        'email' => ':name 必须为一个有效的电子邮箱地址',
        'min' => ':name 必须大于 :param0|:name 最少输入 :param0 位',
        'max' => ':name 必须小于 :param0|:name 最多输入 :param0 位',
        'confirm' => '密码必须与确认密码相符',
        'in' => ':name 必须在指定的值中选择',
        'unique' => ':name 已经存在'
    ];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function make($rules, $messages = [])
    {
        foreach ($rules as $names => $rule) {
            $names = explode('|', $names);
            $field = $names[0];
            $name = count($names) > 1 ? $names[1] : $field;
            $rule = explode('|', $rule);
            foreach ($rule as $r) {
                $r = explode(':', $r);
                $tr = $r[0];
                $params = explode(',',$r[1] ?? '');
                if (($message = $this->validate($field, $name, $tr, $params,
                        $this->data[$field], $messages[$field] ?? "")) !== true) {
                    return [false, $field, $message];
                }
            }
        }

        return [true, null, null];
    }

    /**
     * 获取默认提示消息
     *
     * @param string $rule
     * @param array $replaces
     * @param int $mode
     * @return string
     */
    private function getDefaultMessage($rule, $replaces = [], $mode = 0)
    {
        $texts = explode("|", $this->messages[$rule]);

        $text = $texts[$mode];

        foreach ($replaces as $key => $val) {
            $text = str_replace(':' . $key, $val, $text);
        }

        return $text;
    }

    /**
     * 进行校验
     *
     * @param $field
     * @param $name
     * @param $rule
     * @param $params
     * @param $value
     * @param $message
     * @return bool|string
     */
    private function validate($field, $name, $rule, $params, $value, $message)
    {
        $mode = 0;

        switch ($rule) {
            case 'required':
                $pass = !is_null($value);
                break;
            case 'equal':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                $pass = $value == $params[0];
                break;
            case 'email':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/', $value, $match);
                $pass = count($match) > 0;
                break;
            case 'number':
                $pass = is_numeric($value);
                break;
            case 'min':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                if (($params[1] ?? 'str') == 'num') {
                    $pass = $value >= $params[0];
                } else {
                    $pass = mb_strlen($value) >= $params[0];
                    $mode = 1;
                }
                break;
            case 'max':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                if (($params[1] ?? 'str') == 'num') {
                    $pass = $value <= $params[0];
                } else {
                    $pass = mb_strlen($value) <= $params[0];
                    $mode = 1;
                }
                break;
            case 'between':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                if (($params[2] ?? 'str') == 'num') {
                    $pass = $value >= $params[0] && $value <= $params[1];
                } else {
                    $len = mb_strlen($value);
                    $pass = $len >= $params[0] && $len <= $params[1];
                    $mode = 1;
                }
                break;
            case 'confirm':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                $pass = $value == $this->data['confirm_' . $field];
                break;
            case 'in':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                $pass = in_array($value, $params);
                break;
            case 'unique':
                if (in_array($field, $this->nullable_fields)) {
                    $pass = true;
                    break;
                }
                $pass = !DB::table($params[0])->where($params[1] ?? $field, $value)->exists();
                break;
            case 'nullable':
                $pass = true;
                $this->nullable_fields[] = $field;
                break;
            default: // 不识别的规则直接pass
                $pass = true;
        }

        if (!$pass) {
            if (empty($message)) {
                $message = $this->getDefaultMessage($rule, ['name' => $name, 'value' => $value] +
                    $this->arrayParamsWithKey($params), $mode);
            }
            return $message;
        }

        return true;
    }

    /**
     * 转换数组
     *
     * @param array $params
     * @return array
     */
    private function arrayParamsWithKey($params)
    {
        $data = [];

        for ($i = 0; $i < count($params); $i++) {
            $data['param' . $i] = $params[$i];
        }

        return $data;
    }
}
