<?php

namespace Appserver\Utils;

require_once 'Qiniu' . DIRECTORY_SEPARATOR . 'autoload.php';

use Qiniu\Auth,
      Qiniu\Storage\UploadManager,
      Qiniu\Storage\BucketManager;

class QiniuClient
{
    private $auth;
    private $bucket = 'yd-app';
    private $config;
    const imgModel = 'imageView2';

    public function __construct($di)
    {
        $this->config = $di['sysconfig']['qiniu'];
        $this->auth = new Auth($this->config['accessKey'], $this->config['secretKey']);
    }

    /**
     * 上传图片
     * @param  [type] $imgName [文件名]
     * @param  [type] $content [二进制流]
     * @return [type]          [description]
     */
    public function uploadImage($imgName, $content)
    {
        $token = $this->auth->uploadToken($this->bucket);
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->put($token, $imgName, $content);

        return $this->response($err, $imgName);
    }

    /**
     * 删除图片
     * @param  [type] $imgName [文件名]
     * @return [type]          [description]
     */
    public function delImage($imgName)
    {
        $bucketMgr = new BucketManager($this->auth);
        $err = $bucketMgr->delete($this->bucket, $imgName);
        
        return $this->response($err);
    }

    public function fetchImage($url, $imageName)
    {
        $bucketMgr = new BucketManager($this->auth);
        return $bucketMgr->fetch($url, $this->bucket, $imageName);
    }

    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    public function getImgUrl($imgName, $imgModel = 0, $with = '', $height = '')
    {
        $extModel = $imgModel ? '?' . self::imgModel . '/' . $imgModel : '';
        $extWith = $imgModel && $with ? '/w/' . $with : '';
        $extHeight = $imgModel && $height ? '/h/' . $height : '';
        return $this->config['resourceUrl'] . '/' . $imgName . $extModel . $extWith . $extHeight;
    }

    public function response($err, $msg = '')
    {
        $errCode = $err ? $err->response->statusCode : 0;
        $msg = $err ? $err->response->error : $msg;

        return array(
            'errCode' => $errCode,
            'msg' => $msg
        );
    }
}