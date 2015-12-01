<?php 

namespace Appserver\Utils;

use  \Phalcon\Acl\Exception as E;

class ImgUpload
{
    const MAXIMUM_IMG_SIZE = 10099;
    const PORTION_IMG_UPLOAD = 10096;
    const FAILED_IMG_UPLOAD = 10093;
    const FAILED_IMG_TYPE = 10097;
    const NON_IMG = 10098;
    const FAILED_CREATE_DIR = 44444;

    public $max_size = '10000000';//设置上传文件大小
    public $errmsg = '';//错误信息 
    public $save_path;//上传文件保存路径 
    private $files;//提交的等待上传文件 
    private $file_type = array();//文件类型 
    private $ext = '';//上传文件扩展名 
    private $max_width = ''; //设置图片最大宽度
    private $max_height = ''; //设置图片最大高度

    /** 
    * 构造函数，初始化类 
    * @access public 
    * @param string $di 容器
    * @param string $save_path 上传的目标文件夹 
    */ 
    public function __construct($di, $width='1000', $height='')
    {
        $this->di=$di;
        $this->max_width = $width;
        $this->max_height = $height;
    }
    /** 
    * 上传文件 
    * @access public 
    * @param $files 等待上传的文件(表单传来的$_FILES['tmp_name']) 
    * @param string $path 文件上传的固定路径
    * @param string $re_path 不同的文件各自的路径
    * @return boolean 返回布尔值 
    */ 
    public function uploadFile($files, $path, $imageName, $re_path = '')
    {
        $this->save_path = dirname(dirname(dirname(__FILE__))) . '/public' . $path.'/'.$re_path . '/';

        $name = $files['name'];
        $type = $files['type'];
        $size = $files['size'];
        $tmp_name = $files['tmp_name'];
        $error = $files['error'];
        switch ($error) 
        {
            case 0 : $this->errmsg = '';
            break; 
            case 1 : $this->errmsg = self::MAXIMUM_IMG_SIZE;
            break; 
            case 2 : $this->errmsg = self::MAXIMUM_IMG_SIZE;
            break; 
            case 3 : $this->errmsg = self::PORTION_IMG_UPLOAD;
            break; 
            case 4 : $this->errmsg = self::NON_IMG;
            break; 
            case 5 : $this->errmsg = self::NON_IMG;
            break; 
            default : $this->errmsg = self::FAILED_IMG_UPLOAD;
            break;
        } 
        if($error == 0 && is_uploaded_file($tmp_name))
        {
            //检测文件大小 
            if($size > $this->max_size)
            { 
                return self::MAXIMUM_IMG_SIZE;
            }

            //缩放比例后存储 
            if($this->resizeImage($tmp_name, $this->max_width, $this->max_height, $imageName,'.jpg'))
                return $re_path. '/' . $imageName.'.jpg'; 
            else
                return self::FAILED_IMG_UPLOAD;
        }
        else
            return $this->errmsg;
    }

    /**
     * [resizeImage description]
     * @param  [type] $im        [源目标图片]
     * @param  [type] $maxwidth  [最大宽度]
     * @param  [type] $maxheight [最大高度]
     * @param  [type] $name      [图片名]
     * @param  [type] $filetype  [图片类型]
     * @param  [type] $tmp_name  [上传的文件的临时路径]
     * @return [type]            [成功ｔｒｕｅ]
     */
    function resizeImage($tmp_name,$maxwidth,$maxheight,$name,$filetype)
    {
        try
        {
            $img_info= getimagesize($tmp_name);
            if(!in_array($img_info['mime'], array('image/jpeg', 'image/png', 'image/bmp', 'image/gif')))
            {
                $this->errmsg = self::FAILED_IMG_TYPE;
                return FALSE;
            }
            //判断是否需要创建文件夹
            if(!$this->_createdir( $this->save_path))
            {
                return self::FAILED_CREATE_DIR;
            }
            $pic_width =$img_info[0];
            $pic_height = $img_info[1];
            if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
            {
                if($maxwidth && $pic_width>$maxwidth)
                {
                    $widthratio = $maxwidth/$pic_width;
                    $resizewidth_tag = true;
                }
                else
                    $resizewidth_tag = false;
             
                if($maxheight && $pic_height>$maxheight)
                {
                    $heightratio = $maxheight/$pic_height;
                    $resizeheight_tag = true;
                }
                else
                    $resizeheight_tag = false;
             
                if($resizewidth_tag && $resizeheight_tag)
                {
                    if($widthratio<$heightratio)
                        $ratio = $widthratio;
                    else
                        $ratio = $heightratio;
                }
             
                if($resizewidth_tag && !$resizeheight_tag)
                    $ratio = $widthratio;
                if($resizeheight_tag && !$resizewidth_tag)
                    $ratio = $heightratio;
             
                $newwidth = $pic_width * $ratio;
                $newheight = $pic_height * $ratio;
                $newim = imagecreatetruecolor($newwidth,$newheight);
                switch ($img_info[2])
                {
                    case 1 :
                        $images = imageCreateFromGif($tmp_name);
                    break;
                    case 2 :
                        $images = imageCreateFromJpeg($tmp_name);
                    break;
                    case 3 :
                        $images = imageCreateFromPng($tmp_name);
                    break;
                    case 6 :
                        $images = imageCreateFromBmp($tmp_name);
                    break;
                }
                imagecopyresampled($newim,$images,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
                $name = $this->save_path.$name.$filetype;
                imagejpeg($newim,$name);
                imagedestroy($newim);
            }
            else
            {
                $name = $this->save_path.$name.$filetype;
                if(!move_uploaded_file($tmp_name, $name))
                {
                    return false;
                }
            }
            return TRUE;

        }
        catch( E $e) 
            {
                return FALSE;
            }
    }

        /**
     * 创建目录
     * @param str $path 多级目录
     * @param int $mode 权限级别
     * @return boolean
     */
    public function _createdir($path, $mode = 0777)
    {
        if(!is_dir($path))
        {
            //true为可创建多级目录
            $re = mkdir($path, $mode, true);
            if($re)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return TRUE;
        }
    }
}