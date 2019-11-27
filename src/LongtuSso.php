<?php
/**
 * FILE: Sso.php.
 * User: zhoulei1@longtugame.com
 * Date: 2019/11/27 10:29
 */

namespace Longtugame\Sso;

use Curl\Curl;
use Illuminate\Config\Repository;


class LongtuSso {

    /**
     * sso 配置文件
     * @var mixed
     */
    protected $config;

    protected $client;


    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('sso');

        $this->client = new Curl();
    }

    /**
     * 账号解密
     *
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     * @throws \ErrorException
     */
    public function decrypt($code)
    {
        $client = $this->client;

        //解密参数组装
        $parameter = [
            "app_id"      =>  $this->config['app_id'],
            "app_key"     =>  $this->config['app_key'],
            "act"         =>  "decode",
            "code"        =>  $code,
        ];

        $decrypt_post_data = array_merge(['mod' => 'sso', 'signature' => $this->sign($parameter)],$parameter);

        $response = $client->post($this->config['url'], $decrypt_post_data);

        if ($client->error)
            return $this->export(0, $client->errorMessage);

        if($response->rcode != 'y050406' || empty($response->code))
            return $this->export(0, '解密失败或参数被篡改');

        $client->close();

        return $this->export(1, '解密成功', $response->code);
    }

    /**
     * 绑定账号到应用
     *
     * @param $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function bind($email)
    {
        $client = $this->client;

        //绑定sso参数组装
        $parameter = [
            "app_id"    =>  $this->config['app_id'],
            "app_key"   =>  $this->config['app_key'],
            "act"       =>  "bind",
            'user_mail' =>  $email,
            "app_identify_code" =>  json_encode([[ 'uname_inapp' => $this->config['app_id'],'identify_inapp' => encrypt($email)]])
        ];

        $bind_get_data = array_merge(['mod' => 'sso', 'signature' => $this->sign($parameter)],$parameter);

        $response = $client->get($this->config['url'], $bind_get_data);

        if ($client->error)
            return $this->export(0, $client->errorMessage);

        if($response->rcode != 'y130101')
            return $this->export(0, $response->msg);

        //关闭curl
        $client->close();

        return $this->export(1, $response->msg);
    }

    /**
     * 参数签名
     *
     * @param array $arr_params
     * @return string
     */
    private function sign($arr_params = [])
    {
        ksort($arr_params);
        reset($arr_params);

        $_str_signSrc = urldecode(http_build_query($arr_params));

        if (get_magic_quotes_gpc())
            $_str_signSrc = stripslashes($_str_signSrc);

        return md5($_str_signSrc);
    }

    /**
     * 返回数据
     *
     * @param $code
     * @param $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    private function export($code, $message, $data = [])
    {
        return response()->json(['code' => $code, 'message' => $message, 'data' => $data]);
    }

}