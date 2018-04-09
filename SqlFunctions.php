<?php
/**
 * @notes: 批量插入
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/9
 * @param $table string 表名
 * @param $arr   array 插入的数据
 * @return bool|string
 *
 * $arr = [['id' => 1, 'name' => 'test1'], ['id' => 2, 'name' => 'test2']]
 *
 * array2Insert('post', $arr)
 *
 * INSERT INTO `post`( 'id','name' ) values ('1','test1') , ('2','test2')
 */
function batchInsert($table, array $arr)
{
    $arrKeys = array_keys(array_shift($arr));

    if (empty($table) || !is_array($arr)) {
        return false;
    }

    $fields = implode(',', array_map(function($value) {
        return "`" . $value . "`";
    }, $arrKeys));

    foreach ($arr as $key => $val) {
        $arrValues[$key] = implode(',', array_map(function($value) {
            return "'" . $value . "'";
        }, $val));
    }

    $values = "(" . implode(') , (', array_map(function($value) {
            return $value;
        }, $arrValues)) . ")";

    $sql = "INSERT INTO `%s`( %s ) values %s ";

    $sql = sprintf($sql, $table, $fields, $values);
    return $sql;
}

/**
 * @notes: 批量删除
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/9
 * @param $table  string 表名
 * @param $data   array  待删除的数据，二维数组格式
 * @param $field  string 值不同的条件，默认为id
 * @return bool|string
 * $arr = [['id' => 1], ['id' => 2]]
 *
 * array2Delete('post', $arr, 'id')
 *
 * DELETE FROM `post`  WHERE `id` IN (`1`,`2`)
 */
function batchDelete($table, $data, $field)
{
    if (!is_array($data) || !$field) {
        return false;
    }

    $fields = array_column($data, $field);
    $fields = implode(',', array_map(function($value) {
        return "'".$value."'";
    }, $fields));

    $sql = 'DELETE FROM `%s` WHERE `%s` IN (%s)';

    $sql = sprintf($sql, $table, $field, $fields);
    return $sql;
}

/**
 * @notes: 批量更新
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/9
 * @param $table  string 表名
 * @param $data   array  待更新的数据，二维数组格式
 * @param $field  string 值不同的条件，默认为id
 * @param $params array  值相同的条件，键值对应的一维数组
 * @return bool|string
 * $data = [
 *      ['id' => 1, 'parent_id' => 100, 'title' => 'A', 'sort' => 1],
 *      ['id' => 2, 'parent_id' => 100, 'title' => 'A', 'sort' => 3]
 * ];
 *
 * batchUpdate('post', $data, 'id', ['parent_id' => 100, 'title' => 'A']);
 *
 * UPDATE `post` SET
 *      `id` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '2'
 *      END,
 *      `parent_id` = CASE `id`
 *          WHEN '1' THEN '100'
 *          WHEN '2' THEN '100'
 *      END,
 *      `title` = CASE `id`
 *          WHEN '1' THEN 'A'
 *          WHEN '2' THEN 'A'
 *      END,
 *      `sort` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '3'
 *      END
 * WHERE `id` IN ('1','2')  AND `parent_id` = '100' AND `title` = 'A'
 *
 * $data = [
 *      ['id' => 1, 'sort' => 1],
 *      ['id' => 2, 'sort' => 2]
 * ];
 *
 *
 * batchUpdate('post', $data, 'id');
 *
 * UPDATE `post` SET
 *      `id` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '2'
 *      END,
 *      `sort` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '3'
 *      END
 * WHERE `id` IN ('1','2')
 *
 * $data = [
 *      ['id' => 1, 'sort' => 1],
 *      ['id' => 2, 'sort' => 2]
 * ];
 *
 */
function batchUpdate($table, $data, $field, array $params = [])
{
    if (!is_array($data) || !$field || !is_array($params)) {
        return false;
    }

    $updates = parseUpdate($data, $field);
    $where = parseParams($params);

    // 获取所有键名为$field列的值，值两边加上单引号，保存在$fields数组中
    $fields = array_column($data, $field);
    $fields = implode(',', array_map(function($value) {
        return "'".$value."'";
    }, $fields));

    $sql = 'UPDATE `%s` SET %s WHERE `%s` IN (%s) %s';

    $sql = sprintf($sql, $table, $updates, $field, $fields, $where);

    return $sql;
}


/**
 * 将二维数组转换成CASE WHEN THEN的批量更新条件
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/9
 * @param $data array 二维数组
 * @param $field string 列名
 * @return string sql语句
 */
function parseUpdate($data, $field)
{
    $sql = '';
    $keys = array_keys(current($data));
    foreach ($keys as $column) {

        $sql .= sprintf("`%s` = CASE `%s` \n", $column, $field);
        foreach ($data as $line) {
            $sql .= sprintf("WHEN '%s' THEN '%s' \n", $line[$field], $line[$column]);
        }
        $sql .= "END,";
    }

    return rtrim($sql, ',');
}

/**
 * 解析where条件
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/9
 * @param $params
 * @return array|string
 */
function parseParams($params)
{
    $where = [];
    foreach ($params as $key => $value) {
        $where[] = sprintf("`%s` = '%s'", $key, $value);
    }

    return $where ? ' AND ' . implode(' AND ', $where) : '';
}