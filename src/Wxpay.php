<?php
namespace xplwechat\weixin;
use xplwechat\weixin\lib\WxPayApi;
use xplwechat\weixin\lib\WxPayDataBase;
use xplwechat\weixin\lib\WxPayNotify;
use xplwechat\weixin\lib\WxPayUnifiedOrder;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class Wxpay extends Component {

    public $pay;
    public $app_id;
    public $mch_id;
    public $key;
    public $back_url;
    public $app_secret;
    public $ssl_cert_path;
    public $ssl_key_path;
    public $curl_proxy_host;
    public $curl_proxy_port;
    public $report_level;

    public function init()
    {
        parent::init();
        if (!isset($this->app_id)) {
            throw new InvalidConfigException('请先配置app_id');
        }
        if (!isset($this->mch_id)) {
            throw new InvalidConfigException('请先配置mch_id');
        }
        if (!isset($this->key)) {
            throw new InvalidConfigException('请先配置key');
        }
        if (!isset($this->app_secret)) {
            throw new InvalidConfigException('请先配置app_secret');
        }
        if (!isset($this->ssl_cert_path)) {
            throw new InvalidConfigException('请先配置ssl_cert_path');
        }
        if (!isset($this->back_url)) {
            throw new InvalidConfigException('请先配置back_url');
        }else{
            if(substr($this->back_url,'4') != 'http'){
                $this->back_url = \Yii::$app->request->hostInfo.$this->back_url;
            }
        }
    }

    public function unifiedOrder($goods){
        $input = new WxPayUnifiedOrder();
        $input->SetBody($goods['body']);
        $input->SetAttach($goods['attach']);
        $input->SetOut_trade_no($this->mch_id.date("YmdHis"));
        $input->SetTotal_fee($goods['price']);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($goods['tag']);
        $input->SetNotify_url($this->back_url);
        $input->SetTrade_type("APP");
        $input->SetProduct_id($goods['id']);

        $result = WxPayApi::unifiedOrder($input);
        if($result['return_code'] == 'SUCCESS'){
            $input = new WxPayUnifiedOrder();
            $input->SetPublicValue('appid',$result['appid']);
            $input->SetPublicValue('noncestr',$result['nonce_str']);
            $input->SetPublicValue('package','Sign=WXPay');
            $input->SetPublicValue('partnerid',$result['mch_id']);
            $input->SetPublicValue('prepayid',$result['prepay_id']);
            $input->SetPublicValue('timestamp',time());
            $result['sign'] = $input->MakeSign();
            $result['timestamp'] = (string)time();
            return $result;
        }
    }
    public function callBackResult($result='SUCCESS',$msg='OK'){
        $input = new WxPayUnifiedOrder();
        $input->SetPublicValue('return_code',$result);
        $input->SetPublicValue('return_msg',$msg);
        $input->SetPublicValue('sign',$input->MakeSign());
        return $input->ToXml($result);
    }

    public function notify(){
        try{
            $wxPayNotify = new WxPayNotify();
            $wxPayNotify->Handle(false);
            $values = $wxPayNotify->GetValues();
            if($values['return_code'] == 'SUCCESS'){
                return json_decode($values['return_msg'],true);
            }
            throw new Exception($values['return_msg']);
        }catch (\yii\db\Exception $e){
            \Yii::error('[wechat_callback_error]'.$e->getMessage(),'payment');
            return false;
        }
    }
}
