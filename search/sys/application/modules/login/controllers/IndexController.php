<?php
// IndexController
class Login_IndexController extends Common_LoginController {
	//初期化メソッドの定義
	private $_db;
	public function init(){
		$this->_db = new Model_Editorimageuser();
		//設定の読み込み
		//require("/var/www/html/sys/application/modules/login/models/Loginsettings.php");
		$setting = new Model_Editorsettings;
		$this->_setting = $setting->setting("88");
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);

		$this->view->pref = $this->_db->fetchAll(
				$this->_db->select()
				->from("pref")
			);
	}

	//ログイン
	public function indexAction() {
		//タイトル
		$this->view->title = "ログイン";
		if($this->getRequest()->getParam('m')){
			$this->view->m = $this->getRequest()->getParam('m');
		}
		//ログイン状態を確認
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()){
			// ログインしている場合は権限に応じてリダイレクト
			$authority = $auth->getIdentity()->kind;
			common::redirect($authority);
		}else{
			//ログインしていない
			if($this->getRequest()->getPost('submit')){
				//#########　認証　#########
				//パスワードを暗号化
				$postArr = $this->_db->postArray();
				$pw = common::pwHash($postArr['pw']);
				//マーチャント番号が付与されていた場合
				if($postArr['m']){
					//マーチャントに対してユーザー登録が行われているか確認
					$merchant = $this->_db->fetchAll(
						$this->_db->select()
						->from("ImageUser","id")
						->where("space=?",$postArr['m'])
					);
					$user = $this->_db->fetchAll(
						$this->_db->select()
						->from("ImageUser","*")
						->where("parent=?",$merchant[0]['id'])
						->where("mail=?",$postArr['id'])
						->where("pw=?",$pw)
					);
					if(empty($user)){
						//マーチャントに対してユーザー登録が行われていない場合は、エラーメッセージを表示
						$this->view->Msg = "入力された情報では、ユーザー登録されていません。<br />";
					}else{
						if($user[0]['active'] == 1){
							//マーチャントに対してユーザー登録が行われている場合
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
								//会社情報を取得
								$company = $this->_db->fetchAll(
									$this->_db->select()
									->from("company")
									->where("parent=?",$data->parent)
								);
								$data->company = $company[0]['company'];
								//現在の共有フォルダの容量を計算
								$size = $this->dir_size(HOMEDIR."/image/".$data->parent);
						    //規定容量を超過しているか保存
								$setting = new Model_Indexsettings;
						    if($size >= $setting->setting(88)['global']['defaultSize']){
									$data->over = 1;
						    }else{
									$data->over = 0;
								}
								$data->size = $size;
								$data->m = $postArr['m'];
								$auth->getStorage()->write($data);
								//var_dump($data->company);
								//認証がOKの場合は権限に応じてリダイレクト
								$authority = $auth->getIdentity()->kind;
								common::redirect($authority);
							}elseif($user[0]['active'] == 0){
								$this->view->Msg = "現在利用できません<br />";
							}
						}else{
							//認証がNGの場合はエラーメッセージを表示
							$this->view->Msg = "メールアドレス若しくはパスワードが正しく入力されていません。<br />";
						}
					}
				}else{//マーチャント番号が付与されていない場合
					//マーチャントに対してユーザー登録が行われているか確認
					$user = $this->_db->fetchAll(
						$this->_db->select()
						->from("ImageUser")
						->where("kind!=3")
						->where("kind!=4")
						->where("mail=?",$postArr['id'])
						->where("pw=?",$pw)
					);
					if(empty($user)){
						//管理者、画像編集者、マーチャントとして登録されていない場合
						$this->view->Msg = "メールアドレス若しくはパスワードが正しく入力されていません。<br />";
					}else{
						if($user[0]['active'] == 1){
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
								if($data->kind == 2){
									//スタッフの場合は会社名を追加
									$parent = $this->_db->fetchAll(
										$this->_db->select()
										->from("ImageUser")
										->where("kind=4")
										->where("id=?",$user[0]['parent'])
									);
									$data->company = $parent[0]['company'];
								}
								$auth->getStorage()->write($data);
								//認証がOKの場合は権限に応じてリダイレクト
								$authority = $auth->getIdentity()->kind;
								common::redirect($authority);
							}else{
								//認証がNGの場合はエラーメッセージを表示
								$this->view->Msg = "メールアドレス若しくはパスワードが正しく入力されていません。<br />";
							}
						}elseif($user[0]['active'] == 0){
							$this->view->Msg = "現在利用停止中です<br />";
						}
					}
				}
			}
		}
	}
	public function agreementAction() {
		//タイトル
		$this->view->title = "規約の同意";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function signupAction(){
		if($this->getRequest()->m){
			var_dump($this->getRequest()->m);
		}
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","memberRegister")
		);
		$this->view->html =$html;
		//タイトル
		$this->view->title = "ユーザー登録";
		$this->view->bread = '<li><a href="'.BASEURL.'"/" title="'.$this->_setting['global']['SiteName'].'"><i class="fa fa-tachometer"></i>　トップ</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function confurmAction(){
		//タイトル
		$this->view->title = "ユーザー登録(確認)";
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
		$this->view->title = "ユーザー登録(完了)";
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
			$postArr['kind'] = 4;
			$postArr['active'] = 0;
			$postArr['parent'] = 88;
			$postArr['demand'] = 0;
			$postArr['created'] = date("Y-m-d H:i:s");
			$this->view->detail = $postArr;
			if($l_Id = $this->_db->insertAndGetLastId("ImageUser",$postArr)){
					//結果を送る
					$this->view->register = true;
					$this->view->detail = $postArr;
					//メールを送付
					//$this->sendmail(88,$postArr);
			}
		}
	}
	//ログアウト
	public function logoutAction(){
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		@session_destroy();
		$cookie_params = session_get_cookie_params();
		setcookie(session_name(), '', time()-42000, $cookie_params['path']);
			if($this->getRequest()->getparam('m')){
				//ユーザーの場合、マーチャントのログイン画面へ転送
				common::redirect("",$this->getRequest()->getparam('m'));
			}else{
				//ユーザーでない場合は、通常のログイン画面へ転送
				common::redirect();
			}
	}
	//パスワードを暗号化する例を
	public function hashAction() {
		echo common::pwHash($this->getRequest()->getParam('id'));
		//echo 123;

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
		$detailStr.= "ユーザー情報\n";
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
	private function dir_size($dir){
		$handle = opendir($dir);
		while ($file = readdir($handle)) {
			if ($file != '..' && $file != '.' && !is_dir($dir.'/'.$file)) {
				$mas += filesize($dir.'/'.$file);
			} else if (is_dir($dir.'/'.$file) && $file != '..' && $file != '.') {
				$mas += $this->dir_size($dir.'/'.$file);
			}
		}
		return $mas;
	}
}
?>
