<?php
class Signin_resetController extends Common_SigninController {
	//初期化メソッドの定義
	private $_db;
	public function init(){
		$this->_db = new Model_Indeximageuser();
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			$this->view->loginout = '<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin">ログイン</a>';
		}else{
			$this->view->loginout =
				'<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin/index/logout/">ログアウト</a>
				<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/member/">マイページ</a>';
		}
	}

	//パスワードのリセット
	public function indexAction() {
		//タイトル
		$this->view->title = "パスワードリセット";
		//URLにトークンが付与されているか確認
		if(!$this->_request->getParam('token')){
			$this->view->Msg = array('message'=>"不正なアクセスです",'color'=>'danger');
		}else{
			//付与されている場合は、DBと照合
			$token = htmlspecialchars($this->_request->getParam('token'));

			$isSetToken = $this->_db->fetchAll(
				//ユーザー情報を取得
				$this->_db->select()
					->from("passtoken",'COUNT(*)')
					->where('token = ?',$token)
			);
			if($isSetToken[0]['COUNT(*)'] >0 ){
				//トークンがDBに存在する場合はフォームを表示
				$this->view->isToken = true;
				$this->view->token = $token;
			}else{
				//トークンがDBに存在しない場合はメッセージを表示
				$this->view->Msg = array('message'=>"パスワードリセット用のURLは、1度しか利用できません。すでに変更処理を行ったURLであるか、存在しないURLです。",'color'=>'danger');
			}


			//リセットフォームを送信された場合
			if($this->_request->isPost()){
				$postArr = $this->_db->postArray();
				//パスワードが一致するか確認
				if($postArr['pw'] == $postArr['pw2']){
					//一致する場合
					//パスワードリセット時に登録されたIDを取得
					$user = $this->_db->fetchAll(
						//ユーザー情報を取得
						$this->_db->select()
							->from("passtoken",array('UserId'))
							->where('token = ?',$token)
					);
					if($this->_db->update("ImageUser",array("pw"=>$this->_db->pwHash($postArr['pw'])),"`id`=".$user[0]['UserId'])){
						//アップデート処理が完了した場合
						if($user[0]['parent']>0){
							$this->view->Msg2 = array('message'=>"パスワードのリセットが完了しました。<a href=\"".BASEURL."/signin/?m=".$user[0]['parent']."\">ログインフォーム</a>よりログインしてください。",'color'=>'success');
						}else{
							$this->view->Msg2 = array('message'=>"パスワードのリセットが完了しました。<a href=\"".BASEURL."/signin/\">ログインフォーム</a>よりログインしてください。",'color'=>'success');
						}
						//該当するリクエストを削除
						$this->_db->delete("passtoken","`token` ='".$token."'");
					}else{
						//アップデート処理が完了できなかった場合
						//同一パスワードで登録された場合

						//サーバーの都合により出来なかった場合
						$this->view->Msg = array('message'=>"登録を完了できませんでした。ブラウザを閉じて再度URLにアクセスしてください。",'color'=>'danger');
					}
				}else{
					//一致しなかった場合
					$this->view->Msg3 = array('message'=>"入力されたパスワードが一致しません。",'color'=>'danger');
				}
			}
		}
	}
}
?>
