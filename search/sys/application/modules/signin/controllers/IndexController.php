<?php
// IndexController
class Signin_IndexController extends Common_SigninController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	public $pref;
	public function init(){
		$this->_db = new Model_Indexgeneral();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_setting = $setting->setting("88");
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		$this->view->pref = $this->pref;
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			$this->view->loginout = '<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin">ログイン</a>';
		}else{
			$this->view->loginout =
				'<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin/index/logout/">ログアウト</a>
				<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/member/">マイページ</a>';
		}
	}

	//ログイン
	public function indexAction() {
		/*
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","login")
		);
		$this->view->html =$html;
	*/
		//タイトル
		$this->view->title = "ログインフォーム";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->getRequest()->getParam('m')){
			$this->view->m = $this->getRequest()->getParam('m');
		}
		//ログイン状態を確認
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()){
			// ログインしている場合は権限に応じてリダイレクト
			$authority = $auth->getIdentity()->kind;
			header("location:".BASEURL."/member");
		}else{
			//ログインしていない
			$this->view->referer = $_SERVER['HTTP_REFERER'];
			$this->view->title = "会員様ログイン";
			if($this->_request->isPost()){
				//#########　認証　#########
				//パスワードを暗号化
				$postArr = $this->_db->postArray();
				$pw = common::pwHash($postArr['pw']);


				//マーチャントに対してユーザー登録が行われているか確認
				$user = $this->_db->fetchAll(
										$this->_db->select()
										->from("ImageUser")
										->where("kind =3")
										->where("mail=?",$postArr['id'])
										->where("pw=?",$pw)
				);
				if(empty($user)){
					//管理者、画像編集者、マーチャントとして登録されていない場合
					$this->view->Msg = "入力された情報では、登録されていません。<br />";
				}else{
					//管理者、画像編集者、マーチャントとして登録されている場合
					//DBに接続
					$dbAdapter = Zend_Registry::get('db');
					$authAdapter =  new Zend_Auth_Adapter_DbTable($dbAdapter);
					//DBとポストデータを照合
					$authAdapter->setTableName("ImageUser")
						->setIdentityColumn("id")
						->setCredentialColumn('pw');
					$authAdapter->setIdentity($user[0]['id']);
					$authAdapter->setCredential($pw);
					$result = $authAdapter->authenticate($authAdapter);
					//認証ができているか確認
					if ($result->isValid()){
						//セッションにユーザー情報を書き込み
						$data = $authAdapter->getResultRowObject(null,'password');
						//ポイント情報も登録する2016/05/9
						$point = $this->_db->fetchAll(
							$this->_db->select()
							->from("cutomerPoint","SUM(pin)-SUM(pout) as p")
							->where("customerId=?",$data->id)
						);
						if($point[0]['p'] ==NULL){
							$p = 0;
						}else{
							$p = $point[0]['p'];
						}
						$data->point= $p;
						$auth->getStorage()->write($data);

						//認証がOKの場合は権限に応じてリダイレクト
						$authority = $auth->getIdentity()->kind;
						if($postArr['place'] =="order"){
							header("location:".BASEURL."/order/");
						}else{
							header("location:".BASEURL."/member");
						}
					}else{
						//認証がNGの場合はエラーメッセージを表示
						$this->view->Msg = "メールアドレス若しくはパスワードが正しく入力されていません。<br />";
					}
				}

			}
		}
	}
	public function signupAction(){
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","memberRegister")
		);
		$this->view->html =$html;
		//タイトル
		$this->view->title = "会員登録";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function confurmAction(){
		//タイトル
		$this->view->title = "会員登録(確認)";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$postaArr['pwmask'] = $this->masking($postArr['pw']);
			$this->view->detail = $postArr;
		}
	}

	public function finishAction(){
		//タイトル
		$this->view->title = "会員登録(完了)";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
		if($this->_request->isPost()){

			$postArr = $this->_db->postArray();
			foreach($postArr as $k=>$v){
				if($k == "mail2" || $k == "pwConfurm" || $k == "pwmask"){
					unset($postArr[$k]);
				}
			}
			$postArr['pw'] = common::pwHash($postArr['pw']);
			$postArr['kind'] = 3;
			$postArr['active'] = 1;
			$postArr['parent'] = 88;
			$postArr['demand'] = 0;
			$postArr['created'] = date("Y-m-d H:i:s");
			$this->view->detail = $postArr;
			if($l_Id = $this->_db->insertAndGetLastId("ImageUser",$postArr)){
				$arr = array(
					"customerId" =>$l_Id,
					"created" =>date("Y-m-d H:i:s"),
					"pin" =>$this->_setting['point']['pointfirst'],
					"pout" =>0,
					"balance" =>0,
					"parent" =>88,
					"limit_date" =>date("Y-m-d H:i:s",time()+60*60*24*$this->_setting['point']['pointlimit'])
				);
				if($this->_db->insertAndGetLastId("cutomerPoint",$arr)){
					//結果を送る
					$this->view->register = true;
					$this->view->detail = $postArr;
					//メールを送付
					$this->sendmail(88,$postArr);
				}
			}
			/*
			*/
		}
	}
	//ログアウト
	public function logoutAction(){
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		@session_destroy();
		$cookie_params = session_get_cookie_params();
		setcookie(session_name(), '', time()-42000, $cookie_params['path']);
		header("location:".BASEURL."/signin/");
	}
	//パスワードを暗号化する例を
	public function hashAction() {
		echo common::pwHash($this->getRequest()->getParam('id'));
	}
	//
	public function agreementAction() {
		//タイトル
		$this->view->title = "会員規約の同意";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
	}

	private function sendmail($p,$data) {
		$this->data = $data;
		//メールデータの取得
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from("mail_template")
			->where("name='register' AND parent=?",$p)
			->limit(1)
		);
		$mail = array();
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "会員登録情報\n";
		$detailStr.= "ーーーーーーーーーーーー\n";
		$detailStr.= "お名前：".$data['name']."　".$data['name2']."\n";
		$detailStr.= "フリガナ：".$data['kana']."　".$data['kana2']."\n";
		$detailStr.= "電話番号：".$data['tel']."-".$data['tel2']."-".$data['tel3']."\n";
		$detailStr.= "携帯電話番号：".$data['mtel']."-".$data['mtel2']."-".$data['mtel3']."\n";
		foreach($this->pref as $v){
			if($v['id'] == $data['pref']){
				$cusPrefName = $v['name'];
			}
		}
		$detailStr.= "ご住所：〒".$data['zip']."-".$data['zip2']."\n".$cusPrefName.$data['addr']."\n".$data['addr2']."\n";
		$detailStr.= "メールアドレス：".$data['mail']."\n";
		foreach(array(1=>"女性",2=>"男性") as $K=>$v){
			if($k==$data['sex']){
				$detailStr.= "性別：".$v."\n";
			}
		}
		$detailStr.= "生年月日：".$data['birthY']."年".$data['birthM']."月".$data['birthD']."日"."\n";
		foreach($result[0] as $k=>$vv){
			$patterns[0] = '/%NAME%/';
			$patterns[1] = '/%MAIL%/';
			$patterns[2] = '/%ORDER%/';
			$patterns[3] = '/%SHOP%/';
			$patterns[4] = '/%URL%/';
			$patterns[5] = '/%DELIVERY_PAPER%/';
			$replacements[0] = $data['company'].$data['name'];
			$replacements[1] = $data['mail'];
			$replacements[2] = $detailStr;
			$replacements[3] = $this->_setting['global']['SiteName'];
			$replacements[4] = BASEURL;
			$result[0][$k] = preg_replace($patterns, $replacements, $vv);
		}

		$smtp = array();
		foreach($this->_setting['global'] as $k=>$v){
			if($k == "SMTPhost" || $k == "SMTPuser" || $k == "SMTPpass" || $k == "SMTPport" || $k == "SMTPsecur" || $k == "SMTPcertificate"){
				$smtp[$k] = $v;
			}
		}
		$this->_db->sendmail(
			true,
			$result[0]['subject'],
			$result[0]['body'],
			$this->_setting['global']['infoMail'],
			$this->_setting['global']['SiteName'],
			$this->data['mail'],
			$this->data['name']." ".$this->data['name'],
			$smtp
		);
	}
	private function masking($secretStr) {
		return str_repeat('*', strlen($secretStr));
	}
}
?>
