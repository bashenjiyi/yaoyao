<?php
/**
 * 语音红包模块处理程序
 *
 * @author shenjian
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Lh_yaoyaoModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
        $rid = $this->rule;
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
	}
}