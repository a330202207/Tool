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

/**
 * 分配红包方法
 * @param int $money 钱总数,单位分
 * @param int $packs 红包个数
 * @return string 分配结果
 */
function getRandLucky($money, $packs)
{
    if ($money < ($packs * 100)) {
        echo '钱太少了,每人红包至少1元以上.';
    }
    $luck = array_fill(0, $packs, 100);

    $money = $money - $packs * 100;

    $total = $packs - 1;
    for ($i = 0; $i < $money; $i++) {
        if ($total < 0) {
            $total = $packs - 1;
        }
        $j = rand(0, $total);
        $luck[$j] = $luck[$j] + 1;
        $total -= 1;
    }
    shuffle($luck);
    $sum = 0;
    foreach ($luck as $l) {
        $sum += $l;
    }
    return implode(',', $luck);
}