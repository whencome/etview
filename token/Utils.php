<?php
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 2019/3/8
 * Time: 18:07
 */

namespace etview\token;


class Utils
{

    /**
     * 将字符串格式化为php变量表达式.
     *
     * @param string $v 变量字面量.
     *
     * @return string
     */
    public static function getVariable($v)
    {
        $parts = explode('.', $v);
        $var = '$'.$parts[0];
        unset($parts[0]);
        if (count($parts) > 0) {
            $var .= "['".implode("']['", $parts)."']";
        }
        return $var;
    }

}
