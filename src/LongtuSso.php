<?php
namespace Longtugame\Sso;

use Illuminate\Config\Repository;

class LongtuSso {

    protected $config = [];

    public function __construct(Repository $config)
    {
        $this->config = $config->get('sso');
    }

    /**
     * 解密方法
     *
     * @param $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function decrypt($code)
    {
        //解密参数组装
        $parms = [
            "app_id"      =>  $this->config['app_id'],
            "app_key"     =>  $this->config['app_key'],
            "act"         =>  "decode",
            "code"        =>  $code,
        ];

        $data = array_merge([
                'mod' => 'sso',
                'signature' => $this->getSign($parms)
            ], $parms);

        $result = $this->post($this->config['url'], $data);

        if ($result['rcode'] !== 'y050406' || empty($result['code']['user_id'])) {
            throw new \Exception('Failed to user info:'. json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        return $result['code'];
    }

    /**
     * 参数签名
     *
     * @param array $arr_params
     * @return string
     */
    protected function getSign($arr_params = [])
    {
        ksort($arr_params);
        reset($arr_params);

        $_str_signSrc = urldecode(http_build_query($arr_params));

        if (get_magic_quotes_gpc())
            $_str_signSrc = stripslashes($_str_signSrc);

        return md5($_str_signSrc);
    }

    /**
     * http post
     *
     * @param $url
     * @param array $params
     * @param array $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function post($url, $params = [], $headers = [])
    {
        $client   = new \GuzzleHttp\Client();

        $response = $client->request('POST', $url, ['headers' => $headers, 'form_params' => $params, 'http_errors' => false]);
        $result = json_decode($response->getBody()->getContents(), true);

        return $result;
    }
}