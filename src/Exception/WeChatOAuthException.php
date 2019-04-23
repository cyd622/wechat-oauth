<?php
/**
 * Description: WeChatOAuthException 异常信息
 * Project: WeChatOAuth
 * Author: Ciel (luffywang622@gmail.com)
 * Created on: 2019/04/22 11:26
 * Created by PhpStorm.
 */

namespace Cyd622\WeChatOAuth\Exception;

use Exception;
use Throwable;

class WeChatOAuthException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        // 如果数数字,去读取错误表信息
        if (is_numeric($message) && $code == 0) {
            $code = $message;
            $message = ErrorCode::getMessage($code);
        }
        parent::__construct($message, $code, $previous);
    }
}