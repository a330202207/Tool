<?php

/**
 * 多维数组去重
 * @param array
 * @return array
 */
function super_unique($array)
{
    $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

    foreach ($result as $key => $value) {
        if (is_array($value)) {
            $result[$key] = super_unique($value);
        }
    }

    return $result;
}

/**
 * 多维数组多个字段排序
 * @param array
 * @return array
 * sortArrByManyField($arr, 'id', SORT_ASC, 'name', SORT_ASC, 'age', SORT_DESC);
 */
function sortArrByManyField()
{
    $args = func_get_args();
    if (empty($args)) {
        return null;
    }
    $arr = array_shift($args);
    foreach ($args as $key => $value) {
        if (is_string($value)) {
            $temp = [];
            foreach ($arr as $k => $v) {
                $temp[$k] = $v[$value];
            }
            $args[$key] = $temp;
        }
    }
    //引用值
    $args[] = &$arr;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}