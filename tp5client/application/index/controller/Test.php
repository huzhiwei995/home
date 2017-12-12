<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Test extends Controller
{
	public function query()
	{	
		$mode = Db::name('tenmillion')->force('indexid')->field('id')->select();
		$json = json_encode($mode);
		echo $json;
		file_put_contents('./data.txt', $json);
	}
	public function dataapi()
	{
		$data = file_get_contents('./data.txt');
		$arr = json_decode($data,true);
		$count = count($arr);
		$milion = count($arr)/100;
		$result = array();
		$num = 0;
		for($i=1;$i<=$milion;$i++){
			for ($j=$num; $j < $num+100; $j++) { 
				$result[$i]['IMSIS'][]=$arr[$j]['id'];
			}
			$num=$num+100;
			$result[$i] = json_encode($result[$i]);
		}
		dump($result);exit;
	}
}