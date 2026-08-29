<?php
define("IMG_DIR","image/");

class Editor_EditorController extends Common_EditorController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	private $_pointsetting;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
      $this->view->m = $this->_user->m;
      $this->view->Auth = $this->_user->Authority;
      $this->view->outsource = $this->_user->outsource;
		}
		$this->_db = new Model_Editoreditor();
		//スタッフ種別リストの読み込み
		$userKind = $this->_db->fetchAll(
			$this->_db->select()
			->from("ImageUserKind")
		);
		$this->view->userKind = $userKind;
		$this->_table ="ImageUser";

		//都道府県リストを表示
		$this->pref = $this->_db->fetchAll(
			$this->_db->select()
			->from("pref")
		);
		$this->view->pref  = $this->pref ;

		//部門を取得
		$this->division = $this->_db->fetchAll(
			$this->_db->select()
			->from("division")
			->where("parent=?",$this->_user->parent)
		);
		array_unshift($this->division,array("id"=>"","name"=>"選択"));
		$this->view->division  = $this->division ;

		//権限を取得
		$this->Authority = $this->_db->fetchAll(
			"SELECT * FROM `Authority` WHERE name!='特権管理者' AND activate=1"
		);
		array_unshift($this->Authority,array("id"=>0,"name"=>"未選択"));
		$this->view->Authority  = $this->Authority ;
	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　スタッフ管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"　class="btn disabled"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
													</li>';
		//GETパラメータを取得
		$kind = $this->_request->getParam("kind");
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$factory = $this->_request->getParam("factory");
		$division = $this->_request->getParam("division");
		$Authority = $this->_request->getParam("Authority");
		$limit = $this->_request->getParam("limit");

		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		//スタッフリストの読み込み
		$user = $this->_db->getUsers($kind,$keyword,$Authority,$division,$p,$limit,$this->_user->parent);
		$this->view->users = $user[0];
		//ページャーを生成
		$this->userpager($keyword,$user[1],$limit,$p,$Authority,$division,"/editor/editor/");
	}
	/*
	//詳細
	public function detailAction() {
		$postArr = $this->_db->getArray();
		$data = $this->_db->getUserDetail($postArr['id']);
		$this->view->data = $data;
		$editorSkil = $this->_db->fetchAll(
			$this->_db->select()
			->from("editorSkil")
			->where("editor=?",$postArr['id'])
		);
		$this->view->editorSkil = $editorSkil;

		$skil = $this->_db->fetchAll(
			$this->_db->select()
			->from("skil")
			->where("division=?",$data['division'])
		);
		$this->view->skil = $skil;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->data['name'].'様';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->_sougi = new Model_Merchantsougi();
		$this->view->sougi = $this->_sougi->sougiUser($postArr['id'],$this->_user->parent);
	}
	*/
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　スタッフ登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//パスワードを暗号化
			if(!empty($postArr['pw'])){
				$postArr['pw'] = $this->_db->pwHash($postArr['pw']);
			}
			//スタッフラベルの付与と親をログインスタッフにする
			$postArr['kind'] = 2;
			$postArr['active'] = 1;
			$postArr['created'] = date("Y-m-d H:i:s");
			$postArr['parent'] = $this->_user->parent;
			$postArr['outsource'] = 0;
			//スキル情報を整理
			$skil = json_decode($postArr['skil'],true);
			$skilKind = json_decode($postArr['skilKind'],true);
			unset($postArr['skil']);
			unset($postArr['skilKind']);
			unset($postArr['userfile']);
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId($this->_table,$postArr)){
				if($this->_db->update($this->_table,array("space"=>$this->_db->pwHash($lastId)),$this->_db->quoteInto("id=?",$lastId))){
					//スキル
					foreach($skil as $k=>$v){
						$arr = array("value"=>$v,"editor"=>$lastId,"skil"=>$skilKind[$k]);
						$this->_db->insertAndGetLastId("editorSkil",$arr);
					}
					//インサート処理後に行う処理を記載
					header("location:".BASEURL."/editor/editor/update/?finish=1&id=".$lastId);
				}
			}
			/*
			var_dump($postArr);
			*/
		}
	}
	public function registrer2Action() {
		$postArr = $this->_db->getArray();
		$data = $this->_db->getUserDetail($postArr['id']);
		$this->view->data = $data;
		$editorSkil = $this->_db->fetchAll(
			$this->_db->select()
			->from("editorSkil")
			->where("editor=?",$postArr['id'])
		);
		$this->view->editorSkil = $editorSkil;

		$skil = $this->_db->fetchAll(
			$this->_db->select()
			->from("skil")
			->where("division=?",$data['division'])
		);
		$this->view->skil = $skil;
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　スタッフ登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor/registrer"><i class="fa fa-user"></i>　スタッフ登録</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集
	public function updateAction() {
		//$postArr = $this->_db->getArray();

		$this->view->detail = $this->_db->getUserDetail($this->_request->getParam("id"));
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->detail['name'].$this->view->detail['name2'];
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';

		/**/
	}
	//編集完了
	public function updatefinishAction() {
		//タイトルの定義
		$this->view->title = "スタッフ編集完了";
		$this->view->title = '<i class="fa fa-user"></i>　スタッフ編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor/detail/?id='.$postArr['id'].'"><i class="fa fa-cog"></i>'.$this->view->data['name'].'様のスタッフ登録情報</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//パスワードを暗号化
			if($postArr['pwactive'] ){
				$postArr['pw'] = $this->_db->pwHash($postArr['pw']);
			}
			//スキル情報を整理
			$skil = json_decode($postArr['skil'],true);
			$skilKind = json_decode($postArr['skilKind'],true);
			unset($postArr['skil']);
			unset($postArr['skilKind']);
			//DBに登録
			$arr = array();
			foreach($postArr as $k=>$v){
				//idとpwactiveを除外
				if($k == "id"){
					$id = $v;
				}elseif($k == "pwactive" || $k == "userfile"){
				}else{
					$arr[$k] = $v;
				}
			}
			$arr['space'] = $this->_db->pwHash($id);
			$this->_db->update($this->_table,$arr,"`id`=".$id);
			//スキル
			foreach($skil as $k=>$v){
				$n = $this->_db->fetchAll(
					$this->_db->select()
					->from("editorSkil",array("COUNT(*) AS n"))
					->where("editor=".$id." AND skil=".$skilKind[$k])
				);
				if($n[0]['n'] ==0){
					$arr = array("value"=>$v,"editor"=>$id,"skil"=>$skilKind[$k]);
					$this->_db->insertAndGetLastId("editorSkil",$arr);
				}else{
					$this->_db->update("editorSkil",array("value"=>$v),"editor=".$id." AND skil=".$skilKind[$k]);
				}
			}
			header("location:".BASEURL."/editor/editor/update/?updatefinish=1&id=".$id);
			var_dump($arr);
			/*
			*/
		}
	}
	//CSVインポート
	public function importAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　一括登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor/import"　class="btn disabled">'.$this->view->title.'</a>
													</li>';

	}
	//CSVインポート完了
	public function importfinishAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　一括登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor"><i class="fa fa-cog"></i>　スタッフ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/editor/import">スタッフCSV一括登録</a>
														<a href="'.BASEURL.'/editor/editor/importfinish"　class="btn disabled">'.$this->view->title.'</a>
													</li>';
			//アップロードされたファイルを読み込み
			if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
				if (move_uploaded_file($_FILES["file"]["tmp_name"], APPLICATION_PATH."/files/" . $_FILES["file"]["name"])) {
					chmod(APPLICATION_PATH."/files/" . $_FILES["file"]["name"], 0644);
					echo $_FILES["file"]["name"] . "をアップロードしました。";
					$file_name = APPLICATION_PATH."/files/" . $_FILES["file"]["name"];
					$csv = file($file_name);
					//データ抽出
					$data = array();
					for($i=0;$i<count($csv);$i++){
						$row = explode(",",mb_convert_encoding(rtrim($csv[$i]),"UTF-8","SHIFT_JIS"));
						$data[] = array(
							"company"=>$row[0],
							"division"=>$row[1],
							"name"=>$row[2],
							"kana"=>$row[3],
							"mail"=>$row[4],
							"pw"=>$this->_db->pwHash($row[5]),
							"tel"=>$row[6],
							"fax"=>$row[7],
							"zip"=>$row[8],
							"pref"=>$row[9],
							"addr"=>$row[10],
							"addr2"=>$row[11],
							"kind"=>3,
							"active"=>1,
							"parent"=>$this->_user->id
						);
					}
					$this->view->Msg ="";
					//DBに登録
					foreach($data as $v){
						if($this->_db->insert($this->_table,$v)){
							$this->view->Msg.= $v['name']."を登録しました<br>";
						}
					}
					$this->view->Msg ="<a href=\"".BASEURL."/editor/editor/\">スタッフ管理画面へ進む</a>";
				} else {
					$this->view->Msg = "ファイルをアップロードできません。";
				}
			} else {
				$this->view->Msg = "ファイルが選択されていません。";
			}
	}

	public function upload5Action() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			$postArr = $this->_db->postArray();
			// 調べたい画像のパス
			//getimagesize関数で画像情報を取得する
			list($img_width, $img_height, $mime_type, $attr) = getimagesize($path);
			//list関数の第3引数にはgetimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
			switch($mime_type){
				//jpegの場合
				case IMAGETYPE_JPEG:
					//拡張子の設定
					$img_extension = "jpg";
				break;
				//pngの場合
				case IMAGETYPE_PNG:
					//拡張子の設定
					$img_extension = "png";
				break;
				//gifの場合
				case IMAGETYPE_GIF:
					//拡張子の設定
					$img_extension = "gif";
				break;
			}
			$this->view->type = $img_extension;
			if($img_extension == "gif"){
				$in = imagecreatefromgif($path); // 元画像ファイル読み込み
				$filename = "editor_".$postArr['id']."_".date("YmdHis").".gif";
			}elseif($img_extension == "jpg"){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = "editor_".$postArr['id']."_".date("YmdHis").".jpg";
			}elseif($img_extension == "png"){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = "editor_".$postArr['id']."_".date("YmdHis").".png";
			}else{
				//jpg,gif,png以外のファイルは受付しない
				$this->view->error = "FileTypeError";
			}
			$max = 300;
			//$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
			$width = imagesx($in); // 画像の幅を取得
			$height = imagesy($in); // 画像の高さを取得
			$min_width = $max; // 幅の最低サイズ
			$min_height = $max; // 高さの最低サイズ
			if($width >$min_width || $height == $min_height){
				if($width == $height) {
					$new_width = $min_width;
					$new_height = $min_height;
				} else if($width > $height) {//横長の場合
					$new_width = $min_width;
					$new_height = $height*($min_width/$width);
				} else if($width < $height) {//縦長の場合
					$new_width = $width*($min_height/$height);
					$new_height = $min_height;
				}
			}else{
				$new_width = $width;
				$new_height = $height;
			}
			//　画像生成
			$out = imagecreatetruecolor($new_width , $new_height);
			if($exif_data["Orientation"] == 6){
				$out = imagerotate($out,90, 0);
			}
			//プレースホルダを作成した画像にコピーして
			imagecopyresampled($out, $in,0,0,0,0, $new_width, $new_height, $width, $height);
			if($img_extension == "gif"){
				if(imagegif($out,IMG_DIR.$this->_user->parent."/".$filename) ==false){
					exit();
				}
			}elseif($img_extension == "jpg"){
				if(imagejpeg($out,IMG_DIR.$this->_user->parent."/".$filename) ==false){
					exit();
				}
			}elseif($img_extension == "png"){
				if(imagepng($out,IMG_DIR.$this->_user->parent."/".$filename) ==false){
					exit();
				}
			}
			//テンポラリ内の不要になったキャッシュを削除
			unlink($path);
			//登録した画像をDBに保存
			$upload_dir2 = '/'.IMG_DIR.$this->_user->parent."/";
			$this->view->filename = $upload_dir2.$filename;
			$this->view->success = true;
		}else{
			$this->view->error = "aaaaa";
		}
	}
	//会員用ページャーの生成
	private function userpager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination pagination-sm\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&Authority=".$Authority."&division=".$division."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\">
<span aria-hidden=\"true\">&laquo; 前へ</span>
</a>
</li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&Authority=".$Authority."&division=".$division."&limit=".$limit."&p=0\">1</a></li>";
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
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&Authority=".$Authority."&division=".$division."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&Authority=".$Authority."&division=".$division."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li>
<a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&Authority=".$Authority."&division=".$division."&limit=".$limit."&p=".$next."\" aria-label=\"Next\">
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
