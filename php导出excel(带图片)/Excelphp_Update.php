<?php
/*
	在Thinkphp中使用php导出excel带图片
 */
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller
{
	public function daochu()
    {	
    	//引入文件
    	Vendor('PHPExcel.PHPExcel');
    	$excel = new \PHPExcel();
    	$excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    	$excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    	$objActSheet = $excel->getActiveSheet();
    	//导出几个字段就设几个字母，字母顺序看excel顶部字母对应
		$letter = array('A','B','C','D','E','F','G','H','I');
		//查询的字段
		$tableheader = array('用户id','open_id','nick_name','sex','Language','city','country','head_img_url','power');
		//对查询的字段进行字母的对应，同时设置高度，高度想不想同可以拿出自行填入参数,例："$letter[$i]"代替'I'
		for($i = 0;$i < count($tableheader);$i++) {
			$excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
			$objActSheet->getColumnDimension("$letter[$i]")->setWidth(40);
		}
		//查询数据库
		$stu = M('yy_users');
		$data = $stu -> select();
		//循环将数据放入
		foreach($data as $k=>$v){
			//这个不动默认
            $k +=2;
            //几个字段就几个填
            $objActSheet->setCellValue('A'.$k, $v['user_id']);    
            $objActSheet->setCellValue('B'.$k, $v['open_id']);
            $objActSheet->setCellValue('C'.$k, $v['nick_name']); 
            $objActSheet->setCellValue('D'.$k, $v['sex']);    
            $objActSheet->setCellValue('E'.$k, $v['language']);    
            $objActSheet->setCellValue('F'.$k, $v['city']);
            $objActSheet->setCellValue('G'.$k, $v['country']);
            // 图片生成
            $objDrawing[$k] = new \PHPExcel_Worksheet_Drawing();
            $objDrawing[$k]->setPath('D:\phpStudy\WWW\svn'.$v['head_img_url']);
            // 设置宽度高度
            $objDrawing[$k]->setHeight(80);//照片高度
            $objDrawing[$k]->setWidth(80); //照片宽度
            $objDrawing[$k]->setCoordinates('H'.$k);
            // 图片偏移距离
            $objDrawing[$k]->setOffsetX(12);
            $objDrawing[$k]->setOffsetY(12);
            $objDrawing[$k]->setWorksheet($excel->getActiveSheet());
            $objActSheet->setCellValue('I'.$k, $v['power']);
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(80); 
        }
		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");;
		//默认表名，自行修改
		header('Content-Disposition:attachment;filename="用户详情表.xls"');
		header("Content-Transfer-Encoding:binary");
		$write->save('php://output');
    }
}