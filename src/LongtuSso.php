<?php
/**
 * FILE: Sso.php.
 * User: zhoulei1@longtugame.com
 * Date: 2019/11/27 10:29
 */

namespace Longtugme\Sso;

use Illuminate\Config\Repository;

class LongtuSso {

    protected $config;
    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('sso');
    }


    public function test()
    {

        return $this->config;

    }

}