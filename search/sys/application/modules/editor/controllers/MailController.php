<?php
define("IMG_DIR", HOMEDIR."/catch/");
class Editor_MailController extends Common_EditorController {
	//初期化メソッドの定義
	private $_db;
	private $_user;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			//セッション
			$this->_user = $auth->getIdentity();
			//DBへ接続
			$this->_db = new Model_Editorgeneral();
			//$this->_db = new Model_Merchantcontent();

      $this->view->Auth = $this->_user->Authority;
      $this->view->outsource = $this->_user->outsource;
		}

	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>'.$this->view->data['name'].'メール送信一覧';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';

		//GETパラメータを取得
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		//WHERE句を生成
		$where_arr = array();
		$where_arr[] = $this->_db->quoteInto("`parent` = ?",$this->_user->parent*1);
		if($keyword){
			foreach(explode(" ",mb_convert_kana($keyword,"s")) as $v){
				$where_arr[] = $this->_db->quoteInto("CONCAT(`subject`,`body`) LIKE '%?%'",$v);
			}
		}
		//SQL文を生成
		$sql = "SELECT *,
		(
			SELECT COUNT(*) FROM `mail` WHERE ".implode(" AND ",$where_arr)."
		) AS n
		FROM `mail`
		WHERE ".implode(" AND ",$where_arr)." LIMIT ".$start.",".$limit;
		$data = $this->_db->fetchAll($sql);

		if($data[0]['n'] > 0){
			$this->view->data = $data;
			//ページャーを生成
			$this->pager($keyword,$data[0]['n'],$limit,$p,"/editor/mail/");
		}else{
			$this->view->data = array();
		}
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　メール送信登録';
		$this->view->bread = '<li><a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
								<a href="'.BASEURL.'/editor/mail"><i class="fa fa-cog"></i>　メール送信一覧</a> <span class="divider">/</span>
								'.$this->view->title.'
							</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$postArr['parent'] = $this->_user->parent;
			$postArr['created'] = date("Y-m-d H:i:s");
			if($postArr['tmp']){
				$postArr['tmp'] = implode(",",json_decode($postArr['tmp'],true));
			}else{
				$postArr['tmp'] = "";
			}
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("mail",$postArr)){
				//2重登録防止の為リダイレクト
				header("location:".BASEURL."/editor/mail/update/?id=".$lastId);
			}
		}
	}
	//編集
	public function updateAction() {
		$postArr = $this->_db->getArray();
		$sql = "SELECT * FROM `mail` WHERE "
		.$this->_db->quoteInto("`id`=?",$postArr['id'])
		." AND ".$this->_db->quoteInto("`parent`=?",$this->_user->parent);

		$data = $this->_db->fetchAll($sql);
		//添付ファイルのアイコンを配列に
		$arr = array();
		$arr3 = explode(",",$data[0]['tmp']);
		foreach($arr3 as $v){
			if($v !=""){
				$arr2 = explode(".",$v);
				$exp = $arr2[count($arr2)-1];
				if($exp == "pdf"){
					$icon = BASEURL."/image/icon/pdf.png";
				}elseif($exp == "xls" || $exp == "xlsx"){
					$icon = BASEURL."/image/icon/xls.png";
				}elseif($exp == "doc" || $exp == "docx"){
					$icon = BASEURL."/image/icon/word.png";
				}elseif($exp == "ppt" || $exp == "pptx"){
					$icon = BASEURL."/image/icon/ppt.png";
				}elseif($exp == "jpg"){
					//jpeg
					$icon = BASEURL."/catch/".$v;
				}elseif($exp == "png"){
					//png
					$icon = BASEURL."/catch/".$v;
				}elseif($exp == "gif"){
					//gif
					$icon = BASEURL."/catch/".$v;
				}
				$arr[] = array("file"=>$v,"icon"=>$icon);
			}
		}
		$data[0]['tmp'] = $arr;
		$this->view->detail = $data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　'.$data[0]['name'];
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/mail"><i class="fa fa-cog"></i>　メール送信管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$postArr['tmp'] = implode(",",json_decode($postArr['tmp'],true));
			//データの整理
			$arr = array();
			foreach($postArr as $k=>$v){
				//idを除外
				if($k == "id"){
					$id = $v;
				}else{
					$arr[$k] = $v;
				}
			}
			//DBに登録
			$this->_db->update("mail",$arr,"`id`=".$id);
			header("location:".BASEURL."/editor/mail/update?finish=1&id=".$id);
			var_dump($arr);
			/*
			*/
		}
		//タイトルの定義
		$this->view->title = "部門編集完了";
		$this->view->title = '<i class="fa fa-user"></i>　部門編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/division"><i class="fa fa-cog"></i>　部門管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/division/detail/?id='.$postArr['id'].'"><i class="fa fa-cog"></i>'.$this->view->data['name'].'の部門登録情報</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
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
				}elseif(preg_match("/.xlsx/i",$_FILES['userfile']["name"])){
					//xlsx
					$filename = $f_name."_".date("Y-m-d-H-i-s").".xlsx";
					$icon = BASEURL."/image/icon/xls.png";
				}elseif(preg_match("/.xls/i",$_FILES['userfile']["name"])){
					//xls
					$filename = $f_name."_".date("Y-m-d-H-i-s").".xls";
					$icon = BASEURL."/image/icon/xls.png";
				}elseif(preg_match("/.docx/i",$_FILES['userfile']["name"])){
					//docx
					$filename = $f_name."_".date("Y-m-d-H-i-s").".docx";
					$icon = BASEURL."/image/icon/word.png";
				}elseif(preg_match("/.doc/i",$_FILES['userfile']["name"])){
					//doc
					$filename = $f_name."_".date("Y-m-d-H-i-s").".doc";
					$icon = BASEURL."/image/icon/word.png";
				}elseif(preg_match("/.pptx/i",$_FILES['userfile']["name"])){
					//pptx
					$filename = $f_name."_".date("Y-m-d-H-i-s").".pptx";
					$icon = BASEURL."/image/icon/ppt.png";
				}elseif(preg_match("/.ppt/i",$_FILES['userfile']["name"])){
					//ppt
					$filename = $f_name."_".date("Y-m-d-H-i-s").".ppt";
					$icon = BASEURL."/image/icon/ppt.png";
				}elseif(preg_match("/.jpeg/i",$_FILES['userfile']["name"]) || preg_match("/.jpg/i",$_FILES['userfile']["name"])){
					//jpeg
					$filename = $f_name."_".date("Y-m-d-H-i-s").".jpg";
					$icon = BASEURL."/catch/".$filename;
				}elseif(preg_match("/.png/i",$_FILES['userfile']["name"])){
					//png
					$filename = $f_name."_".date("Y-m-d-H-i-s").".png";
					$icon = BASEURL."/catch/".$filename;
				}elseif(preg_match("/.gif/i",$_FILES['userfile']["name"])){
					//gif
					$filename = $f_name."_".date("Y-m-d-H-i-s").".gif";
					$icon = BASEURL."/catch/".$filename;
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

	//ページャーの生成
	private function pager($keyword="",$n=0,$limit=10,$p=0,$page){
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
