<?php

/**
 * @notes: 导出excel(csv)
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/9
 * @param array $data 导出数据
 * @param array $headlist 第一行,列名
 * @param $fileName 输出Excel文件名
 */
function csvExport(array $data = [], array $headlist = [], $fileName)
{

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
    header('Cache-Control: max-age=0');

    //打开PHP文件句柄,php://output 表示直接输出到浏览器
    $fp = fopen('php://output', 'a');

    //输出Excel列名信息
    foreach ($headlist as $key => $value) {
        //CSV的Excel支持GBK编码，一定要转换，否则乱码
        $headlist[$key] = iconv('utf-8', 'gbk', $value);
    }

    //将数据通过fputcsv写到文件句柄
    fputcsv($fp, $headlist);

    //计数器
    $num = 0;

    //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
    $limit = 100000;

    //逐行取出数据，不浪费内存
    $count = count($data);
    for ($i = 0; $i < $count; $i++) {

        $num++;

        //刷新一下输出buffer，防止由于数据过多造成问题
        if ($limit == $num) {
            ob_flush();
            flush();
            $num = 0;
        }

        $row = $data[$i];
        foreach ($row as $key => $value) {
            $row[$key] = iconv('utf-8', 'gbk', $value);
        }

        fputcsv($fp, $row);
    }
}

/**
 * @notes: 读取文件中内容写入到另一文件
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/10/4
 * @param $read_file_path 读取文件
 * @param $write_file_path 写入文件
 * @version: 1.0
 */
function readFileToWriteFile($read_file_path, $write_file_path)
{
    $is_exist = file_exists($read_file_path) or exit("There is no file");

    $file = fopen($read_file_path, "r") ;

    $data = [];

    $i = 0;

    //输出文本中所有的行，直到文件结束为止。
    while(! feof($file))
    {
        $row_data = json_decode(fgets($file),true);
        $data[$i] = $row_data;
        $i++;
    }

    fclose($file);

    foreach ($data as $key => $val) {
        file_put_contents($write_file_path, json_encode($val).PHP_EOL, FILE_APPEND);
    }
}