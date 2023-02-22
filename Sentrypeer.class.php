<?php
namespace FreePBX\modules;

class Sentrypeer extends \FreePBX_Helpers implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}
	public function install(){
		$this->setConfig('foo','bar');
		$this->getConfig('foo'); //returns 'bar'
	}
	public function uninstall(){}
	public function backup() {}
	public function restore($backup) {}

	/**
	 * Set Priority for Dialplan Hooking
	 * Core sits at a priority of 600
	 * @method myDialplanHooks
	 * @return string        Priority
	 */
	public static function myDialplanHooks() {
		return 900;
	}

	/**
	 * Hook into Dialplan (extensions_additional.conf)
	 * @method doDialplanHook
	 * @param  class       $ext      The Extensions Class https://wiki.freepbx.org/pages/viewpage.action?pageId=98701336
	 * @param  string       $engine   Always Asterisk, Legacy
	 * @param  string       $priority Priority
	 */
	public function doDialplanHook(&$ext, $engine, $priority) {
		//$ext->addInclude('from-internal-additional', 'sentrypeer-context'); // Add the context to from from-internal
		$mcontext = 'sentrypeer-context';
		$ext->add($mcontext, '_X!','', new \ext_noop('Hello from sentrypeer'));
	}

	public function doConfigPageInit($page) {}
	public function ucpConfigPage($mode, $user, $action) {
		if(empty($user)) {
			$enabled = ($mode == 'group') ? true : null;
		} else {
			if($mode == 'group') {
				$enabled = $this->FreePBX->Ucp->getSettingByGID($user['id'],'Sentrypeer','enabled');
				$enabled = !($enabled) ? false : true;
			} else {
				$enabled = $this->FreePBX->Ucp->getSettingByID($user['id'],'Sentrypeer','enabled');
			}
		}

		$html = array();
		$html[0] = array(
			"title" => _("UCP Module sentrypeer"),
			"rawname" => "sentrypeer",
			"content" => load_view(dirname(__FILE__)."/views/ucp_config.php",array("mode" => $mode, "enabled" => $enabled))
		);
		return $html;
	}
	public function ucpAddUser($id, $display, $ucpStatus, $data) {
		$this->ucpUpdateUser($id, $display, $ucpStatus, $data);
	}
	public function ucpUpdateUser($id, $display, $ucpStatus, $data) {
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'user') {
			if(isset($_POST['sentrypeer_enable']) && $_POST['sentrypeer_enable'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByID($id,'Sentrypeer','enabled',true);
			}elseif(isset($_POST['sentrypeer_enable']) && $_POST['sentrypeer_enable'] == 'no') {
				$this->FreePBX->Ucp->setSettingByID($id,'Sentrypeer','enabled',false);
			} elseif(isset($_POST['sentrypeer_enable']) && $_POST['sentrypeer_enable'] == 'inherit') {
				$this->FreePBX->Ucp->setSettingByID($id,'Sentrypeer','enabled',null);
			}
		}
	}
	public function ucpDelUser($id, $display, $ucpStatus, $data) {}
	public function ucpAddGroup($id, $display, $data) {
		$this->ucpUpdateGroup($id,$display,$data);
	}
	public function ucpUpdateGroup($id,$display,$data) {
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'group') {
			if(isset($_POST['sentrypeer_enable']) && $_POST['sentrypeer_enable'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByGID($id,'Sentrypeer','enabled',true);
			} else {
				$this->FreePBX->Ucp->setSettingByGID($id,'Sentrypeer','enabled',false);
			}
		}
	}
	public function ucpDelGroup($id,$display,$data) {}
	public function getActionBar($request) {
		$buttons = array();
		switch($_GET['display']) {
			case 'sentrypeer':
			$buttons = array(
			'delete' => array(
			'name' => 'delete',
			'id' => 'delete',
			'value' => _('Delete')
			),
			'reset' => array(
			'name' => 'reset',
			'id' => 'reset',
			'value' => _('Reset')
			),
			'submit' => array(
			'name' => 'submit',
			'id' => 'submit',
			'value' => _('Submit')
			)
			);
			if (empty($_GET['extdisplay'])) {
				unset($buttons['delete']);
			}
			break;
		}
		return $buttons;
	}
	public function showPage(){
		$vars = array('helloworld' => _("Hello World"));
		return load_view(__DIR__.'/views/main.php',$vars);
	}
	public function ajaxRequest($req, &$setting) {
		switch ($req) {
			case 'getJSON':
			return true;
			break;
			default:
			return false;
			break;
		}
	}
	public function ajaxHandler(){
		switch ($_REQUEST['command']) {
			case 'getJSON':
			switch ($_REQUEST['jdata']) {
				case 'grid':
				$ret = array();
				/*code here to generate array*/
				return $ret;
				break;

				default:
				return false;
				break;
			}
			break;

			default:
			return false;
			break;
		}
	}
	public function getRightNav($request) {
		$html = 'your custom html';
		return $html;
	}
}
