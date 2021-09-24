<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Hook;
use think\Validate;

/**
 * 用户接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';


    /**
     * Feedback模型对象
     * @var \app\admin\model\Feedback
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\api\model\User();
    }

//    public function _initialize()
//    {
//        parent::_initialize();
//
//        if (!Config::get('fastadmin.usercenter')) {
//            $this->error(__('User center already closed'));
//        }
//
//    }

    /**
     * 用户中心
     */
//    protected function index()
//    {
//        $this->success('', ['welcome' => $this->auth->nickname]);
//    }

    /**
     * 用户登录
     * @ApiTitle    (用户登录)
     * @ApiSummary  (用户登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/login)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="account", type="string", required=true, description="账号")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiReturnParams   (name="id", type="string", required=true, sample="用户id")
     * @ApiReturnParams   (name="username", type="string", required=true, sample="用户账号", description="用户账号")
     * @ApiReturnParams   (name="nickname", type="string", required=true, sample="用户昵称", description="用户昵称")
     * @ApiReturnParams   (name="mobile", type="string", required=true, sample="用户手机号", description="用户手机号")
     * @ApiReturnParams   (name="avatar", type="string", required=true, sample="用户头像", description="用户头像")
     * @ApiReturnParams   (name="birthday", type="string", required=true, sample="生日", description="生日")
     * @ApiReturnParams   (name="gender", type="string", required=true, sample="性别0女|1男", description="性别0女|1男")
     * @ApiReturnParams   (name="height", type="string", required=true, sample="身高", description="身高")
     * @ApiReturnParams   (name="weight", type="string", required=true, sample="体重", description="体重")
     * @ApiReturnParams   (name="bio", type="string", required=true, sample="个性签名", description="个性签名")
     * @ApiReturnParams   (name="token", type="string", required=true, sample="登录token", description="登录token")
     * @ApiReturn   ({
    "code": 1,
    "msg": "登录成功",
    "time": "1631774448",
    "data": {
    "userinfo": {
    "id": 1,
    "username": "admin",
    "nickname": "admin",
    "mobile": "13888888888",
    "avatar": "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgaGVpZ2h0PSIxMDAiIHdpZHRoPSIxMDAiPjxyZWN0IGZpbGw9InJnYigxNjAsMTkwLDIyOSkiIHg9IjAiIHk9IjAiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48L3JlY3Q+PHRleHQgeD0iNTAiIHk9IjUwIiBmb250LXNpemU9IjUwIiB0ZXh0LWNvcHk9ImZhc3QiIGZpbGw9IiNmZmZmZmYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIHRleHQtcmlnaHRzPSJhZG1pbiIgZG9taW5hbnQtYmFzZWxpbmU9ImNlbnRyYWwiPkE8L3RleHQ+PC9zdmc+",
    "gender": 0,
    "birthday": "2017-04-08",
    "bio": "",
    "height": null,
    "weight": null,
    "token": "5c0d306e-491b-41ec-b951-6948e4f421e0",
    "user_id": 1,
    "createtime": 1631774448,
    "expiretime": 1634366448,
    "expires_in": 2592000
    }
    }
    })
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    protected function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 注册用户
     * @ApiTitle    (注册用户)
     * @ApiSummary  (注册用户)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/register)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="username", type="string", required=true, description="账号")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiParams   (name="email", type="string", required=true, description="邮箱")
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="code", type="string", required=true, description="验证码")
     * @ApiReturnParams   (name="id", type="string", required=true, sample="用户id")
     * @ApiReturnParams   (name="username", type="string", required=true, sample="用户账号", description="用户账号")
     * @ApiReturnParams   (name="nickname", type="string", required=true, sample="用户昵称", description="用户昵称")
     * @ApiReturnParams   (name="mobile", type="string", required=true, sample="用户手机号", description="用户手机号")
     * @ApiReturnParams   (name="avatar", type="string", required=true, sample="用户头像", description="用户头像")
     * @ApiReturnParams   (name="birthday", type="string", required=true, sample="生日", description="生日")
     * @ApiReturnParams   (name="gender", type="string", required=true, sample="性别0女|1男", description="性别0女|1男")
     * @ApiReturnParams   (name="height", type="string", required=true, sample="身高", description="身高")
     * @ApiReturnParams   (name="weight", type="string", required=true, sample="体重", description="体重")
     * @ApiReturnParams   (name="bio", type="string", required=true, sample="个性签名", description="个性签名")
     * @ApiReturnParams   (name="token", type="string", required=true, sample="登录token", description="登录token")
     * @ApiReturn   ({
    "code": 1,
    "msg": "注册成功",
    "time": "1631776883",
    "data": {
    "userinfo": {
    "id": 2,
    "username": "15100003427",
    "nickname": "151****3427",
    "mobile": "15100003427",
    "avatar": "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgaGVpZ2h0PSIxMDAiIHdpZHRoPSIxMDAiPjxyZWN0IGZpbGw9InJnYigyMjksMTYwLDE2NSkiIHg9IjAiIHk9IjAiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48L3JlY3Q+PHRleHQgeD0iNTAiIHk9IjUwIiBmb250LXNpemU9IjUwIiB0ZXh0LWNvcHk9ImZhc3QiIGZpbGw9IiNmZmZmZmYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIHRleHQtcmlnaHRzPSJhZG1pbiIgZG9taW5hbnQtYmFzZWxpbmU9ImNlbnRyYWwiPjE8L3RleHQ+PC9zdmc+",
    "gender": 0,
    "birthday": null,
    "bio": "",
    "height": null,
    "weight": null,
    "token": "0962600d-1304-41e3-b0b1-6bb35f7f5b2b",
    "user_id": 2,
    "createtime": 1631776883,
    "expiretime": 1634368883,
    "expires_in": 2592000
    }
    }
    })
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');

        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }

        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }

        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        $ret = Ems::check($email, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
//        $ret = Sms::check($mobile, $code, 'register');
//        if (!$ret) {
//            $this->error(__('Captcha is incorrect'));
//        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 退出登录
     * @ApiTitle    (退出登录)
     * @ApiSummary  (退出登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/logout)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturn   ({
    "code": 1,
    "msg": "退出成功",
    "time": "1631777241",
    "data": null
    })
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }


    /**
     * 修改个人信息
     * @ApiTitle    (修改用户个人信息)
     * @ApiSummary  (修改用户个人信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/profile)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="nickname", type="string", required=true, sample="用户昵称", description="用户昵称")
     * @ApiParams   (name="avatar", type="string", required=true, sample="用户头像", description="用户头像")
     * @ApiParams   (name="birthday", type="string", required=true, sample="生日", description="生日")
     * @ApiParams   (name="gender", type="int", required=true, sample="性别", description="性别0女|1男")
     * @ApiParams   (name="height", type="string", required=true, sample="身高", description="身高")
     * @ApiParams   (name="weight", type="string", required=true, sample="体重", description="体重")
     * @ApiParams   (name="bio", type="string", required=true, sample="个性签名", description="个性签名")
     * @ApiReturn   ({"code":1,"msg":"操作成功","time":"1631779810","data":null})
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        //$username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $birthday = $this->request->post('birthday');
        $gender = $this->request->post('gender');
        $height = $this->request->post('height');
        $weight = $this->request->post('weight');
        $bio = $this->request->post('bio','','trim,strip_tags,htmlspecialchars');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
//        if ($username) {
//            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
//            if ($exists) {
//                $this->error(__('Username already exists'));
//            }
//            $user->username = $username;
//        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }

        if($bio) $user->bio = $bio;
        if($birthday) $user->birthday = $birthday;
        if($gender) $user->gender = $gender;
        if($height) $user->height = $height;
        if($weight) $user->weight = $weight;
        if($avatar) $user->avatar = $avatar;

        $user->save();
        $this->success('操作成功');
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    protected function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    protected function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    protected function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定用户
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    protected function resetpwd()
    {
        $type = $this->request->post("type");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
