# Laravel Theme 

### [主题开发文档:https://doc.uhaveshop.vip/theme](https://doc.uhaveshop.vip/theme)

## 关于
Laravel Theme是一个主题机制解决方案，为需要建立自己的生态系统的开发人员，有了它，你可以建立一个类似wordpress的生态系统。它可以帮助您如下:

* 加载主题基于服务注册。
* 通过命令行，主题开发人员可以轻松快速地构建主题并将主题上传到主题市场。
* 提供主题编写器包支持。在创建的主题中单独引用composer。
* 以事件监控的方式执行主题的安装、卸载、启用、禁用逻辑。易于开发人员扩展。
* 插槽式主题市场支持，通过修改配置文件，开发者可以无缝连接到自己的主题市场。
* 附带一个基本的主题市场，开发人员可以上传主题并对其进行审查。

## 适用环境

```yml
"php": "^7.3|^8.0",
"ext-zip": "*",
"laravel/framework": "^8.12",
"spatie/laravel-enum": "^2.5"
```


## installation

* Step 1
```shell
composer require sanlilin/laravel-theme
```

* Step 2
```php
php artisan vendor:publish --provider="Sanlilin\LaravelTheme\Providers\ThemeServiceProvider"
```














