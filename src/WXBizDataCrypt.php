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
     * @return string 解密得到的明文
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

        try {
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            mcrypt_generic_init($module, $aesKey, $aesIV);
            $decrypted = mdecrypt_generic($module, $aesCipher);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (\Exception $e) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_AES_KEY);
        }
        try {
            $result = $this->removeComplementText($decrypted);
        } catch (\Exception $e) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_BUFFER);
        }

        $data = json_decode($result, true);
        if ($data == NULL) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_BUFFER);
        }
        if ($data['watermark']['appid'] != $this->appId) {
            throw new WeChatOAuthException(ErrorCode::ILLEGAL_UN_BELONG);
        }
        return $data;
    }

    /**
     * 去除补位字符
     * @param $text
     * @return bool|string
     */
    private function removeComplementText($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}