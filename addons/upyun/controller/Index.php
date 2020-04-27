<?php

namespace addons\upyun\controller;

use app\common\model\Attachment;
use think\addons\Controller;
use think\Config;


use Upyun\Config as UpyunConfig;
use Upyun\Upyun as UpyunClient;

/**
 * 又拍云上传
 *
 */
class Index extends Controller
{

    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    public function upload()
    {
        Config::set('default_return_type', 'json');
        if (!session('admin') && !$this->auth->id) {
            $this->error("请登录后再进行操作");
        }
        $config = get_addon_config('upyun');

        $file = $this->request->file('file');
        if (!$file || !$file->isValid()) {
            $this->error("请上传有效的文件");
        }
        $fileInfo = $file->getInfo();

        $filePath = $file->getRealPath() ?: $file->getPathname();

        preg_match('/(\d+)(\w+)/', $config['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$config['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);

        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $md5 = md5_file($filePath);
        $search = ['{year}', '{mon}', '{month}', '{day}', '{filemd5}', '{suffix}', '{.suffix}'];
        $replace = [date("Y"), date("m"), date("m"), date("d"), $md5, $suffix, '.' . $suffix];
        $object = ltrim(str_replace($search, $replace, $config['savekey']), '/');

        $mimetypeArr = explode(',', strtolower($config['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //检查文件大小
        if (!$file->checkSize($size)) {
            $this->error("起过最大可上传文件限制");
        }

        //验证文件后缀
        if ($config['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $config['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('上传格式限制'));
        }

        $savekey = '/' . $object;

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //先上传到本地
        $splInfo = $file->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $extparam = $this->request->post();
            $filePath = $splInfo->getRealPath() ?: $splInfo->getPathname();

            $sha1 = sha1_file($filePath);
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'admin_id'    => session('admin.id'),
                'user_id'     => $this->auth->id,
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
                'extparam'    => json_encode($extparam),
            );
            $attachment = Attachment::create(array_filter($params), true);

            try {
                $config = new UpyunConfig($config['bucket'], $config['operator'], $config['password']);
                $upyun = new UpyunClient($config);
                $upyun->write($savekey, file_get_contents($filePath));
            } catch (\Exception $e) {
                $attachment->delete();
                unlink($filePath);
                $this->error("上传失败");
            }
            $url = '/' . $object;

            //上传成功后将存储变更为upyun
            $attachment->storage = 'upyun';
            $attachment->save();

            $this->success("上传成功", null, ['url' => $url]);
        } else {
            $this->error('上传失败');
        }
        return;
    }

    //上传异步通知
    public function notify()
    {
        $url = $this->request->post("url");
        $code = $this->request->post("code");
        $message = $this->request->post("message");
        $sign = $this->request->post("sign");
        $time = $this->request->post("time");
        $extparam = $this->request->post("ext-param");
        if ($url && $code && $message && $time && $sign) {
            $config = get_addon_config('upyun');
            $arr = [$code, $message, $url, $time, $config['formkey']];
            if ($extparam) {
                $arr[] = $extparam;
            }
            if ($sign == md5(implode('&', $arr))) {
                $admin_id = $user_id = 0;
                if ($extparam) {
                    list($admin_id, $user_id) = explode('_', $extparam);
                }
                $params = array(
                    'admin_id'    => (int)$admin_id,
                    'user_id'     => (int)$user_id,
                    'filesize'    => $this->request->param("file_size", 0),
                    'imagewidth'  => $this->request->param("image-width", 0),
                    'imageheight' => $this->request->param("image-height", 0),
                    'imagetype'   => $this->request->param("image-type", ''),
                    'imageframes' => $this->request->param("image-frames", 1),
                    'mimetype'    => $this->request->param("mimetype", ''),
                    'url'         => $url,
                    'uploadtime'  => $time,
                    'storage'     => 'upyun'
                );
                Attachment::create($params);
                echo "success";
            } else {
                echo "failure";
            }
        } else {
            echo "failure";
        }
        return;
    }

}
