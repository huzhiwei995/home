<?php

namespace app\index\controller;

use think\Controller;
//该文件为tp5框架下开发，放入其他框架部分框架方法可能不兼容,请注意,还有命名空间和类名
class Token extends Controller
{
    //签名可以让用户请求服务器端返回，也可以给客户端一份有签名方法的php文件，减少每次加密请求(随意)
    //解密最好在服务器端解密
    /**
     * 签名
     * @param  array $params API调用的请求参数集合的关联数组，不包含sign参数
     * @param  string $secret 签名的密钥
     * @return string 返回参数签名值
     */
    public function getSignature($params, $secret = '')
    {
        if (is_array($params)) {
            //时效性签名，不添加则没有时效性，所有的错误码自定义
            if (!array_key_exists('timestamp', $params)) {
                $data = ['code' => 10007, 'msg' => '缺少timestamp参数'];
                return json_encode($data);
            }
            if ($params['appkey'] == '') {
                $data = ['code' => 10006, 'msg' => '缺少appkey参数'];
                return json_encode($data);
            }
            if ($secret == '') {
                $data = ['code' => 10001, 'msg' => '缺少secret参数'];
                return json_encode($data);
            }
            $str = '';
            ksort($params);
            foreach ($params as $k => $v) {
                $str .= "$k=$v";
            }
            $str .= $secret;
            $str = strtoupper(sha1($str));
            $data = ['code' => 1, 'msg' => '成功获取签名', 'data' => $str];
            return json_encode($data);
        } else {
            $data = ['code' => 10002, 'msg' => '传递的参数需为数组形式'];
            return json_encode($data);
        }
    }

    /**
     * 签名解密
     * @param  json $arr json格式的数组
     * @return Array 返回没有sign参数的数组
     */
    public function DecryptSign($serverArray)
    {
        //时效性签名，不添加则没有时效性，所有的错误码自定义
        if (time() - $serverArray['timestamp'] > 7200) {
            $data = ['code' => 10005, 'msg' => '你的签名已过期'];
            return $data;
        }
        $file = config('signApp');
        if ($serverArray['appkey'] == $file['appkey']) {
            $clientSign = $serverArray['sign'];
            unset($serverArray['sign']);
            ksort($serverArray);
            $str = '';
            foreach ($serverArray as $k => $v) {
                $str .= "$k=$v";
            }
            $str .= $file['secret'];
            $seviceSign = strtoupper(sha1($str));
            if ($seviceSign == $clientSign) {
                $data = ['code' => 1, 'msg' => '签名验证成功', 'data' => $serverArray];
                return $data;
            } else {
                $data = ['code' => 10010, 'msg' => '签名验证失败'];
                return $data;
            }
        } else {
            $data = ['code' => 10086, 'msg' => 'appkey错误', $file];
            return $data;
        }
    }

    //假设客户端请求接口的方法,调用签名，发送数据给服务器端的rechange方法
    public function test()
    {
        //配置中读取实现定义好的appkey和secret
        $config = config("signApp");
        $arr = [
            "appkey" => $config['appkey'],
            "timestamp" => time(),
            "mouth" => 123,
            'orderId' => 1928301920,
            "userName" => '大爱是',
            'devNumber' => 123123123,
        ];
        //加密
        $sign = $this->getSignature($arr, $config['secret']);
        $sign = json_decode($sign, true);
        if ($sign['code'] != 1) {
            dump($sign);
            exit;
        }
        $arr['sign'] = $sign['data'];
        dump($arr);
        exit;
        $json = json_encode($arr);
        $url = 'http://localhost/tp5client/public/index.php/index/index/rechange';//路径根据情况自定义自定义
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($json))
        );
        $result = curl_exec($ch);
        curl_close($ch);
        dump($result);//打印观察
        exit;
    }

    //假设服务器端接收请求数据，验证签名，接收test参数，处理完返回数据
    public function rechange()
    {
        $serverArray = input('post.');
        $arr = $this->DecryptSign($serverArray);
        //如果解密成功，执行接下来的操作
        if ($arr['code'] == 1) {
            echo "ok";
        } else {
            echo "error";
        }
        //操作完，执行返回
        $dev = $arr['data'];
        $josn = json_decode($dev['dev_number'], true);
        dump($arr);
        exit;
    }
}
