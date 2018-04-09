<?php

/**
 * @notes: 将数组分割拼接成,形式字符串
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/8
 * @param array $arr
 * @param $valName
 * @return string
 * $arr = [[id => '1'], [id => '2'], [id => '3']];
 * return '1,2,3'
 */
function spliceStr(array $arr, $valName)
{
    $data = array_column($arr, $valName);
    $str = implode(',', $data);
    return $str;
}
