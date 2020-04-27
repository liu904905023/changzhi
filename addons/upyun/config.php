<?php

return array (
  0 => 
  array (
    'name' => 'bucket',
    'title' => 'bucket',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'match-file',
    'rule' => 'required',
    'msg' => '',
    'tip' => '服务名称',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'cdnurl',
    'title' => 'CDN地址',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'matchfile.wlzjedu.com',
    'rule' => 'required',
    'msg' => '',
    'tip' => '回事域名',
    'ok' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => 'uploadurl',
    'title' => '上传接口地址',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'https://v0.api.upyun.com/yourbucket',
    'rule' => 'required',
    'msg' => '',
    'tip' => '上传接口地址',
    'ok' => '',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => 'notifyurl',
    'title' => '回调通知地址',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'http://www.yoursite.com/addons/upyun/index/notify',
    'rule' => '',
    'msg' => '',
    'tip' => '回调通知地址',
    'ok' => '',
    'extend' => '',
  ),
  4 => 
  array (
    'name' => 'formkey',
    'title' => '表单密钥',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'mxN/2nNFlLDaC3K/M8RrSENsDAw=',
    'rule' => 'required',
    'msg' => '',
    'tip' => '请前往配置 > 内容管理 > API密钥 处获取',
    'ok' => '',
    'extend' => '',
  ),
  5 => 
  array (
    'name' => 'uploadmode',
    'title' => '上传模式',
    'type' => 'select',
    'content' => 
    array (
      'client' => '客户端直传(速度快,无备份)',
      'server' => '服务器中转(占用服务器带宽,有备份)',
    ),
    'value' => 'client',
    'rule' => '',
    'msg' => '',
    'tip' => '启用服务器中转时务必配置操作员和密码',
    'ok' => '',
    'extend' => '',
  ),
  6 => 
  array (
    'name' => 'savekey',
    'title' => '保存文件名',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  7 => 
  array (
    'name' => 'expire',
    'title' => '上传有效时长',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '7200',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  8 => 
  array (
    'name' => 'maxsize',
    'title' => '最大可上传',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '20M',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  9 => 
  array (
    'name' => 'mimetype',
    'title' => '可上传后缀格式',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx,pptx',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  10 => 
  array (
    'name' => 'multiple',
    'title' => '多文件上传',
    'type' => 'radio',
    'content' => 
    array (
      0 => '否',
      1 => '是',
    ),
    'value' => '0',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  11 => 
  array (
    'name' => 'operator',
    'title' => '操作员',
    'type' => 'bool',
    'content' => 
    array (
    ),
    'value' => '0',
    'rule' => '',
    'msg' => '服务端上传和删除文件时间使用',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  12 => 
  array (
    'name' => 'password',
    'title' => '密码',
    'type' => 'bool',
    'content' => 
    array (
    ),
    'value' => '0',
    'rule' => '',
    'msg' => '服务端上传和删除文件时间使用',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  13 => 
  array (
    'name' => 'syncdelete',
    'title' => '附件删除时是否同步删除文件',
    'type' => 'bool',
    'content' => 
    array (
    ),
    'value' => '0',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  14 => 
  array (
    'name' => '__tips__',
    'title' => '温馨提示',
    'type' => '',
    'content' => 
    array (
    ),
    'value' => '在使用之前请注册又拍云账号并进行认证和创建云储存，注册链接:<a href="https://console.upyun.com/register/?invite=SyAt3ehQZ" target="_blank">https://console.upyun.com/register/?invite=SyAt3ehQZ</a>',
    'rule' => '',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
);
