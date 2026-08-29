<?php
ini_set('display_errors',0);
define("IMG_DIR","/img/");
define("FONT_DIR","/font/");
class Admin_FontController extends Common_AdminController {
	//初期化メソッドの定義
	private $_db;
	private $_user;
	private $_table;
	private $ja;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Adminimageuser();

		////日本語を定義
		$this->ja = "フォント";
		$this->view->ja = $this->ja;
		////コントローラー名を定義
		$this->controller = $this->getRequest()->getControllerName();
		$this->view->controller = $this->controller;
		$this->_table = $this->controller;
		///////////ここからこのコントローラーのみの設定を読み込み
		/*
		//投票ルール
		$pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		array_unshift($pref,array("id"=>"","name"=>"選択"));
		$this->view->pref = $pref;
		*/
	}
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->ja.'登録';
		$this->view->bread = '<li><a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>'.$this->view->title.'</li>';
		//該当月のFax送信履歴を表示
		//GETパラメータを取得
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		if(!empty($keyword)){
			$where = " WHERE name LIKE('%".$keyword."%')";
		}
		$data = $this->_db->fetchAll(
			"SELECT * FROM `font`".$where." ORDER BY id DESC LIMIT {$start} , {$limit}"
		);
		$this->view->users = $data;

		$data2 = $this->_db->fetchAll(
			"SELECT COUNT(*) AS n FROM `font`".$where
		);
		//ページャーを生成
		$this->userpager($keyword,$data2[0]['n'],$limit,$p,"/admin/".$this->controller."/");
	}

	//登録・編集
	public function editAction() {
		$postArr = $this->_db->getArray();
		$this->view->mode = $postArr['mode'];


		if($postArr['mode'] == "register"){
			if($this->_request->isPost()){
				//POSTデータのエスケープ処理
				$postArr = $this->_db->postArray();
				//会員ラベルの付与と親をログインユーザーにする
				//$postArr['parent'] = $this->_user->id;
				$postArr['parent'] = 88;
				$postArr['created'] = date("Y/m/d H:i:s");
				unset($postArr['userfile']);
				unset($postArr['id']);
				if($lastId = $this->_db->insertAndGetLastId("font",$postArr)){
					//インサート処理後に詳細画面へ
					header("location:".BASEURL."/admin/{$this->controller}/edit/?id=".$lastId."&mode=detail&job=register");
				}
			}
			$getArr = $this->_db->getArray();
			//タイトルの定義
			$this->view->title = '<i class="fa fa-gift"></i>　'.$this->ja.'登録';
			$this->view->bread = '<li><a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
									<a href="'.BASEURL.'/admin/'.$this->controller.'"><i class="fa fa-gift"></i>　'.$this->ja.'管理</a> <span class="divider">/</span>
									'.$this->view->title.'</li>';
		}elseif($postArr['mode'] == "update"){
			if($this->_request->isPost()){
				//POSTデータのエスケープ処理
				$postArr = $this->_db->postArray();
				unset($postArr['userfile']);
				//DBに登録
				$arr = array();
				foreach($postArr as $k=>$v){
					//idとpwactiveを除外
					if($k == "id"){
						$id = $v;
					}else{
						$arr[$k] = $v;
					}
				}
				//アップデート処理
				$this->_db->update("font",$arr,$this->_db->quoteInto("`id`=?",$id));
				header("location:".BASEURL."/admin/{$this->controller}/edit/?id=".$id."&mode=detail&job=update&contest={$postArr['contestID']}");
			}

			$postArr = $this->_db->getArray();
			$data = $this->_db->fetchAll(
				$this->_db->select()
				->from("font")
				->where("id=?",$postArr['id'])
			);
			$this->view->data = $data[0];
			//タイトルの定義
			$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'] .'を編集';
			$this->view->bread = '<li><a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
									<a href="'.BASEURL.'/admin/'.$this->controller.'"><i class="fa fa-gift"></i>　'.$this->ja.'管理</a> <span class="divider">/</span>
									<a href="'.BASEURL.'/admin/'.$this->controller.'/edit/?mode=detail&id='.$postArr['id'].'">
									<i class="fa fa-gift"></i>　'.$this->view->data['name'] .'</a> <span class="divider">/</span>
									'.$this->view->title.'</li>';
		}elseif($postArr['mode'] == "detail"){
			$postArr = $this->_db->getArray();
			//詳細データの取得
			$data = $this->_db->fetchAll(
				$this->_db->select()
				->from("font")
				->where("id=?",$postArr['id'])
			);
			$this->view->data = $data[0];
			$link = " <a href=\"".BASEURL."/admin/font/\">".$this->ja."管理へ</a>";
			if($postArr['job'] == "register"){
				$this->view->msg = $data[0]['name']."を登録しました。".$link;
			}elseif($postArr['job'] == "update"){
				$this->view->msg = $data[0]['name']."を編集しました。".$link;
			}
			//タイトルの定義
			$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'の詳細';
			$this->view->bread = '<li><a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
									<a href="'.BASEURL.'/admin/'.$this->controller.'"><i class="fa fa-gift"></i>　'.$this->ja.'管理</a> <span class="divider">/</span>
									'.$this->view->title.'</li>';
		}
	}
	public function uploadAction() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			// 調べたい画像のパス
			$arr = explode('.', $_FILES['userfile']['name']);
			$ext = array_pop($arr);


			if($ext == "ttf" || $ext == "TTF" || $ext == "Ttf" || $ext == "otf"){
				//TTFの場合は、
				$filename = md5(time()).".".$ext;
				move_uploaded_file($_FILES['userfile']['tmp_name'], HOMEDIR.FONT_DIR.$filename);
				$this->view->path = HOMEDIR.FONT_DIR.$filename;
			}else{
				$this->view->error = true;
			}
		}
	}
	//会員用ページャーの生成
	private function userpager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination pagination-sm\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\">
<span aria-hidden=\"true\">&laquo; 前へ</span>
</a>
</li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
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
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li>
<a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\">
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
