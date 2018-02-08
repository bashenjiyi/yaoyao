<?php
defined('IN_IA') or exit('Access Denied');

define('LH_YAOYAO_RES', '../addons/lh_yaoyao/assets');
class Lh_yaoyaoModuleSite extends WeModuleSite {

    public function get_openid() {
        global $_W;
        return $_W['openid'];
    }
    
}
