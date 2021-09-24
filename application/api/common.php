<?php
use think\exception\HttpResponseException;

/**
 * 返回封装后的 API 数据到客户端
 * @access protected
 * @param mixed  $msg    提示信息
 * @param mixed  $data   要返回的数据
 * @param int    $code   错误码，默认为0
 * @param string $type   输出类型，支持json/xml/jsonp
 * @param array  $header 发送的 Header 信息
 * @return void
 * @throws HttpResponseException
 */
function result($msg, $data = null, $code = 0, $type = null, array $header = [])
{
    $result = [
        'code' => $code,
        'msg'  => $msg,
        'time' => request()->server('REQUEST_TIME'),
        'data' => $data,
    ];
    // 如果未设置类型则自动判断
    $type = $type ? $type : (request()->param(config('var_jsonp_handler')) ? 'jsonp' : 'json');

    if (isset($header['statuscode'])) {
        $code = $header['statuscode'];
        unset($header['statuscode']);
    } else {
        //未设置状态码,根据code值判断
        $code = $code >= 1000 || $code < 200 ? 200 : $code;
    }

    $response = response()->create($result, $type, $code)->header($header);
    throw new HttpResponseException($response);
}