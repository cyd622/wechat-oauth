<?php
/**
 * Description: ErrorCode 错误码说明
 * Project: WeChatOAuth
 * Author: Ciel (luffywang622@gmail.com)
 * Created on: 2019/04/22 11:48
 * Created by PhpStorm.
 */

namespace Cyd622\WeChatOAuth\Exception;

class ErrorCode
{
    const ILLEGAL_AES_KEY = 41001;
    const ILLEGAL_IV = 41002;
    const ILLEGAL_BUFFER = 41003;
    const ILLEGAL_SESSION_KEY = 41004;
    const ILLEGAL_UN_BELONG = 41005;
    const ILLEGAL_CODE = 40029;
    const FREQ_LIMIT = 45011;

    public static function getMessage($code)
    {
        $list = [
            41001 => 'AES 解密失败',
            41002 => 'IV初始向量 非法',
            41003 => '解密后得到的buffer非法',
            41004 => 'sessionKey 非法',
            41005 => '解密后的buffer数据不归属该appId',
            40029 => 'code 无效',
            45011 => '频率限制，每个用户每分钟100次',
        ];
        if (in_array($code, array_keys($list))) {

        } else {
            return '系统错误';
        }
    }
}