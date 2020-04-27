<?php

namespace addons\upyun;

use think\Addons;
use think\Loader;
use Upyun\Config as UpyunConfig;
use Upyun\Upyun as UpyunClient;

/**
 * 又拍云上传
 */
class Upyun extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 添加命名空间
     */
    public function appInit()
    {
        //添加支付包的命名空间
        Loader::addNamespace('Upyun', ADDON_PATH . 'upyun' . DS . 'library' . DS . 'Upyun' . DS);
    }

    /**
     *
     * @return mixed
     */
    public function uploadConfigInit(&$upload)
    {
        $config = $this->getConfig('upyun');
        $config = $config ? $config : [];
        $config['bucket'] = isset($config['bucket']) ? $config['bucket'] : '';
        $multiple = isset($config['multiple']) ? $config['multiple'] : false;
        $savekey = isset($config['savekey']) ? $config['savekey'] : '';
        $config['save-key'] = isset($config['save-key']) ? $config['save-key'] : $savekey;
        $expiration = time() + (isset($config['expire']) ? $config['expire'] : 600);
        $config['expiration'] = isset($config['expiration']) ? $config['expiration'] : $expiration;
        $notifyurl = isset($config['notifyurl']) ? $config['notifyurl'] : '';
        $returnurl = isset($config['returnurl']) ? $config['returnurl'] : '';
        if ($notifyurl) {
            $config['notify-url'] = $notifyurl;
        } else {
            unset($config['notify-url']);
        }
        if ($returnurl) {
            $config['return-url'] = $returnurl;
        } else {
            unset($config['return-url']);
        }

        //设置允许的附加字段
        $allowfields = [
            'bucket', 'save-key', 'expiration', 'date', 'content-md5', 'notify-url', 'return-url', 'content-secret', 'content-type', 'allow-file-type', 'content-length-range',
            'image-width-range', 'image-height-range', 'x-gmkerl-thumb', 'x-gmkerl-type', 'apps', 'b64encoded', 'ext-param'
        ];
        $params = array_intersect_key($config, array_flip($allowfields));
        $policy = base64_encode(json_encode($params));
        $signature = md5($policy . '&' . (isset($config['formkey']) ? $config['formkey'] : ''));
        $multipart = [
            'policy'    => $policy,
            'signature' => $signature,
        ];
        $admin_id = (int)session('admin.id');
        $user_id = (int)cookie('uid');

        $params['ext-param'] = "{$admin_id}_{$user_id}";
        $multipart = array_merge($multipart, $params);
        if ($config['uploadmode'] == 'client') {
            $upload = [
                'cdnurl'    => isset($config['cdnurl']) ? $config['cdnurl'] : '',
                'uploadurl' => isset($config['uploadurl']) ? $config['uploadurl'] : url('ajax/upload'),
                'bucket'    => $config['bucket'],
                'maxsize'   => isset($config['maxsize']) ? $config['maxsize'] : '',
                'mimetype'  => isset($config['mimetype']) ? $config['mimetype'] : '',
                'multipart' => $multipart,
                'multiple'  => $multiple,
            ];
        } else {
            $upload = array_merge($upload, [
                'cdnurl'    => $config['cdnurl'],
                'uploadurl' => addon_url('upyun/index/upload'),
                'maxsize'   => $config['maxsize'],
                'mimetype'  => $config['mimetype'],
                'multiple'  => $config['multiple'] ? true : false,
            ]);
        }
    }

    /**
     * 附件删除后
     */
    public function uploadDelete($attachment)
    {
        $config = $this->getConfig();
        if ($attachment['storage'] == 'upyun' && isset($config['syncdelete']) && $config['syncdelete']) {
            $configObj = new UpyunConfig($config['bucket'], $config['operator'], $config['password']);
            $upyun = new UpyunClient($configObj);
            //同步删除又拍云文件
            $ret = $upyun->delete($attachment->url);
            //如果是服务端中转，还需要删除本地文件
            if ($config['uploadmode'] == 'server') {
                $filePath = ROOT_PATH . 'public' . str_replace('/', DS, $attachment->url);
                if ($filePath) {
                    @unlink($filePath);
                }
            }
        }
        return true;
    }
}
