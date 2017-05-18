<?php  
// 连接mongodb  
function conn($dbhost, $dbname, $dbuser, $dbpasswd){  
    $server = 'mongodb://'.$dbuser.':'.$dbpasswd.'@'.$dbhost.'/'.$dbname;  
    try{  
        $conn = new MongoClient($server);  
        $db = $conn->selectDB($dbname);  
    } catch (MongoException $e){  
        throw new ErrorException('Unable to connect to db server. Error:' . $e->getMessage(), 31);  
    }  
    return $db;  
}
// 插入坐标到mongodb  
function add($dbconn, $tablename, $longitude, $latitude, $name){  
    $index = array('loc'=>'2dsphere');  
    $data = array(  
            'loc' => array(  
                    'type' => 'Point',  
                    'coordinates' => array(doubleval($longitude), doubleval($latitude))  
            ),  
            'name' => $name  
    );  
    $coll = $dbconn->selectCollection($tablename);  
    $coll->ensureIndex($index);  
    $result = $coll->insert($data, array('w' => true));  
    return (isset($result['ok']) && !empty($result['ok'])) ? true : false;  
}  
  
// 搜寻附近的坐标  
function query($dbconn, $tablename, $longitude, $latitude, $maxdistance, $limit=10){  
    $param = array(  
        'loc' => array(  
            '$nearSphere' => array(  
                '$geometry' => array(  
                    'type' => 'Point',  
                    'coordinates' => array(doubleval($longitude), doubleval($latitude)),   
                ),  
                '$maxDistance' => $maxdistance*1000  
            )  
        )  
    );  
  
    $coll = $dbconn->selectCollection($tablename);  
    $cursor = $coll->find($param);  
    $cursor = $cursor->limit($limit);  
      
    $result = array();  
    foreach($cursor as $v){  
        $result[] = $v;  
    }    
  
    return $result;  
}  
//数据自行修改
//1.创建lbs集合存放地点坐标
/*use lbs;  
  
db.lbs.insert(  
    {  
        loc:{  
            type: "Point",  
            coordinates: [113.332264, 23.156206]  
        },  
        name: "广州东站"  
    }  
)  
  
db.lbs.insert(  
    {  
        loc:{  
            type: "Point",  
            coordinates: [113.330611, 23.147234]  
        },  
        name: "林和西"  
    }  
)  
  
db.lbs.insert(  
    {  
        loc:{  
            type: "Point",  
            coordinates: [113.328095, 23.165376]  
        },  
        name: "天平架"  
    }  
)  */
//创建地理位置索引（必须加地理位置索引，数据库和数据可修改）
/*db.lbs.ensureIndex(  
    {  
        loc: "2dsphere"  
    }  
)  */
//演示PHP代码，首先需要在mongodb的lbs中创建用户和执行auth,mondo自身设置账号密码php连接才完整
//创建用户，给予权限
/*use lbs;
db.createUser(  
    {  
        "user":"root",  
        "pwd":"123456",  
        "roles":[]  
    }  
)  
  
db.auth(  
    {  
        "user":"root",  
        "pwd":"123456"  
    }  
)*/
$db = conn('localhost','lbs','root','123456');  
  
// 随机插入100条坐标纪录  
for($i=0; $i<100; $i++){  
    $longitude = '113.3'.mt_rand(10000, 99999);  
    $latitude = '23.15'.mt_rand(1000, 9999);  
    $name = 'name'.mt_rand(10000,99999);  
    add($db, 'lbs', $longitude, $latitude, $name);  
}  
  
// 搜寻一公里内的点  
$longitude = 113.323568;  
$latitude = 23.146436;  
$maxdistance = 1;  
$result = query($db, 'lbs', $longitude, $latitude, $maxdistance);  
print_r($result);  
?>  
