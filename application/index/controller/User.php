<?php

namespace app\index\controller;

use app\admin\model\match\Match;
use app\admin\model\match\Matchitem;
use app\admin\model\match\Matchiteminvest;
use app\admin\model\match\Matchitemmember;
use app\admin\model\match\Matchitempatent;
use app\admin\model\match\Matchitemstock;
use app\admin\model\match\Matchitemteacher;
use app\admin\model\match\Matchschool;
use app\admin\model\match\Matchscore;
use app\common\controller\Frontend;
use fast\Date;
use fast\Random;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 个人中心
 */
class User extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 空的请求
     * @param $name
     * @return mixed
     */
    public function _empty($name)
    {
        $data = Hook::listen("user_request_empty", $name);
        foreach ($data as $index => $datum) {
            $this->view->assign($datum);
        }
        return $this->view->fetch('user/' . $name);
    }

    /**
     * 个人中心
     */
    public function index()
    {
        $itemModel = new Matchitem();
        $userModel = new \app\admin\model\User();
        $userId = $this->auth->id;
        $list = $itemModel->alias("a")
            ->field("a.item_id,a.item_name,a.audit_status,a.item_status,u.nickname,mk.track_name,mg.group_name,a.createtime")
            ->join("user u","u.id = a.user_id","left")
            ->join("match_track mk","mk.track_id = a.track_id","left")
            ->join("match_group mg","mg.group_id = a.group_id","left")
            ->where("a.user_id","eq",$userId)
            ->limit("0","2")
            ->select();

        $userMsg = $userModel->alias("a")
            ->field("a.mobile,a.email,a.nickname,a.major,s.school_name")
            ->join("match_school s","s.school_id = a.school","left")
            ->where("a.id","eq",$userId)
            ->select();

        //查询当前是否有允许报名的比赛
        $matchModel = new Match();
        $matchList = $matchModel->selMatch();
        if(sizeof($matchList) == 0){
            $this->view->assign('errorMsg',"报名已截止");
        }else{
            $this->view->assign('errorMsg',"");
        }
        $allow_date = strtotime("2019-11-05 00:00:00");
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        $this->view->assign('list', $list);
        $this->view->assign('allowDate', $allow_date);
        $this->view->assign('user', $userMsg[0]);
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 注册会员
     */
    public function register()
    {
        $url = $this->request->request('url', '', 'trim');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            $row['nickname'] = $this->request->post('nickname');
//            $row['college'] = $this->request->post('college');
            $row['school'] = $this->request->post('school');
            $row['major'] = $this->request->post('major');
            $row['education'] = $this->request->post('education');
            $row['grade'] = $this->request->post('grade');
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $row['politicalStatus'] = $this->request->post('politicalStatus');
            $row['idCard'] = $this->request->post('idCard');
            $row['sno'] = $this->request->post('sno');
            $row['gender'] = $this->request->post('gender');
            $row['birthday'] = $this->request->post('birthday');
            $email = $this->request->post('email');
            $mobile = $this->request->post('mobile', '');
            $captcha = $this->request->post('captcha');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:6,30',
                'email'     => 'require|email',
                'mobile'    => 'regex:/^1\d{10}$/',
                'captcha'   => 'require|captcha',
                '__token__' => 'token',
            ];

            $msg = [
                'username.require' => 'Username can not be empty',
                'username.length'  => 'Username must be 3 to 30 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
                'captcha.require'  => 'Captcha can not be empty',
                'captcha.captcha'  => 'Captcha is incorrect',
                'email'            => 'Email is incorrect',
                'mobile'           => 'Mobile is incorrect',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                'email'     => $email,
                'mobile'    => $mobile,
                'captcha'   => $captcha,
                '__token__' => $token,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            if ($this->auth->register($username, $password, $email, $mobile,$row)) {
                $this->success(__('Sign up successful'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }

        //选择学校下拉
        $schoolModel = new Matchschool();
        $schoolNameList = $schoolModel->field("school_id,school_name")->select();
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
        $this->view->assign("schoolNameList",$schoolNameList);
        return $this->view->fetch();
    }

    /**
     * 会员登录
     */
    public function login()
    {
        $url = $this->request->request('url', '', 'trim');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            $account = $this->request->post('account');
            $password = $this->request->post('password');
            $keeplogin = (int)$this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'account'   => 'require|length:3,50',
                'password'  => 'require|length:6,30',
                '__token__' => 'token',
            ];

            $msg = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data = [
                'account'   => $account,
                'password'  => $password,
                '__token__' => $token,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }
            if ($this->auth->login($account, $password)) {
                $this->success(__('Logged in successful'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        //注销本站
        $this->auth->logout();
        $this->success(__('Logout successful'), url('user/index'));
    }

    /**
     * 个人信息
     */
    public function profile()
    {
        $userModel = new \app\admin\model\User();
        $schoolModel = new Matchschool();

        $userId = $this->auth->id;
        $userMsg = $userModel->alias("a")
            ->field("a.id,a.bio,a.avatar,a.username,a.mobile,a.email,a.nickname,a.major,a.school,a.college,a.gender,a.class,a.education,a.sno,a.id_card,s.school_name")
            ->join("match_school s","s.school_id = a.school","left")
            ->where("a.id","eq",$userId)
            ->select();

        $schoolList = $schoolModel->selSchool();
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        $this->view->assign('user', $userMsg[0]);
        $this->view->assign('schoolList', $schoolList);
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改会员个人信息
     */
    public function saveProfile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $gender = $this->request->request('gender');
        $college = $this->request->request('college');
        $school = $this->request->request('school');
        $major = $this->request->request('major');
        $class = $this->request->request('class');
        $education = $this->request->request('education');
        $sno = $this->request->request('sno');
        $id_card = $this->request->request('id_card');
        $email = $this->request->request('email');
        $mobile = $this->request->request('mobile');
        $bio = $this->request->request('bio');
        $avatar = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        $user->nickname = $nickname;
        $user->gender = $gender;
        $user->college = $college;
        $user->school = $school;
        $user->major = $major;
        $user->class = $class;
        $user->education = $education;
        $user->sno = $sno;
        $user->id_card = $id_card;
        $user->email = $email;
        $user->mobile = $mobile;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success("修改成功");
        return $this->view->assign("profile");
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|length:6,30|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        $userModel = new \app\admin\model\User();
        $userId = $this->auth->id;
        $userMsg = $userModel->alias("a")
            ->field("a.mobile,a.email,a.nickname,a.major,s.school_name")
            ->join("match_school s","s.school_id = a.school","left")
            ->where("a.id","eq",$userId)
            ->select();
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        $this->view->assign('user', $userMsg[0]);
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        $name = $this->request->post("name");
        Config::set('default_return_type', 'json');
        $file = $this->request->file($name);
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();
        $extparam = $this->request->post();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            if (in_array($suffix, ['mp4','docx','pdf','doc','ppt','pptx'])) {
                $imgInfo = '';
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'admin_id'    => (int)$this->auth->id,
                'user_id'     => 0,
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
            $attachment = model("attachment");
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen("upload_after", $attachment);
            $this->success(__('Upload successful'), null, [
                'url' => $uploadDir . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 创建,编辑项目页面
     *
     */
    public function createproject()
    {
        $project_id = null;
        if(!empty($this->request->param("projectId"))){
            $project_id = $this->request->param("projectId");
        }
        $matchModel = new Match();
        $userModel = new \app\admin\model\User();
        $schoolModel = new Matchschool();
        $itemModel = new Matchitem();
        $stockModel = new Matchitemstock();
        $investModel = new Matchiteminvest();
        $memberModel = new Matchitemmember();
        $teacherModel = new Matchitemteacher();
        $patentModel = new Matchitempatent();

        //下拉数据
        $matchList = $matchModel->selMatch();
        $groupList = $matchModel->selGroup();
        $trackList = $matchModel->selTrack();
        $schoolList = $schoolModel->selSchool();
        $student_id = $this->auth->id;
        $userList = $userModel->getUserInfo($student_id);

        //回显数据
        $row = null;
        $stock = [];
        $invest = [];
        $member = null;
        $teacher = null;
        $patent = null;
        if(!empty($project_id)){
            //编辑

            $row = $itemModel->where("item_id","eq",$project_id)->select();
            //项目计划书后缀
            $planPaperSuffix = '';
            //一分钟展示视频后缀
            $vedioFileSuffix = '';
            //其他佐证资料后缀
            $otherFileSuffix = '';
            if(!empty($row[0]['plan_paper_file'])){
                $plan_paper_file = $row[0]['plan_paper_file'];
                $planPaperSuffix = substr($plan_paper_file,strrpos($plan_paper_file,'.') + 1);
            }
            if(!empty($row[0]['vedio_file'])){
                $vedioFileSuffix = $row[0]['vedio_file'];
                $vedioFileSuffix = substr($vedioFileSuffix,strrpos($vedioFileSuffix,'.') + 1);
            }
            if(!empty($row[0]['other_file'])){
                $otherFileSuffix = $row[0]['other_file'];
                $otherFileSuffix = substr($otherFileSuffix,strrpos($otherFileSuffix,'.') + 1);
            }
            $stock = $stockModel->where("item_id","eq",$project_id)->select();
            $invest = $investModel->where("item_id","eq",$project_id)->select();
            $member = $memberModel->alias("a")
                ->field("a.*,b.school_name")
                ->join("match_school b","b.school_id = a.school_id","left")
                ->where("a.item_id","eq",$project_id)
                ->order("a.member_id","asc")
                ->select();
            $teacher = $teacherModel->alias("a")
                ->field("a.*,b.school_name")
                ->join("match_school b","b.school_id = a.school_id","left")
                ->where("a.item_id","eq",$project_id)
                ->select();
            $patent = $patentModel->where("item_id","eq",$project_id)->select();
            if($stock != ''){
                $this->view->assign('stockList', $stock);
            }
            if($invest != ''){
                $this->view->assign('investList', $invest);
            }
            if($member != ''){
                $this->view->assign('memberList', $member);
                $this->view->assign('memberNum', sizeof($member));
            }
            if($teacher != ''){
                $this->view->assign('teacherList', $teacher);
                $this->view->assign('teacherNum', sizeof($teacher));
            }
            if($patent != ''){
                $this->view->assign('patentList', $patent);
                $this->view->assign('patentNum', sizeof($patent));
            }
            $this->view->assign('planPaperSuffix', $planPaperSuffix);
            $this->view->assign('vedioFileSuffix', $vedioFileSuffix);
            $this->view->assign('otherFileSuffix', $otherFileSuffix);
            $this->view->assign('project_id', $project_id);
            $this->view->assign('row', $row[0]);
        }else{
            //新增
            $this->view->assign('stockList', $stock);
            $this->view->assign('investList', $invest);
            $this->view->assign('memberList', $member);
            $this->view->assign('teacherList', $teacher);
            $this->view->assign('patentList', $patent);
            $this->view->assign('project_id', $project_id);
            $this->view->assign('row', null);
            $this->view->assign('memberNum', null);
            $this->view->assign('teacherNum', null);
            $this->view->assign('patentNum', null);
        }
        $this->view->assign('matchList', $matchList);
        $this->view->assign('groupList', $groupList);
        $this->view->assign('trackList', $trackList);
        $this->view->assign('schoolList', $schoolList);
        $this->view->assign('userId', $student_id);
        $this->view->assign('username', $userList['nickname']);
        $this->view->assign('schoolName', $userList);
        $this->view->assign('title', __('Profile'));
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        return $this->view->fetch();
    }

    /**
     * 保存项目
     */
    public function saveProject()
    {
       $result = null;
       $match_item_id = null;
       $matchItemModel = new Matchitem();
       $id = $this->request->post('id');
       $arr['group_id'] = "";
       $arr['match_id'] = $this->request->post('match_id');
       $arr['track_id'] = $this->request->post('track_id');
       $arr['logo_avatar'] = "";
       $arr['item_name'] = $this->request->post('name');
       $arr['city'] = "";
//       $list= $this->request->param('category/a');
//       $category = null;
//       if(sizeof($list) > 1){
//            foreach($list as $key => $vals){
//                $category.=$vals.',';
//            }
//           $category = substr($category, 0, -1);
//       }else{
//           $category = $list[0];
//       }
       $arr['category'] = "";
       $arr['item_desc'] = $this->request->post('item_desc');
//       $arr['is_hightech'] = $this->request->post('is_hightech');
       $arr['founder_desc'] = $this->request->post('founder_desc');
//       $arr['is_founder_lead'] = $this->request->post('is_founder_lead');
//       $arr['is_together'] = $this->request->post('is_together');
       $arr['is_team'] = 'Y';
       $arr['progress'] = $this->request->post('progress');
       $arr['vedio_file'] = $this->request->post('vedioUrl');
       $arr['other_file'] = $this->request->post('otherUrl');
       $arr['plan_paper_file'] = $this->request->post('fileUrl');
//       $arr['is_private'] = $this->request->post('secretStatus');

        //如果项目进展不为 创意计划阶段 则保存如下数据字段
        if($arr['progress'] != '0'){
            $arr['company_name'] = $this->request->post('companyName');
            $arr['legal_person_type'] = $this->request->post('legalRepresentType');
            $arr['legal_person_name'] = $this->request->post('legalRepresent');
            $arr['legal_person_post'] = $this->request->post('job');
            $arr['register_capital'] = $this->request->post('registerCapital');
            $arr['register_date'] = $this->request->post('registerDate');
            $arr['register_address'] = $this->request->post('registerAddress');
            $arr['credit_code'] = $this->request->post('organizationCode');
            $arr['is_invest'] = $this->request->post('isInvest');
            if($arr['is_invest'] == '0'){
                $arr['is_invest'] = "N";
            }else{
                $arr['is_invest'] = "Y";
            }
        }

        //判断是新增还是编辑
        if($id != '') {
            //如果审核状态是 审核未通过 或退回修改 编辑后状态变更为待审核
            $audit_status = $matchItemModel->field("audit_status")->where("item_id","eq",$id)->select();
            if(!empty($audit_status[0])){
                $audit_status = $audit_status[0]['audit_status'];
            }
            if($audit_status == '审核未通过' || $audit_status == '退回修改'){
                $arr['audit_status'] = '待审核';
            }
            //编辑
            $matchItemModel->isUpdate(true)->save($arr, ['item_id' => $id]);
            //如果项目进展选择 已注册公司运营
            if ($arr['progress'] == '1') {
                //修改股权表（先删除 再新增）
                $stockDelModel = new Matchitemstock();
                $stockDelModel->where("item_id", "eq", $id)->delete();

                $stockholder_type = $this->request->post('shareholderType/a');
                $stockholder = $this->request->post('shareholder/a');
                $hold_ratio = $this->request->post('shareholderRatio/a');
                for ($i = 0; $i < sizeof($stockholder_type); $i++) {
                    $stockModel = new Matchitemstock();
                    $stock['item_id'] = $id;
                    $stock['stockholder_type'] = $stockholder_type[$i];
                    $stock['stockholder'] = $stockholder[$i];
                    $stock['hold_ratio'] = $hold_ratio[$i];
                    $stock['createtime'] = time();
                    $stockModel->save($stock);
                }
                //如果 选择已获投资
                if ($arr['is_invest'] == 'Y') {
                    $investDelModel = new Matchiteminvest();
                    $investDelModel->where("item_id", "eq", $id)->delete();
                    //保存投资表
                    $invest_name = $this->request->post('orgName/a');
                    $invest_stage = $this->request->post('investStageCode/a');
                    $invest_amount = $this->request->post('investMoney/a');
                    $gain_time = $this->request->post('gainDate/a');
                    for ($i = 0; $i < sizeof($invest_name); $i++) {
                        $investModel = new Matchiteminvest();
                        $invest['item_id'] = $id;
                        $invest['invest_name'] = $invest_name[$i];
                        $invest['invest_stage'] = $invest_stage[$i];
                        $invest['invest_amount'] = $invest_amount[$i];
                        $invest['gain_time'] = $gain_time[$i];
                        $invest['createtime'] = time();
                        $investModel->save($invest);
                    }
                }
            }
            $msg['flag'] = 'editSuccess';
            $msg['item_id'] = $id;
            return $msg;
        } else {
            //新增
            $matchCount = $matchItemModel->where(['user_id' => $this->auth->id])->count();
            if ($matchCount>=1) {
                $msg['flag'] = 'false';
                $msg['message'] = '添加参赛作品失败,一位教师只允许参加一个参赛作品！';
                $msg['item_id'] = $match_item_id;
                return $msg;
            }else{
                $arr['audit_status'] = '待审核';
                $arr['item_status'] = '复赛';
                $arr['createtime'] = time();
                $arr['user_id'] = $this->auth->id;
                $matchItemModel->save($arr);
                $match_item_id = $matchItemModel->getLastInsID();//项目id
                //如果项目进展选择 已注册公司运营
                if ($arr['progress'] == '1') {
                    //保存股权表
                    $stockholder_type = $this->request->post('shareholderType/a');
                    $stockholder = $this->request->post('shareholder/a');
                    $hold_ratio = $this->request->post('shareholderRatio/a');
                    for ($i = 0; $i < sizeof($stockholder_type); $i++) {
                        $stockModel = new Matchitemstock();
                        $stock['item_id'] = $match_item_id;
                        $stock['stockholder_type'] = $stockholder_type[$i];
                        $stock['stockholder'] = $stockholder[$i];
                        $stock['hold_ratio'] = $hold_ratio[$i];
                        $stock['createtime'] = time();
                        $stockModel->save($stock);
                    }
                    //如果 选择已获投资
                    if ($arr['is_invest'] == 'Y') {
                        //保存投资表
                        $invest_name = $this->request->post('orgName/a');
                        $invest_stage = $this->request->post('investStageCode/a');
                        $invest_amount = $this->request->post('investMoney/a');
                        $gain_time = $this->request->post('gainDate/a');
                        for ($i = 0; $i < sizeof($invest_name); $i++) {
                            $investModel = new Matchiteminvest();
                            $invest['item_id'] = $match_item_id;
                            $invest['invest_name'] = $invest_name[$i];
                            $invest['invest_stage'] = $invest_stage[$i];
                            $invest['invest_amount'] = $invest_amount[$i];
                            $invest['gain_time'] = $gain_time[$i];
                            $invest['createtime'] = time();
                            $investModel->save($invest);
                        }
                    }
                }
                $msg['flag'] = 'success';
                $msg['message'] = '添加项目成功';
                $msg['item_id'] = $match_item_id;
                return $msg;
            }
            }

    }

    /**
     * 删除项目
     */
    function delProject(){
        $item_id = $this->request->post("itemId");
        $itemModel = new Matchitem();
        $investModel = new Matchiteminvest();
        $memberModel = new Matchitemmember();
        $patentModel = new Matchitempatent();
        $stockModel = new Matchitemstock();
        $teacherModel = new Matchitemteacher();
        //删除项目表数据
        $itemModel->where("item_id","eq",$item_id)->delete();
        $investModel->where("item_id","eq",$item_id)->delete();
        $memberModel->where("item_id","eq",$item_id)->delete();
        $patentModel->where("item_id","eq",$item_id)->delete();
        $stockModel->where("item_id","eq",$item_id)->delete();
        $teacherModel->where("item_id","eq",$item_id)->delete();

    }

    /**
     * 保存成员邀请
     */
    public function saveMemberInvite(){
        $memberModel = new Matchitemmember();
        $memberCount = $memberModel
            ->where(['item_id' => $this->request->param("projectId")])
            ->count();

        if($memberCount>=10){
            $msg['flag'] = false;
            $msg['message'] = '成员数量超过10人，邀请失败！';

        }else{
            $arr['item_id'] = $this->request->param("projectId");
            $arr['school_id'] = $this->request->param("school");
            $arr['member_name'] = $this->request->param("memName");
            $arr['phone'] = $this->request->param("phone");
            $arr['college'] = $this->request->param("college");
            $arr['major'] = $this->request->param("major");
            $arr['grade'] = $this->request->param("grade");
            $arr['education'] = $this->request->param("education");
            $arr['sno'] = $this->request->param("sno");
            $arr['team_role'] = '成员';
            $arr['createtime'] = time();
            $result = $memberModel->save($arr);
            if($result){
                $msg['flag'] = true;
                $msg['member_id'] = $memberModel->getLastInsID();
            }else{
                $msg['flag'] = false;
                $msg['message'] = '邀请失败';
            }
        }

        return $msg;
    }

    /**
     * 删除成员
     */
    function delMember(){
        $memberModel = new Matchitemmember();
        $id = $this->request->param("id");
        $result = $memberModel->where("member_id","eq",$id)->delete();
        if($result){
            return "success";
        }else{
            return "error";
        }
    }

    /**
     * 添加指导教师
     */
    function saveTeacher(){
        $arr['item_id'] = $this->request->post("projectId");
        $arr['teacher_name'] = $this->request->post("name");
        $arr['teacher_mobile'] = $this->request->post("phone");
        $arr['teacher_email'] = $this->request->post("email");
        $arr['school_id'] = $this->request->post("schoolCode");
        $arr['department'] = $this->request->post("department");
        $arr['positional_title'] = $this->request->post("jobTitle");
        $arr['createtime'] = time();
        $teacherModel = new Matchitemteacher();
        $matchItemTeachCount = $teacherModel
            ->where(['item_id'=>$this->request->post("projectId")])
            ->count();
        if ($matchItemTeachCount >=5) {
            $teacherList['flag'] = 'error';
            $teacherList['message'] = '项目中指导教师的人数不超过5人！';
            return $teacherList;
        }else{
            $list = $teacherModel->save($arr);
            $newTeacherId = $teacherModel->getLastInsID();
            if($list){
                $schoolModel = new Matchschool();
                $schList = $schoolModel->field("school_name")->where("school_id","eq",$arr['school_id'])->select();
                $teacherList = $arr;
                $teacherList['teacherId'] = $newTeacherId;
                $teacherList['schoolName'] = $schList[0]['school_name'];
                $teacherList['flag'] = 'success';
                return $teacherList;
            }else{
                $teacherList['flag'] = 'error';
                return $teacherList;
            }
        }

    }

    /**
     * 删除指导教师
     */
    function delTeacher(){
        $teacherModel = new Matchitemteacher();
        $id = $this->request->param("id");
        $result = $teacherModel->where("teacher_id","eq",$id)->delete();
        if($result){
            return "success";
        }else{
            return "error";
        }
    }

    /**
     * 添加专利
     */
    function savePatent(){
        $arr['patent_name'] = $this->request->post("name");
        $arr['patent_type'] = $this->request->post("patentCategoryCode");
        $arr['patent_no'] = $this->request->post("patentCode");
        $arr['gain_date'] = $this->request->post("gainTime");
        $arr['item_id'] = $this->request->post("projectId");
        $arr['createtime'] = time();
        $patentModel = new Matchitempatent();
        $list = $patentModel->save($arr);
        $patent_id = $patentModel->getLastInsID();
        if($list){
            $patentList['id'] = $patent_id;
            $patentList['patent_name'] = $arr['patent_name'];
            $patentList['patent_type'] = $arr['patent_type'];
            $patentList['patent_no'] = $arr['patent_no'];
            $patentList['gain_date'] = $arr['gain_date'];
            $patentList['flag'] = 'success';
            return $patentList;
        }else{
            $patentList['flag'] = 'error';
            return $patentList;
        }
    }

    /**
     * 删除专利
     */
    function delPatent(){
        $patentModel = new Matchitempatent();
        $id = $this->request->param("id");
        $result = $patentModel->where("patent_id","eq",$id)->delete();
        if($result){
            return "success";
        }else{
            return "error";
        }
    }

    /**
     * 评分记录
     */
    function scoreDecord(){
        $projectId = $this->request->post("projectId");
        $item_status = $this->request->post("item_status");

        $matchScoreModel = new Matchscore();
        //数据
        $list = null;
        $final_list = null;
        //复赛计算评分状态
        $scoreHalfCount = null;
        //总决赛计算评分状态
        $scoreFinalCount = null;
        if($item_status == '复赛'){
            $list = $matchScoreModel->alias("s")
                ->field("a.nickname,s.score,mi.half_score,GROUP_CONCAT(CONCAT(d.rule_name,':',d.score,'分') SEPARATOR ',') score_detail")
                ->join("fa_match_score_detail d","s.score_id=d.score_id","left")
                ->join("fa_match_item mi","mi.item_id = s.item_id","left")
                ->join("fa_admin a","s.expert_id = a.id","left")
                ->where("s.item_id","eq",$projectId)
                ->where("s.stage","eq","复赛")
                ->group("a.nickname,mi.half_score,s.score")
                ->select();
            $scoreHalfCount = $matchScoreModel->field("COUNT(score_id) AS expertCount,COUNT(score)AS scoreCount")->where("item_id","eq",$projectId)->where("stage","eq","复赛")->select();
        }else if($item_status == '总决赛'){
            $list = $matchScoreModel->alias("s")
                ->field("a.nickname,s.score,mi.half_score,GROUP_CONCAT(CONCAT(d.rule_name,':',d.score,'分') SEPARATOR ',') score_detail")
                ->join("fa_match_score_detail d","s.score_id=d.score_id","left")
                ->join("fa_match_item mi","mi.item_id = s.item_id","left")
                ->join("fa_admin a","s.expert_id = a.id","left")
                ->where("s.item_id","eq",$projectId)
                ->where("s.stage","eq","复赛")
                ->group("a.nickname,mi.half_score,s.score")
                ->select();
            $final_list = $matchScoreModel->alias("s")
                ->field("a.nickname,s.score,mi.half_score,mi.final_score,GROUP_CONCAT(CONCAT(d.rule_name,':',d.score,'分') SEPARATOR ',') score_detail")
                ->join("fa_match_score_detail d","s.score_id=d.score_id","left")
                ->join("fa_match_item mi","mi.item_id = s.item_id","left")
                ->join("fa_admin a","s.expert_id = a.id","left")
                ->where("s.stage","eq","总决赛")
                ->where("s.item_id","eq",$projectId)
                ->group("a.nickname,mi.half_score,mi.final_score,s.score")
                ->select();
            $scoreHalfCount = $matchScoreModel->field("COUNT(score_id) AS expertCount,COUNT(score)AS scoreCount")->where("item_id","eq",$projectId)->where("stage","eq","复赛")->select();
            $scoreFinalCount = $matchScoreModel->field("COUNT(score_id) AS expertCount,COUNT(score)AS scoreCount")->where("item_id","eq",$projectId)->where("stage","eq","总决赛")->select();
        }
        $res['list'] = $list;
        $res['finalList'] = $final_list;
        $res['halfCount'] = null;
        $res['finalCount'] = null;
        if(!empty($scoreHalfCount[0])){
            $res['halfCount']['expertCount'] = $scoreHalfCount[0]['expertCount'];
            $res['halfCount']['scoreCount'] = $scoreHalfCount[0]['scoreCount'];
        }
        if(!empty($scoreFinalCount[0])){
            $res['finalCount']['expertCount'] = $scoreFinalCount[0]['expertCount'];
            $res['finalCount']['scoreCount'] = $scoreFinalCount[0]['scoreCount'];
        }
        return $res;
    }

    /**
     * 详情
     * @return string
     */
    public function detail(){
        $id = $this->request->get("id");
        $investModel = new Matchiteminvest();
        $memberModel = new Matchitemmember();
        $patentModel = new Matchitempatent();
        $stockModel = new Matchitemstock();
        $teacherModel = new Matchitemteacher();
        $matchItemModel = new Matchitem();
        $row = $matchItemModel->alias("a")
            ->field("a.*,m.match_name,mg.group_name,mt.track_name,u.nickname,mi.*,mb.*,mp.*,ms.*,mit.*")
            ->join("match m","m.match_id = a.match_id","left")
            ->join("match_group mg","mg.group_id = a.group_id","left")
            ->join("match_track mt","mt.track_id = a.track_id","left")
            ->join("user u","u.id = a.user_id","left")
            ->join("match_item_invest mi","mi.item_id = a.item_id","left")
            ->join("match_item_member mb","mb.item_id = a.item_id","left")
            ->join("match_item_patent mp","mp.item_id = a.item_id","left")
            ->join("match_item_stock ms","ms.item_id = a.item_id","left")
            ->join("match_item_teacher mit","mit.item_id = a.item_id","left")
            ->where("a.item_id","eq",$id)
            ->select();
        $row[0]['category'] = str_replace('1','',$row[0]['category']);
        $row[0]['category'] = str_replace('2','',$row[0]['category']);
        //个人信息
        $userInfo = $matchItemModel->alias("a")->field("u.*")->join("user u","u.id = a.user_id","left")->where("a.item_id","eq",$id)->select();
        //投资数据
        $investList = $investModel->alias("a")
            ->field("a.*")
            ->where("a.item_id","eq",$id)
            ->select();

        //成员数据
        $memberList = $memberModel->alias("a")
            ->field("a.*,s.school_name")
            ->join("match_school s","s.school_id = a.school_id","left")
            ->where("a.item_id","eq",$id)
            ->order("a.member_id","asc")
            ->select();

        //项目专利
        $patentList = $patentModel->where("item_id","eq",$id)->select();

        //公司股权
        $stockList = $stockModel->where("item_id","eq",$id)->select();

        //指导教师
        $teacherList = $teacherModel->alias("a")
            ->field("a.*,s.school_name")
            ->join("match_school s","s.school_id = a.school_id","left")
            ->where("item_id","eq",$id)
            ->select();

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $tetle =$this->auth->nickname?"就业指导中心比赛"."/".$this->auth->nickname:"就业指导中心比赛/姓名";
        $this->view->assign("tetle",$tetle);
        $this->view->assign("row", $row[0]);
        $this->view->assign("userInfo", $userInfo[0]);
        $this->view->assign("investList", $investList);
        $this->view->assign("memberList", $memberList);
        $this->view->assign("patentList", $patentList);
        $this->view->assign("stockList", $stockList);
        $this->view->assign("teacherList", $teacherList);
        $this->view->assign("item_id", $id);
        return $this->view->fetch("detail");
    }
}
