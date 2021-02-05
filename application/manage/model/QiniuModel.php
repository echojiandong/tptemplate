<?php
namespace app\manage\Model;

// use Think\Model;
// namespace app\models;
use think\Model;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
// Vendor('Qiniu.Storage.UploadManager');
// Vendor('Qiniu.Storage.BucketManager');

class QiniuModel extends Model
{
    /*
     * 七牛AK和七牛SK 
     */
    // public $accessKey = 'kjNUqdU_tC8DekJNTzNd_8u9H35F8wdz-IFkWvBn';
    // public $secretKey = 'j_oi5caraElgKhY2F5RtQHa5I8vR--zazvR3Em5g';
    public $accessKey = 'PEe4FPr5SCILVbQ3T8mKw4TL4mk5PolFFzGuV4E0';
    public $secretKey = 'XTt-clIWF8uDPaV9oRoYZiwFeeOjY1MGqZX_p-tf';
    // public $accessKey = '52WwVlofuaNdzMv-jXb9L7XkiFgaFTt2dr2nIl98';
    // public $secretKey = 'Xb_QlbvP1giDBbvpKLBWuL30lvNQAmKdnOr8FM1W';

    /*
     * 资源地址
     */
    // public $QnUrl='http://od8ad4458.bkt.clouddn.com/';
    public $QnUrl='http://ydtvideoimg.rjt-stirling.com/';
    /*
     * 空间名称
     */
    public $bucket='ydtvideoimg';
    // public $bucket='yikaoaa';

    /*
     * 生成随机名
     */
    public function getNewfilename($file='',$filename){
        return $file.substr(microtime(),2,6).rand(100,999).substr($filename,strrpos($filename,'.'));
    }

    /*
     * 获取七牛上传凭证
     */
    public function getQnToken($qiniuSpace){
        require_once __DIR__.'/autoload.php';
        $auth = new Auth($this->accessKey, $this->secretKey);
        $bucket = $qiniuSpace;
        $uploadToken = $auth->uploadToken($bucket);
        return $uploadToken;
    }

    /*
     * 文件上传到七牛
     */
    public function uploadFile($token,$filePath,$fileName,$qiniuSpaceHost){
        require_once __DIR__.'/autoload.php';
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token, $fileName, $filePath);
        if ($err !== null) {
            return false;
            //return $err;
        } else {
            return $qiniuSpaceHost.$ret['key'];
        }
    }

    /*
    * 删除七牛资源中的文件
    */
    public function delFile($src,$qiniuSpace){
        require_once __DIR__.'/autoload.php';
        // require_once __DIR__.'/vendor/autoload.php';
        $auth = new Auth($this->accessKey, $this->secretKey);
        $bucketMgr = new BucketManager($auth);
        $bucket = $qiniuSpace;
        $fileName=str_replace($this->QnUrl,'',$src);
        $err = $bucketMgr->delete($bucket, $fileName);
        if ($err !== null) {
            return false;
        } else {
            return "Success!";
        }
    }
}