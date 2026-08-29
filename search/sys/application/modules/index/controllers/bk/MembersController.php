<?php
//コンポーネントのロード
class MembersController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	public $_user;
	public $pref;
	public function init(){
		
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/signin/");
		}else{
			$this->_user = $auth->getIdentity();
			$this->view->user = $this->_user;
			$this->_db = new Model_Indexitem();
			//設定の読み込み
			$setting = new Model_Indexsettings;
			$this->_setting = $setting->setting("88");
			$this->view->setting = $this->_setting;
			$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
			);
			$this->view->pref =$this->pref;
		}
	}
	public function indexAction() {
		//登録情報の取得
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("`id`=?",$this->_user->id)
			->where("parent=88")
			->limit(1)
		);
		foreach($this->pref as $v){
			if($v['id'] == $detail[0]['pref']){
				$detail[0]['prefName'] = $v['name'];
			}
		}
		$this->view->detail = $detail[0];
		//タイトル
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$detail[0]['company'].$detail[0]['name'].'様のマイページ';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i> '.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		//ポイント情報
		$point = $this->_db->fetchAll(
			$this->_db->select()
			->from(
				"cutomerPoint",
				array(
					"*",
					"sum(pin)-sum(pout) as p"
				)
			)
			->where("`customerId`=?",$this->_user->id)
			->order("limit_date DESC")
			->limit(1)
		);
		$this->view->point = $point[0];
		//注文履歴
		$order = $this->_db->fetchAll(
			$this->_db->select()
			->from("order")
			->where("`member_no`=?",$this->_user->id)
			->order("order_date DESC")
		);
		foreach($order as $k=>$v){
			$order_item = $this->_db->fetchAll(
				$this->_db->select()
				->from("orderItem")
				->where("orderId=?",$v['id'])
			);
			$order[$k]['item'] = $order_item;
		}
		/*
		*/
		$this->view->order = $order;
	}
	public function updateAction() {
		//登録情報の取得
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("`id`=?",$this->_user->id)
			->where("parent=88")
			->limit(1)
		);
		foreach($this->pref as $v){
			if($v['id'] == $detail[0]['pref']){
				$detail[0]['prefName'] = $v['name'];
			}
		}
		$this->view->detail = $detail[0];
		//タイトル
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$detail[0]['company'].$detail[0]['name'].'様の登録情報編集';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i> '.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function confurmAction(){
		//登録情報の取得
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("`id`=?",$this->_user->id)
			->where("parent=88")
			->limit(1)
		);
		foreach($this->pref as $v){
			if($v['id'] == $detail[0]['pref']){
				$detail[0]['prefName'] = $v['name'];
			}
		}
		//タイトル
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$detail[0]['company'].$detail[0]['name'].'様の登録情報編集(確認)';
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$this->view->detail = $postArr;
		}
	}
	public function finishAction(){
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			if($postArr['pwedit'] == 1 && $postArr['pw']){
				$postArr['pw'] = common::pwHash($postArr['pw']);
			}else{
				unset($postArr['pw']);
			}
			$id = $postArr['id'];
			unset($postArr['id']);
			unset($postArr['pwedit']);
			unset($postArr['mail2']);
			unset($postArr['pwConfurm']);
			/*
			var_dump($postArr);
			*/
			if($this->_db->update("ImageUser",$postArr,$this->_db->quoteInto("`id`=?",$id))){
				$this->view->Msg = "登録情報の更新が完了しました。";
			}
		}
		//登録情報の取得
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("`id`=?",$this->_user->id)
			->where("parent=88")
			->limit(1)
		);
		foreach($this->pref as $v){
			if($v['id'] == $detail[0]['pref']){
				$detail[0]['prefName'] = $v['name'];
			}
		}
		$this->view->detail = $detail[0];
		//タイトル
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$detail[0]['company'].$detail[0]['name'].'様の登録情報編集(完了)';
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function deleteAction() {
		//登録情報の取得
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUser")
			->where("`id`=?",$this->_user->id)
			->where("parent=88")
			->limit(1)
		);
		foreach($this->pref as $v){
			if($v['id'] == $detail[0]['pref']){
				$detail[0]['prefName'] = $v['name'];
			}
		}
		$this->view->detail = $detail[0];
		//タイトル
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$detail[0]['company'].$detail[0]['name'].'様の会員登録の解除';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i> '.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function deletefinishAction() {
		//タイトル
		$this->view->title = '<i class="fa fa-tachometer"></i>会員登録の解除';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i> '.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$id = $postArr['id'];
			if($this->_db->delete("ImageUser",$this->_db->quoteInto("`id`=?",$id))){
				//ポイントの削除
				$this->_db->delete("cutomerPoint",$this->_db->quoteInto("`customerId`=?",$id));
				$auth = Zend_Auth::getInstance();
				$auth->clearIdentity();
				@session_destroy();
				$cookie_params = session_get_cookie_params();
				setcookie(session_name(), '', time()-42000, $cookie_params['path']);
				$this->view->Msg = "会員登録を解除しました。";
			}
		}
	}
}
?>