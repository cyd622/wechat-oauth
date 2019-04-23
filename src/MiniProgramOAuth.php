<?php
/**
 * Description: MiniProgramOAuth
 * Project: WeChatOAuth
 * Author: Ciel (luffywang622@gmail.com)
 * Created on: 2019/04/22 11:26
 * Created by PhpStorm.
 */

namespace Cyd622\WeChatOAuth;

use Cyd622\WeChatOAuth\Exception\WeChatOAuthException;

class MiniProgramOAuth
{
    private $appId;
    private $secret;
    private $openId;
    private $unionId;
    private $sessionKey;
    const CODE2SESSION_URL = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";

    public function __construct($appId = null, $secret = null)
    {
        $this->appId = $appId;
        $this->secret = $secret;
        return $this;
    }

    /**
     * 初始化配置
     * @param $appId
     * @param $secret
     * @return $this
     */
    public function init($appId, $secret)
    {
        $this->appId = $appId;
        $this->secret = $secret;
        return $this;
    }


    /**
     * 通过code获取sessionKey
     * @param $code
     * @return  string
     * @throws WeChatOAuthException
     */
    public function getSessionKey($code)
    {
        $url = sprintf(self::CODE2SESSION_URL, $this->appId, $this->secret, $code);
        $response = $this->httpRequest($url);
        // 抛出异常信息
        if (!isset($response['session_key'])) {
            throw new WeChatOAuthException($response['errmsg'], $response['errcode']);
        }

        $this->sessionKey = $response['session_key'];
        $this->openId = $response['openid'];
        $this->unionId = isset($response['unionid']) ? $response['unionid'] : null;
        return $this->sessionKey;
    }

    /**
     * 通过加密的数据拿到openId或者unionId
     * @param $encryptedData
     * @param $iv
     * @return string
     * @throws WeChatOAuthException
     */
    public function getUserInfo($encryptedData, $iv)
    {
        $dataCrypt = new WXBizDataCrypt($this->appId, $this->sessionKey);

        return $dataCrypt->decrypt($encryptedData, $iv);
    }

    /**
     * @return mixed
     */
    public function getOpenId()
    {
        return $this->openId;
    }

    /**
     * @return mixed
     */
    public function getUnionId()
    {
        return $this->unionId;
    }

    private function httpRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if ($output === FALSE) {
            return false;
        }
        curl_close($curl);
        return json_decode($output, JSON_UNESCAPED_UNICODE);
    }
}