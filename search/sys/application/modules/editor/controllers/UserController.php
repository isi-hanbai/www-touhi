<?php
define("IMG_DIR", HOMEDIR."/catch/");
class Editor_UserController extends Common_EditorController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Editorimageuser();
		$this->_table ="imageuser";

		//都道府県リストを表示
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		$this->view->pref  = $this->pref;

		//会員分類を取得
		$this->classes = $this->_db->fetchAll(
			$this->_db->select()
			->from("userClassification")
			->where("parent=?",$this->_user->parent)
			->where("hidden!=1")
		);

		array_unshift($this->classes,array("id"=>0,"name"=>"指定なし"));
		$this->view->classes  = $this->classes;

		$this->view->Auth = $this->_user->Authority;
		$this->view->outsource = $this->_user->outsource;
	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　会員管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/user"　class="btn disabled"><i class="fa fa-cog"></i>　会員管理</a> <span class="divider">/</span>
													</li>';
		//GETパラメータを取得
		$kind = 3;
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$classes = $this->_request->getParam("classes");
		$limit = $this->_request->getParam("limit");

		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//会員リストの読み込み
		$user = $this->_db->getUsers($kind,$keyword,$classes,$p,$limit,$this->_user->parent);
		$this->view->users = $user[0];
		//ページャーを生成
		$this->userpager($keyword,$classes,$user[1],$limit,$p,"/editor/user/");
		//common::userpager($keyword,$user[1],$limit,$p,"/merchant/user/");
	}
	//詳細
	public function detailAction() {
		//$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getUserDetail($this->_request->getParam("id"));
		//タイトルの定義
		$this->view->reg = $postArr['reg'];
		$this->view->update = $postArr['update'];
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->data['name'];
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/user"><i class="fa fa-cog"></i>　会員管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//登録
	public function registrerAction() {
		$this->view->comData = $this->_user->comData;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　会員登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/user"><i class="fa fa-cog"></i>　会員管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			try{
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//パスワードを暗号化
			if(!empty($postArr['pw'])){
				$postArr['pw'] = $this->_db->pwHash($postArr['pw']);
			}
			//会員ラベルの付与と親をログイン会員にする
			$postArr['kind'] = 3;
			$postArr['parent'] = $this->_user->parent;
			$postArr['updated'] = date("Y-m-d H:i:s"); // 現在日時をセット
			$added = json_decode($postArr['added'],true);
			unset($postArr['added']);
			//DBに登録
			/*
			$keys = array("company","name","name2","kana","kana2","mail","pw","kind","class","active","parent","space","tel","tel2","tel3","mtel","mtel2","mtel3","zip","zip2","pref","addr","addr2","division","fax","created","demand","sex","birthY","birthM","birthD","trader","branch","plan","payment_method","joinYear","joinMonth","joinDay","outsource","Authority","image","stop","cancellation","tmp","yobou","houmon");
			$arr = array();
			foreach($keys as $vv){
				$ck = false;
				foreach($postArr as $k=>$v){
					if($k == $vv){
						$arr[$k] = $v;
						$ck = true;
						break;
					}
				}
				if(!$ck){
					$arr[$vv] = "";
				}
			}
			*/
			// var_dump($postArr);
			
			if($lastId = $this->_db->insertAndGetLastId($this->_table,$postArr)){
				//インサート処理後に行う処理を記載
				//追加項目を登録
				$added["user"] = $lastId;
				$added["parent"] = $this->_user->parent;
				$this->_db->insert("facilityInfomation",$added);
				header("location:".BASEURL."/editor/user/update/?reg=ture&id=".$lastId);
			}
				/*
			*/
			} catch (Exception $e) {
            var_dump($e->getMessage());
        	}
		}
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		if($postArr['reg']){
			$this->view->msg = "登録しました。";
			$this->view->reg = 1;
		}elseif($postArr['update']){
			$this->view->msg = "更新しました。";
			$this->view->update = 1;
		}
		//$postArr = $this->_db->getArray();
		$this->detail = $this->_db->getUserDetail($postArr["id"]);
		if($this->detail['tmp']){
			$tmp = json_decode($this->detail['tmp'],true);
			if(!is_array($tmp)){
				$this->detail['tmp'] = array($tmp);
			}else{
				$this->detail['tmp'] = $tmp;
			}
		}
		$this->view->detail = $this->detail;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/user"><i class="fa fa-cog"></i>　会員管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//パスワードを暗号化
			if($postArr['pwactive'] ){
				$postArr['pw'] = $this->_db->pwHash($postArr['pw']);
			}
			//DBに登録
			$arr = array();
			foreach($postArr as $k=>$v){
				//idとpwactiveを除外
				if($k == "id"){
					$id = $v;
				}elseif($k == "pwactive"){
				}elseif($k == "added"){
					$added = json_decode($postArr['added'],true);
				}else{
					$arr[$k] = $v;
				}
			}
			if(!isset($postArr['houmon'])){
				$arr['houmon'] = 0;
			}
			if(!isset($postArr['yobou'])){
				$arr['yobou'] = 0;
			}
			var_dump($postArr);
			$arr['updated'] = date("Y-m-d H:i:s");
			$this->_db->update($this->_table,$arr,"id=".$id);
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

			}
			//var_dump($postArr);
			header("location:".BASEURL."/editor/user/update/?update=ture&id=".$id);

		}
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


	//会員用ページャーの生成
	private function userpager($keyword="",$classes=0,$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination pagination-sm\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&classes=".$classes."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\">
<span aria-hidden=\"true\">&laquo; 前へ</span>
</a>
</li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&classes=".$classes."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&classes=".$classes."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			/*
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&classes=".$classes."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			*/
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li>
<a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&classes=".$classes."&limit=".$limit."&p=".$next."\" aria-label=\"Next\">
<span aria-hidden=\"true\">次&raquo;</span>
</a>
</li>";
			}
			$pager.= "</ul></div>";
			$this->view->pager = $pager;
		}
	}
}
?>
