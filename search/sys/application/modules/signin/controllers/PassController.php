<?php
class Signin_PassController extends Common_SigninController {
	//初期化メソッドの定義
	private $_db;
	public function init(){
		$this->_db = new Model_Indeximageuser();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		//$this->_setting = $setting->setting("88");
		$this->_setting = $setting->setting("2998");
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			$this->view->loginout = '<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin">ログイン</a>';
		}else{
			$this->view->loginout =
				'<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin/index/logout/">ログアウト</a>
				<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/member/">マイページ</a>';
		}
	}
	//パスワードを忘れた方
	public function indexAction() {

		//タイトル
		$this->view->title = "パスワードを忘れた方";
		$parent = htmlspecialchars($this->_request->getParam('m'));
		if($parent){
			$this->view->m = $parent;
		}
		if($this->_request->isPost()){
			//同一IPからの1時間以内のリクエストは排除
			$ipAddress = $_SERVER["REMOTE_ADDR"];
			$datetime = date('Y-m-d H:i:s');
			$token = $this->_db->pwHash($datetime);
			$isSetToken = $this->_db->fetchAll(
				//現存するリクエストを取得
				$this->_db->select()
					->from("passtoken",'COUNT(*)')
					->where('ipAddress = ?',$ipAddress)
					->where("`created` > current_timestamp + INTERVAL 1 HOUR")
			);
			//同一IPからのリクエストが存在する場合
			if($isSetToken[0]['COUNT(*)'] >0 ){
				$this->view->Msg = array('message'=>"同一IPアドレスからのパスワードリセットのリクエストは、一定時間中は複数回行えません。",'color'=>'danger');
			}else{
				//同一IPからのリクエストが存在しない場合
				$postArr = $this->_db->postArray();
				if($parent){
					//マーチャント番号が付与されている場合
					$user = $this->_db->fetchAll(
						//ユーザー情報を取得
						$this->_db->select()
							->from("ImageUser",array('COUNT(*)','id'))
							->where('mail=?',$postArr['mail'])
							->where('parent=?',$parent)
					);
				}else{
					//マーチャント番号が付与されていない場合
					$user = $this->_db->fetchAll(
						//ユーザー情報を取得
						$this->_db->select()
							->from("ImageUser",array('COUNT(*)','id'))
							->where('mail=?',$postArr['mail'])
					);
				}
				if($user[0]['COUNT(*)'] <=0 ){
					//ユーザーが存在しなかった場合
					$this->view->Msg = array('message'=>"該当するユーザーが登録されていません",'color'=>'danger');
				}else{
					//ユーザーが存在した場合
					//パスワードリセット用のメールで送信して
					$url = BASEURL."/signin/reset?token=".$token;
					function mbCnv($string) {
						return mb_convert_encoding($string, 'ISO-2022-JP', 'UTF-8');
					}
					function mbMime($string) {
						return mb_encode_mimeheader(mbCnv($string), 'ISO-2022-JP', 'B');
					}

					$subject = 'パスワードのリセット';
					$body = "パスワードリセットのリクエストがありました。心当たりのない方は、このメールを無視してください。\n";
					$body.= "パスワードリセット用URL：　".$url." \n";
					$mail = new Zend_Mail('ISO-2022-JP');
					$mail->setSubject(mbCnv($subject));
					$mail->addTo($postArr['mail'], mbMime($postArr['mail']));
					$mail->setFrom($this->_setting['global']['infoMail'], mbMime($this->_setting['global']['infoMail']));
					$mail->setBodyText(mbCnv($body), null, Zend_Mime::ENCODING_7BIT);
					$config = array(
						'port'=>$this->_setting['global']['SMTPport'],
						'auth' => '',
						'username' => $this->_setting['global']['SMTPuser'],
						'password' => $this->_setting['global']['SMTPpass']
					);
					$smtp = new Zend_Mail_Transport_Smtp($this->_setting['global']['SMTPhost'], $config);
					try {
						$mail->send($smtp);
							$this->view->Msg = array('message'=>"パスワードのリセット用のURLを".$postArr['mail']."宛に送信しました。",'color'=>'success');
					} catch (Zend_Exception $e) {
							$this->view->Msg = array('message'=>"メールの送信ができませんでした。:".$e->getMessage(),'color'=>'danger') or die;
					}
					//DBを登録
					if(empty($parent)){
						$parent = 2998;
					}
					$arr = array(//データ
						"UserId"=>$user[0]['id'],//id
						"token"=>$token,//トークン
						"created"=>$datetime,//作成日時
						"ipAddress"=>$ipAddress,//IPアドレス
						"parent"=>$parent//マーチャント番号
					);
					$this->_db->insert("passtoken",$arr);
				}
			}
		}
	}
}
?>
