<?php

/**
 * 思刻通行证登录
 * 
 * @package SckurPassport
 * @author 思刻协会
 * @version 1.0.0
 * @link https://passport.sckur.com
 */
class SckurPassport_Plugin implements Typecho_Plugin_Interface {

    private static $pluginName = 'SckurPassport';

    public static function activate() {

        Typecho_Plugin::factory('Widget_User')->___SckurPassportIcon = array('SckurPassport_Action', 'AuthIcon');
        Helper::addAction('SckurPassportAuthorize', 'SckurPassport_Action');
        Helper::addRoute('SckurPassportAuthorize', '/passport/login', 'SckurPassport_Action', 'action');
        Helper::addRoute('SckurPassportCallback', '/passport/callback', 'SckurPassport_Action', 'callback');
        return '插件启用成功！请进行<a href="options-plugin.php?config=' . self::$pluginName . '">初始化设置</a>';
    }


    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate() {
        Helper::removeRoute('SckurPassportAuthorize');
        Helper::removeRoute('SckurPassportCallback');
        Helper::removeAction('SckurPassportAuthorize');
    }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {
        $client_id = new Typecho_Widget_Helper_Form_Element_Text('client_id', NULL, '', 'App Key', '在<a href="https://open.tsinbei.com" target="_blank">清北 API 开放平台</a>申请的应用 ID');
        $form->addInput($client_id);
        $client_secret = new Typecho_Widget_Helper_Form_Element_Text('client_secret', NULL, '', 'App Secret', '在<a href="https://open.tsinbei.com" target="_blank">清北 API 开放平台</a>申请的应用密钥');
        $form->addInput($client_secret);
        $callback_url = new Typecho_Widget_Helper_Form_Element_Text('callback_url', NULL, 'https://', '回调地址', '在<a href="https://open.tsinbei.com" target="_blank">清北 API 开放平台</a>设置的回调地址，格式为<code>http(s)://Typecho地址/passport/callback</code>');
        $form->addInput($callback_url);
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {
        //还没写好……
    }

}
