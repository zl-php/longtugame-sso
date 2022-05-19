<?php
namespace Longtugame\Sso;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;

class LongtuSso {

    protected $code;
    protected $config = [];
    protected $httpClient = null;

    public function __construct(Repository $config)
    {
        $this->config = $config->get('sso');
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function decrypt()
    {
        $response = $this->httpPost($this->config['url'], $this->genData());
        $result = json_decode($response->getBody()->getContents(), true);

        if ($result['rcode'] !== 'y050406' || empty($result['code']['user_id'])) {
            throw new \Exception('Failed to user info:'. json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        return $result['code'];
    }

    /**
     * @return array
     */
    protected function genData()
    {
        $params = [
            "app_id" =>  $this->config['app_id'],
            "app_key"=>  $this->config['app_key'],
            "act"    =>  "decode",
            "code"   =>  $this->code
        ];

        return array_merge([
            'mod' => 'sso',
            'signature' => $this->genSignature($params)
        ], $params);
    }

    /**
     * @param $arr_params
     * @return string
     */
    protected function genSignature($arr_params = [])
    {
        ksort($arr_params);
        reset($arr_params);

        $_str_signSrc = urldecode(http_build_query($arr_params));

        if (get_magic_quotes_gpc())
            $_str_signSrc = stripslashes($_str_signSrc);

        return md5($_str_signSrc);
    }

    /**
     * @param $url
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function httpPost($url, array $data = [])
    {
        return (new Client())->request('POST', $url, ['form_params' => $data]);
    }
}
