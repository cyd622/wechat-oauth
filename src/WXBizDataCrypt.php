<?php
/**
 * Description: WXBizDataCrypt 解密数据
 * Project: WeChatOAuth
 * Author: Ciel (luffywang622@gmail.com)
 * Created on: 2019/04/22 11:32
 * Created by PhpStorm.
 */

namespace Cyd622\WeChatOAuth;
use Cyd622\WeChatOAuth\Exception\WeChatOAuthException;
use Cyd622\WeChatOAuth\Exception\ErrorCode;

class WXBizDataCrypt
{
    private $appId;
    private $sessionKey;

    /**
     * 构造函数
     * @param $appId
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     */
    public function __construct($appId, $sessionKey)
    {
        $this->appId = $appId;
        $this->sessionKey = $sessionKey;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param string $encryptedData 加密数据
     * @param string $iv 加密算法的初始向量
     * @return array $data 解密得到的明文
     * @throws WeChatOAuthException
     */
    public function decrypt($encryptedData, $iv)
    {
        if (strlen($this->sessionKey) != 24) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_SESSION_KEY);
        }

        if (strlen($iv) != 24) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_IV);
        }

        $aesKey = base64_decode($this->sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $data = json_decode($result, true);
        if ($data == NULL) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_BUFFER);
        }
        if ($data['watermark']['appid'] != $this->appId) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_UN_BELONG);
        }
        return $data;
    }
}