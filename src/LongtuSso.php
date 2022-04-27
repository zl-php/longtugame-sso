<?php
namespace Longtugame\Sso;

use Illuminate\Config\Repository;

class LongtuSso {

    protected $config = [];

    public function __construct(Repository $config)
    {
        $this->config = $config->get('sso');
    }

    // 解密
    public function decrypt($code)
    {
        //解密参数组装
        $parms = [
            "app_id"      =>  $this->config['app_id'],
            "app_key"     =>  $this->config['app_key'],
            "act"         =>  "decode",
            "code"        =>  $code,
        ];

        $data = array_merge(['mod' => 'sso', 'signature' => $this->getSign($parms)], $parms);

        $rsp = json_decode($this->post($this->config['url'], $data), true);

        if (!isset($rsp['rcode']) || $rsp['rcode'] !== 'y050406') {
            throw new \Exception(
                'get result error:'.$rsp['rcode'].' - '.$rsp['msg']
            );
        }

        return $rsp;
    }

    private function getSign($arr_params = [])
    {
        ksort($arr_params);
        reset($arr_params);

        $_str_signSrc = urldecode(http_build_query($arr_params));

        if (get_magic_quotes_gpc())
            $_str_signSrc = stripslashes($_str_signSrc);

        return md5($_str_signSrc);
    }

    private function post($url, $params = [], $headers = [])
    {
        $client   = new \GuzzleHttp\Client();

        $rsp = $client->request('POST', $url, ['headers' => $headers, 'form_params' => $params, 'http_errors' => false]);

        return $rsp->getBody()->getContents();
    }
}