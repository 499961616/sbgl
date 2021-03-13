<?php


namespace app\equip\service;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    /**
     * Created by PhpStorm.
     * User: Linliangfeng
     * Date: 2021/03/01
     * Time: 11:31
     *
     * ━━━━━━━━━神兽出没━━━━━━━━━
     *
     *　　　　　　　　┏┓　　　┏┓+ +
     *　　　　　　　┏┛┻━━━┛┻┓ + +
     *　　　　　　　┃　　　　　　　┃ 　
     *　　　　　　　┃　　　━　　　┃ ++ + + +
     *　　　　　　 ████━████ ┃+
     *　　　　　　　┃　　　　　　　┃ +
     *　　　　　　　┃　　　┻　　　┃
     *　　　　　　　┃　　　　　　　┃ + +
     *　　　　　　　┗━┓　　　┏━┛
     *　　　　　　　　　┃　　　┃　　　　　　　　　　　
     *　　　　　　　　　┃　　　┃ + + + +
     *　　　　　　　　　┃　　　┃　　　　Code is far away from bug with the animal protecting　　　　　　　
     *　　　　　　　　　┃　　　┃ + 　　　　神兽保佑,代码无bug　　
     *　　　　　　　　　┃　　　┃
     *　　　　　　　　　┃　　　┃　　+　　　　　　　　　
     *　　　　　　　　　┃　 　　┗━━━┓ + +
     *　　　　　　　　　┃ 　　　　　　　┣┓
     *　　　　　　　　　┃ 　　　　　　　┏┛
     *　　　　　　　　　┗┓┓┏━┳┓┏┛ + + + +
     *　　　　　　　　　　┃┫┫　┃┫┫
     *　　　　　　　　　　┗┻┛　┗┻┛+ + + +
     *
     * ━━━━━━━━━感觉萌萌哒━━━━━━━━━
     */
        public function exportExcel($fileName = '设备信息', $arr = [], $headAr = [], $keyAr = [])
    {
        // 计算所需表头数量
        $count = count($headAr)-1;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('宋体');

        // 设置列
        $rowAr = self::setRowAr($count);
        foreach ($rowAr as $k => $v) {
            if($k > $count) break;
            $sheet->setCellValue($v.'1', $headAr[$k]);
        }

        // 写入值
        foreach ($arr as $k => $v) {
            foreach ($rowAr as $ke => $ve){
                if($ke > $count) break;
                $sheet->setCellValue($ve.($k+2), $v[$keyAr[$ke]]);      // 给单元格设置值
                $spreadsheet->getActiveSheet()->getColumnDimension($ve)->setWidth(14); // 固定列宽,看着更整齐
            }
        }
        // 冻结首行
//        $spreadsheet->getActiveSheet()->freezePaneByColumnAndRow(0,2);

        // 在输出Excel前，缓冲区中处理BOM头
        ob_end_clean();
        ob_start();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.date('_Ymd_Hi',time()).'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        // 删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }

        /**
         * [setRowAr 设置列]
         * @param int $count 列总数
         * @return array
         * @author llf <499961616@qq.com>
         * */
        public function setRowAr($count = 26)
    {
        $indData = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $indData = explode(',',$indData);
        $curCount = 26;
        for ($i = 0; $i <26 ; $i++) {
            for ($j = 0; $j < 26 ; $j++) {
                if($curCount >= $count) return $indData;
                $indData[] = $indData[$i].$indData[$j];
                $curCount++;
            }
        }
        return $indData;
    }

}