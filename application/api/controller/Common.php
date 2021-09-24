<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\library\Upload;
use app\common\model\Area;
use app\common\model\Version;
use fast\Random;
use think\Config;
use think\Hook;

/**
 * 公共接口
 */
class Common extends Api
{
    protected $noNeedLogin = ['init'];
    protected $noNeedRight = '*';

    /**
     * 加载初始化
     * @param string $version 版本号
     * @param string $lng     经度
     * @param string $lat     纬度
     */
    protected function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');

            //配置信息
            $upload = Config::get('upload');
            //如果非服务端中转模式需要修改为中转
            if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
                //临时修改上传模式为服务端中转
                set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

                $upload = \app\common\model\Config::upload();
                // 上传信息配置后
                Hook::listen("upload_config_init", $upload);

                $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
            }

            $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
            $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => $upload,
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 文件上传
     * @ApiTitle    (文件上传)
     * @ApiSummary  (文件上传)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/upload)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="file", type="file", required=true, description="文件")
     * @ApiReturnParams   (name="url", type="string", required=true, sample="相对地址")
     * @ApiReturnParams   (name="fullurl", type="string", required=true, sample="完整地址")
     * @ApiReturn   ({
    "code": 1,
    "msg": "上传成功",
    "time": "1631762977",
    "data": {
    "url": "/uploads/20210916/997372b7b6614ea2cb6b63024896a920.jpg",
    "fullurl": "http://blt_seat.test/uploads/20210916/997372b7b6614ea2cb6b63024896a920.jpg"
    }
    })
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        //必须设定cdnurl为空,否则cdnurl函数计算错误
        Config::set('upload.cdnurl', '');
        $chunkid = $this->request->post("chunkid");
        if ($chunkid) {
            if (!Config::get('upload.chunking')) {
                $this->error(__('Chunk file disabled'));
            }
            $action = $this->request->post("action");
            $chunkindex = $this->request->post("chunkindex/d");
            $chunkcount = $this->request->post("chunkcount/d");
            $filename = $this->request->post("filename");
            $method = $this->request->method(true);
            if ($action == 'merge') {
                $attachment = null;
                //合并分片文件
                try {
                    $upload = new Upload();
                    $attachment = $upload->merge($chunkid, $chunkcount, $filename);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
            } elseif ($method == 'clean') {
                //删除冗余的分片文件
                try {
                    $upload = new Upload();
                    $upload->clean($chunkid);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            } else {
                //上传分片文件
                //默认普通上传文件
                $file = $this->request->file('file');
                try {
                    $upload = new Upload($file);
                    $upload->chunk($chunkid, $chunkindex, $chunkcount);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
        } else {
            $attachment = null;
            //默认普通上传文件
            $file = $this->request->file('file');
            try {
                $upload = new Upload($file);
                $attachment = $upload->upload();
            } catch (UploadException $e) {
                $this->error($e->getMessage());
            }

            $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
        }

    }

    /**
     * 管理员密码检测
     * @ApiTitle    (管理员密码检测)
     * @ApiSummary  (管理员密码检测)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/checkManager)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="admin_pass", type="string", required=true, description="管理员密码")
     * @ApiReturn   ({
    "code": 1,
    "msg": "通过",
    "time": "1631930958",
    "data": null
    })
     */
    public function checkManager(){
        if($this->request->post('admin_pass') == config('site.admin_pass')){
            session('admin_pass',$this->request->post('admin_pass'));
            $this->success("通过");
        }
        $this->error("管理员密码错误");
    }

}
