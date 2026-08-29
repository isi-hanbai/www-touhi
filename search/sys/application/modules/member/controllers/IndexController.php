<?php
define("IMG_DIR", HOMEDIR."/catch/");
// IndexController
class Member_IndexController extends Common_MemberController {
	//初期化メソッドの定義
	private $_db;
	private $_user;
	private $_merchant;
	public function init(){
		//ユーザー情報の取得
		$this->_db =  new Model_Memberimageuser();
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/signin/");
		}else{
			$this->_user = $auth->getIdentity();
			$this->user = $this->_db->getUserDetail($this->_user->id);
			$this->view->userdetail = $this->user;
			$this->view->loginout =
				'<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/signin/index/logout/">ログアウト</a>
				<i class="fas fa-user margin-right-10"></i><a href="'.BASEURL.'/member/">マイページ</a>';
		}
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		$this->view->pref = $this->pref;

		//会員分類を取得
		$this->classes = $this->_db->fetchAll(
			$this->_db->select()
			->from("userClassification")
			->where("parent=?",$this->_user->parent)
		);
		array_unshift($this->classes,array("id"=>0,"name"=>"指定なし"));
		$this->view->classes  = $this->classes;
	}
	public function hitokuchimemoAction() {
		$this->view->title = "健康一口メモ";

		$db = new Model_Indexgeneral();
		$category = $db->fetchAll(
			$db->select()
				->from("content_category")
				->where("parent=?", 2998)
				->where("name=?", "健康一口メモ")
				->limit(1)
		);
		$this->view->hitokuchiMemoCategoryId = !empty($category) ? (int)$category[0]['id'] : null;
	}
	public function indexAction() {
		$this->view->title = "マイページ";

		$db = new Model_Indexgeneral();
		$category = $db->fetchAll(
			$db->select()
				->from("content_category")
				->where("parent=?", 2998)
				->where("name=?", "健康一口メモ")
				->limit(1)
		);
		$this->view->hitokuchiMemoCategoryId = !empty($category) ? (int)$category[0]['id'] : null;

		//ページ独自のスクリプトを読み込み
		$this->view->add_script = '<script src="'.BASEURL.'/js/validation.js"></script>\n<script src="'.BASEURL.'/js/member/member.js"></script>';

		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();

			if(is_array($this->_request->getPost("tmp"))){
				$postArr['tmp'] = implode(",",$this->_request->getPost("tmp"));
			}else{
				$postArr['tmp'] = "";
			}
			//データを整理
			//追加情報
			$added = $this->_request->getPost("added");
			unset($postArr['added']);
			//idを削除
			$id = $postArr['id'];
			unset($postArr['id']);
			//パスワードを削除
			$pwactive = $postArr['pwactive'];
			if($pwactive){
				unset($postArr['pwactive']);
				//パスワードが変更された場合
				$pw = $this->pwhash($postArr['pw']);
				$postArr['pw'] = $pw;
			}else{
				unset($postArr['pw']);
				$pw = $this->_user->pw;
				$postArr['pw'] = $pw;
			}

			//データベースを更新
			$postArr['updated'] = date("Y-m-d H:i:s");
			$this->_db->update("ImageUser",$postArr,$this->_db->quoteInto("id=?",$id));
			if(!empty($added)){
				//ユーザーに応じた追加情報があるか確認
				$n = $this->_db->fetchAll(
					$this->_db->select()
					->from("facilityInfomation",array("COUNT(*) AS c"))
					->where("parent=?",$this->_user->parent)
					->where("user=?",$id)
				);
				if($n[0]['c'] >0){
					//更新
					$this->_db->update("facilityInfomation",$added,$this->_db->quoteInto("`user`=?",$id));
				}else{
					//登録
					$added["user"] = $id;
					$added["parent"] = $this->_user->parent;
					$this->_db->insert("facilityInfomation",$added);
				}
				$this->view->msg = "更新しました";
			}
		}
		$this->user = $this->_db->getUserDetail($this->_user->id);
		$this->view->userdetail = $this->user;
	}
	public function uploadAction() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			$postArr = $this->_db->postArray();
			// 調べたい画像のパス
			$filesize = filesize($path);
			if($postArr['filesize']*1+$filesize > 3000000){
				$str = '<script>alert("1メールあたりの添付ファイル容量を超えています。")</script>';
				echo $str;
				exit();
			}else{
				$ck = true;
				$f_name = explode(".",$_FILES['userfile']["name"])[0];
				if(preg_match("/.pdf/i",$_FILES['userfile']["name"])){
					//pdf
					$filename = $f_name."_".date("Y-m-d-H-i-s").".pdf";
					$icon = BASEURL."/image/icon/pdf.png";
				}else{
					//jpg,gif,png,pdf,xls,doc,xlsx,docx,ppt,pptx以外のファイルは受付しない
					$str = '<script>alert("ファイル形式が許可されていません。")</script>';
					echo $str;
					$ck = false;
				}
				if($ck){

					if(move_uploaded_file($path,IMG_DIR.$filename)){
						//テンポラリ内の不要になったキャッシュを削除
						unlink($path);
						$this->view->success = true;
						$this->view->filename = $filename;
						$this->view->extention = $extention;
						$this->view->icon = $icon;
					}
				}
			}
		}else{
			$str = '<script>alert("ファイルが添付されていません。")</script>';
			echo $str;
		}
	}

	private function pwHash($pw){
		return sha1("serialize".md5($pw)."by_kmjcrew");
	}
}
?>
