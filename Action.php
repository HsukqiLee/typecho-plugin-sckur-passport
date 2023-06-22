<?php
/**
 * SckurPassport Plugin
 *
 * @copyright  Copyright (c) 2023 Sckur Association https://www.sckur.com
 * @license    GNU General Public License 2.0
 * 
 */

class SckurPassport_Action extends Typecho_Widget implements Widget_Interface_Do 
{

    protected $db;
    private $config;
    private static $pluginName = 'SckurPassport';

    public function __construct($request, $response, $params = NULL) {
        parent::__construct($request, $response, $params);
        $this->config = Helper::options()->plugin(self::$pluginName);
        $this->db = Typecho_Db::get();
    }

    public function action() {
        echo '<script>window.location.href = "https://passport.sckur.com/login.php?callback='.$this->config->callback_url.'&api_id='.$this->config->client_id.'";</script>';
        exit;
    }

    public static function AuthIcon() {
        return '<a href="https://passport.sckur.com/login.php?callback='.$this->config->callback_url.'&api_id='.$this->config->client_id.'"><img style="height:30px" src="https://passport.sckur.com/static/images/login.png" alt="使用思刻通行证登录"></a>';
    }
    
    public function callback() {
        if (empty($_REQUEST['user_key'])) {
            throw new Typecho_Exception('无效的回调请求！');
        }

        $res = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/','',file_get_contents('https://passport.sckur.com/api.php?site_key='.$this->config->client_secret.'&user_key='.$_REQUEST['user_key'].'&request=get-maininfo')),true);

        
        if ($res['status']!='successful') {
            throw new Typecho_Exception($res['message']);//参考错误码
            exit();
        }
       
        // 在 users 表中查找是否存在此邮箱的用户
        $user = $this->db->fetchRow($this->db->select()->from('table.users')->where('mail = ?', $res['email']));

        if ($user) {
            // 用户已存在，直接登录
            $this->setUserLogin($user['uid']);
            
        } else {
            // 用户不存在，注册并登录再跳转到后台
            $username = $this->generateUsername($res['email']); // 生成用户名
            $password = $this->generatePassword(); // 生成密码
            $user = $this->registerUser($username, $res['email'], $password); // 注册用户

            if ($user) {
                // 注册成功，直接登录并跳转到后台
                $this->setUserLogin($user['uid']);

               
            } else {
                // 注册失败，返回错误信息
                $this->response->throwJson(array('status' => 'error', 'message' => 'Failed to register user'));
            }
        }
        
        
        $this->response->redirect(Typecho_Widget::widget('Widget_Options')->adminUrl);//登录成功
        exit();
    }


    /**
     * 设置用户登陆状态
     */
    protected function setUserLogin($uid, $expire = 30243600) {
        Typecho_Widget::widget('Widget_User')->simpleLogin($uid);
        $authCode = function_exists('openssl_random_pseudo_bytes') ?
                bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Typecho_Common::randString(20));
        Typecho_Cookie::set('__typecho_uid', $uid, time() + $expire);
        Typecho_Cookie::set('__typecho_authCode', Typecho_Common::hash($authCode), time() + $expire);
        //更新最后登录时间以及验证码
        $this->db->query($this->db
                        ->update('table.users')
                        ->expression('logged', 'activated')
                        ->rows(array('authCode' => $authCode))
                        ->where('uid = ?', $uid));
    }

}
