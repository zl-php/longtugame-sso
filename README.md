<h1 align="center">longtugame sso</h1>

基于 Laravel 开发 longtugame sso 功能模块

## 安装
```bash
composer require longtugame/sso
```

## Laravel 项目内使用
```
# 发布配置文件
php artisan vendor:publish --provider="Longtugame\Sso\LongtuSsoServiceProvider"

# 控制器使用
use Longtugame\Sso\Facades\LongtuSso;

# 返回一个数组格式，只包含用户数据
$user = LongtuSso::setCode($code)->decrypt();

```
