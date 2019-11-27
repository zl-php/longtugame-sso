<?php
/**
 * FILE: Sso.php.
 * User: zhoulei1@longtugame.com
 * Date: 2019/11/27 10:29
 */

namespace Longtugame\Sso;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use GuzzleHttp\Exception\RequestException;

class LongtuSso {

    /**
     * sso 配置文件
     * @var mixed
     */
    protected $config     = [];

    protected $httpClient = null;

    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('sso');

        $this->httpClient = new Client();
    }

    /**
     * 账号解密
     *
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function decrypt($code)
    {
        //解密参数组装
        $parameter = [
            "app_id"      =>  $this->config['app_id'],
            "app_key"     =>  $this->config['app_key'],
            "act"         =>  "decode",
            "code"        =>  $code,
        ];

        $decrypt_post_data = array_merge(['mod' => 'sso', 'signature' => $this->sign($parameter)],$parameter);

        try {

            $response = $this->post($this->config['url'], $decrypt_post_data);

        } catch (RequestException $e) {

            if ($e->hasResponse())
                return $this->export(0, $e->getResponse());

            return $this->export(0, $e->getRequest());
        }

        $result = mb_convert_encoding($response->getBody(), 'utf-8', 'gb2312');

        $result = json_decode($result, true);

        if($result['rcode'] != 'y050406' || empty($result['code']))
            return $this->export(0, '解密失败或参数被篡改');

        return $this->export(1, '解密成功', $result['code']);
    }

    /**
     * 绑定账号到应用
     *
     * @param $email
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function bind($email)
    {
        //绑定sso参数组装
        $parameter = [
            "app_id"    =>  $this->config['app_id'],
            "app_key"   =>  $this->config['app_key'],
            "act"       =>  "bind",
            'user_mail' =>  $email,
            "app_identify_code" =>  json_encode([[ 'uname_inapp' => $this->config['app_id'],'identify_inapp' => encrypt($email)]])
        ];

        $bind_get_data = array_merge(['mod' => 'sso', 'signature' => $this->sign($parameter)],$parameter);

        try {

            $response = $this->get($this->config['url'], $bind_get_data);

        } catch (RequestException $e) {

            if ($e->hasResponse())
                return $this->export(0, $e->getResponse());

            return $this->export(0, $e->getRequest());
        }

        $result = mb_convert_encoding($response->getBody(), 'utf-8', 'gb2312');

        $result = json_decode($result, true);

        if($result['rcode'] != 'y130101')
            return $this->export(0, $result['msg']);

        return $this->export(1, $result['msg']);
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
     * get请求
     * @param $endpoint
     * @param array $query
     * @param array $headers
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function get($endpoint, $query = [], $headers = [])
    {
        return $this->httpClient->request('get', $endpoint, [
            'headers' => $headers,
            'query'   => $query,
        ]);
    }

    /**
     * post请求
     *
     * @param $endpoint
     * @param $data
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function post($endpoint, $data, $options = [])
    {
        if (!is_array($data)) {
            $options['body'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        return $this->httpClient->request('post', $endpoint, $options);
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