<?php


namespace app\equip\service;


class Csv
{

    //导出csv文件
    public function put_csv(&$data, $titleList = array(), $fileName = '')
    {
        ini_set("max_execution_time", "3600");
        $csvData = '';

        // 标题
        $nums = count($titleList);
        for ($i = 0; $i < $nums - 1; $i++)
        {
            $csvData .= '"' . $titleList[$i] . '",';
        }
        $csvData .= '"' . $titleList[$nums - 1] . "\"\r\n";

        foreach ($data as $key => $row)
        {
            $i = 0;
            foreach ($row as $_key => $_val)
            {
                $_val = str_replace("\"", "\"\"", $_val);
                if ($i < ($nums - 1))
                {
                    $csvData .= '"' . $_val . '",';
                }
                elseif ($i == ($nums - 1))
                {
                    $csvData .= '"' . $_val . "\"\r\n";
                }
                $i++;
            }
            unset($data[$key]);
        }

        $csvData = mb_convert_encoding($csvData, "cp936", "UTF-8");
        $fileName = empty($fileName) ? date('Y-m-d-H-i-s', time()) : $fileName;
        $fileName = $fileName . '.csv';
        header("Content-type:text/csv;");
        header("Content-Disposition:attachment;filename=" . $fileName);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $csvData;
        exit();
    }

    // csv导入,支持10W+数据
    public function input_csv($filename,$field,$table) {
        //$field 打散为数组
        $fieldArr = explode(',', $field);
        //CSV转数组 $excelData
        $content = trim(file_get_contents($filename));
        $excelData = explode("\n",$content); //把字符串打散为数组
        //删除第一行标题
        array_splice($excelData, 0, 1);

        // 将这个大量数据（10W+）的数组分割成5000一个的小数组。这样就一次批量插入5000条数据。mysql 是支持的。
        $chunkData = array_chunk($excelData ,5000);
        $count = count($chunkData);
        for ($i = 0; $i < $count; $i++) {
            $insertRows = array();
            foreach($chunkData[$i] as $value){
                //转码，有中文不要用这种方式，会出乱码
                //$string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gbk');
                $string = trim(strip_tags($value));//转码
                $v = explode(',', $string); //把字符串打散为数组
                $row = array();
                for($j=0;$j<count($fieldArr);$j++){
                    $row[$fieldArr[$j]] = $v[$j];
                }
                $sqlString = "('".implode( "','", $row )."')"; //把数组元素组合为字符串 批量
                $insertRows[] = $sqlString;
            }
            $result = $this->addData($table,$insertRows,$field); //批量将sql插入数据库。
        }
        return $result;
    }



}