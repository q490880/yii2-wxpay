# yii2-wxpay
yii2微信支付组件

[CHANGE LOG](CHANGELOG.md)

Installation
--------------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require xplwechat/weixin
```

or add

```json
"xplwechat/weixin": "*"
```

to the `require` section of your composer.json.


Configuration
--------------------

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'wxpay'=>[
            'class'=>'xplwechat\weixin\Wxpay',
            'back_url'=> '回调地址',
            'app_id'=>'APPID',
            'mch_id'=>'MCHID',
            'key'=>'KEY',
            'app_secret'=>'APPSECRET',
            'ssl_cert_path'=>'../cert/apiclient_cert.pem',
            'ssl_key_path'=>'../cert/apiclient_key.pem',
            'curl_proxy_host'=>'0.0.0.0',
            'curl_proxy_port'=>0,
            'report_level'=>1,
        ],
    ],
];

[生成支付信息]
$result = \Yii::$app->wxpay->unifiedOrder([
                'attach'=>'扩展字段',
                'out_trade_no'=>'订单号',
                'price'=>'价格(分)',
                'body'=>'商品描述',
                'tag'=>'商品标签',
                'id'=>'商品ID',
            ])
将result通过json形式返给客户端即可

[回调代码]
--------------------
$result = false;
if($notifyData = \Yii::$app->wxpay->notify()){
	$result = "回调逻辑";
}
if($result){
	echo '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
}else{
	echo '<xml><return_code><![CDATA[FAIL]]></return_code></xml>';
}
```
