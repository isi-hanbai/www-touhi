<?php
define("IMG_DIR","img/");
class Editor_ContentController extends Common_EditorController {
	//初期化メソッドの定義
	private $_db;
	private $_table;
	private $_user;
	private $_category;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
			//ユーザー権限
			$this->view->Auth = $this->_user->Authority;
		}
		$this->_db = new Model_Editorimageuser();
		$this->_table ="content";

		$category = $this->_db->fetchAll(
			$this->_db->select()
			->from("content_category",array("id","name"))
			->where("parent=?",$this->_user->parent)
		);
		array_unshift($category,array("id"=>"0","name"=>"選択"));
		$this->view->category = $category;
	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　お知らせ管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';


		//GETパラメータを取得
		$postArr = $this->_db->getArray();
		$keyword = $postArr["keyword"];
		$p = $postArr["p"];
		$limit = $postArr["limit"];
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = $this->_user->parent;
		//WHERE句を生成
		$whereArr = array();
		//検索キーワードが指定された場合
		if(!empty($keyword)){
			$key = mb_convert_kana($keyword,"s","UTF-8");
			$keyArr = explode(" ",$key);
			foreach($keyArr as $v){
				$whereArr[] = "name LIKE '%".$v."%'";
			}
		}
		$whereArr[] = "parent=".$parent;
		$where = "";
		if(!empty($whereArr)){
			$where =  " WHERE ".implode(" and ",$whereArr);
		}
		$sql = "SELECT c.*,
						cc.name AS category_name,
						(
							SELECT COUNT(*) FROM `content`{$where}
						) AS c
						FROM `content` c
						LEFT JOIN `content_category` cc ON cc.id = c.category
						{$where}";
		//検索クエリを作成
		//ORDER句を生成
		$sql.= " ORDER BY id DESC";
		//LIMIT句を生成
		$start = $p*$limit;
		$sql.= " LIMIT {$start} , {$limit}";
		//DBから取得
		$result = $this->_db->fetchAll($sql);
		//ユーザーリストの読み込み
		$this->view->contents = $result;
		//ページャーを生成
		$this->pager($keyword,$result[0]['c'],$limit,$p,"/editor/content/");
	}
	//詳細
	public function detailAction() {
		$postArr = $this->_db->getArray();
		$this->view->data = $this->_db->getContentDetail($postArr['id']);
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'の商品詳細';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　お知らせ登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　お知らせ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
		//$this->view->categorys = $this->_db->getContentCategory();
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//会員ラベルの付与と親をログインユーザーにする
			if(empty($postArr['thumb'])){
				$postArr['thumb'] = "";
			}
			$postArr['parent'] = $this->_user->parent;
			$postArr['created'] = date("Y-m-d H:i:s");
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId($this->_table,$postArr)){
				//インサート処理後に行う処理を記載
				//$this->view->data = $postArr;
				header("location:".BASEURL."/editor/content/update/?id=".$lastId."&add=1");
			}
		}
	}
	//編集
	public function updateAction() {
		$getArr = $this->_db->getArray();
		$data = $this->_db->fetchAll(
			$this->_db->select()
			->from("content")
			->where("id=?",$getArr['id'])
		);
		$this->view->data = $data[0];
		if($getArr['add'] ==1){
			$this->view->msg = "登録しました。";
		}
		if($getArr['update'] ==1){
			$this->view->msg = "編集しました。";
		}
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'];
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　お知らせ管理</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function updatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//DBに登録
			$arr = array();
			foreach($postArr as $k=>$v){
				if($k == "id"){
					$id = $v;
				}else{
					$arr[$k] = $v;
				}
			}
			//アップデート処理
			$this->_db->update("content",$arr,$this->_db->quoteInto("`id`=?",$id)."AND parent=".$this->_user->parent);
			//詳細を表示
			header("location:".BASEURL."/editor/content/update/?id=".$id."&update=1");
		}
	}
	//CSVインポート
	public function importAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　商品CSV一括登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';

	}
	//CSVインポート完了
	public function importfinishAction() {
		//タイトルの定義
		$this->view->title = "商品CSV一括登録完了";
		$this->view->title = '<i class="fa fa-gift"></i>　商品CSV一括登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/import"><i class="fa fa-gift"></i>　商品CSV一括登録</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
			//アップロードされたファイルを読み込み
			if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
				if (move_uploaded_file($_FILES["file"]["tmp_name"], APPLICATION_PATH."/files/" . $_FILES["file"]["name"])) {
					chmod(APPLICATION_PATH."/files/" . $_FILES["file"]["name"], 0644);
					$file_name = APPLICATION_PATH."/files/" . $_FILES["file"]["name"];
					$csv = file($file_name);
					//データ抽出
					$data = array();
					for($i=1;$i<count($csv);$i++){
						$row = explode(",",mb_convert_encoding(rtrim($csv[$i]),"UTF-8","SHIFT_JIS"));
						$data[] = array(
							"name"=>$row[0],
							"price"=>$row[1],
							"cost"=>$row[2],
							"category"=>$row[3],
							"active"=>$row[4],
							"size"=>$row[5],
							"description"=>$row[6],
							"tag"=>$row[7],
							"display"=>$row[8],
							"season"=>str_replace(":",",",$row[9]),
							"stockFlug"=>$row[10],
							"minOfSale"=>$row[11],
							"maxOfSale"=>$row[12],
							"realtimeFlug"=>$row[13],
							"orderToDeli"=>$row[14],
							"endOfSale"=>$row[15],
							"unit"=>$row[16],
							"number"=>$row[17],
							"parent"=>$this->_user->id
						);
					}
					/*
					var_dump($data);
					*/
					$this->view->Msg ="";
					$this->view->Msg.= "<p>".$_FILES["file"]["name"] . "をアップロードしました。</p>";
					$this->view->Msg.= "<ul>";
					//DBに登録
					foreach($data as $v){
						if($this->_db->insert($this->_table,$v)){
							$this->view->Msg.= "<li>".$v['name']."を登録しました</li>";
						}
					}
					$this->view->Msg.= "</ul>";
					$this->view->Msg.="<p><a href=\"".BASEURL."/editor/content/\">商品管理画面へ進む</a></p>";
				} else {
					$this->view->Msg= "ファイルをアップロードできません。";
				}
			} else {
				$this->view->Msg= "ファイルが選択されていません。";
			}
	}

	public function uploadAction() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			// 調べたい画像のパス
			$mime = shell_exec('file -bi '.escapeshellcmd($path));
			$mime = trim($mime);
			$mime = preg_replace("/ [^ ]*/", "", $mime);


			$f_name = explode(".",$_FILES['userfile']["name"])[0];
			if(preg_match("/.gif/i",$_FILES['userfile']["name"])){
				$in = imagecreatefromgif($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".gif";
			}elseif(preg_match("/.jpg/i",$_FILES['userfile']["name"]) || preg_match("/.jpeg/i",$_FILES['userfile']["name"])){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".jpg";
			}elseif(preg_match("/.png/i",$_FILES['userfile']["name"])){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".png";
			}else{
				//jpg,gif,png以外のファイルは受付しない
				$this->view->error = "FileTypeError";
			}
			$max = 1920;
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
			//プレースホルダを作成した画像にコピーして
			imagecopyresampled($out, $in,0,0,0,0, $new_width, $new_height, $width, $height);
			if($exif_data["Orientation"] == 6){
				$out = imagerotate($out,270, 0);
			}

			if(preg_match("/.gif/i",$_FILES['userfile']["name"])){
				imagegif($out,IMG_DIR.$filename);
			}elseif(preg_match("/.jpg/i",$_FILES['userfile']["name"]) || preg_match("/.jpeg/i",$_FILES['userfile']["name"])){
				imagejpeg($out,IMG_DIR.$filename);
			}elseif(preg_match("/.png/i",$_FILES['userfile']["name"])){
				imagepng($out,IMG_DIR.$filename);
			}
			//テンポラリ内の不要になったキャッシュを削除
			unlink($path);
			//登録した画像をDBに保存
			$upload_dir2 = '/img/';
			$this->view->filename = $upload_dir2.$filename;
			$this->view->success = true;
		}else{
			$this->view->error = "aaaaa";
		}
	}
	public function upload2Action() {
		if(is_uploaded_file($path = $_FILES['userfile2']['tmp_name'])){
			// 調べたい画像のパス
			$mime = shell_exec('file -bi '.escapeshellcmd($path));
			$mime = trim($mime);
			$mime = preg_replace("/ [^ ]*/", "", $mime);
			if($mime == "image/gif"){
				$in = imagecreatefromgif($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".gif";
			}elseif($mime == "image/jpeg"){
				$exif_data = exif_read_data($path);
				$in = imagecreatefromjpeg($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".jpg";
			}elseif($mime == "image/png"){
				$in = imagecreatefrompng($path); // 元画像ファイル読み込み
				$filename = $this->_user->id."_".date("YmdHis").".png";
			}else{
				//jpg,gif,png以外のファイルは受付しない
				$this->view->error = "FileTypeError";
			}
			$max = 1920;
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
			if($mime == "image/gif"){
				imagegif($out,IMG_DIR.$filename);
			}elseif($mime == "image/jpeg"){
				imagejpeg($out,IMG_DIR.$filename);
			}elseif($mime == "image/png"){
				imagepng($out,IMG_DIR.$filename);
			}
			//テンポラリ内の不要になったキャッシュを削除
			unlink($path);
			//登録した画像をDBに保存
			$upload_dir2 = '/img/';
			$this->view->filename = $upload_dir2.$filename;
			$this->view->success = true;
		}else{
			$this->view->error = "aaaaa";
		}
	}
	public function deleteimageAction() {
		//画像を削除
		$image = $this->getRequest()->getPost('url');
		$image2 = preg_replace("/(https?|ftp)(:\/\/nanpuku.co.jp\/)/","/home/nanpuku/nanpuku.co.jp/public_html/",$image);
		unlink($image2);
		//データベースから削除
		$id = $this->getRequest()->getPost('id');
		$table = "image";
		$where = "`id`={$id}";
		$this->_db->delete($table,$where);
		$this->view->id = $id;
	}
	public function updatehtmlAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$where= $this->_db->quoteInto('id = ?' , $postArr['id']);
			if($this->_db->update("content",array("addHtml"=>$postArr['addHtml']),$where)){
				$this->view->success = "登録が完了しました。";
			}else{
				$this->view->error = "登録が完了できませんでした。";
			}
			$this->view->data = $this->_db->getcontentDetail($postArr['id']);
		}
	}


	/////////////////////////商品カテゴリの設定
	//一覧
	public function categoryAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　お知らせカテゴリ一覧';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　お知らせカテゴリ管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';

		//GETパラメータを取得
		$postArr = $this->_db->getArray();
		//$category = $postArr["category"];
		$keyword = $postArr["keyword"];
		$p = $postArr["p"];
		$limit = $postArr["limit"];
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		$parent = "";
		if($keyword){
			$user = $this->_db->fetchAll(
				$this->_db->select()
				->from("content_category",array("id","name","(SELECT COUNT(*) FROM content_category)"))
				->where("CONCAT(`name`,`title`,`description`,`html`) LIKE '%".$keyword."%'")
				->where("parent=?",$this->_user->parent)
				->limit($limit,$start)
			);
		}else{
			$user = $this->_db->fetchAll(
				$this->_db->select()
				->from("content_category",array("id","name","(SELECT COUNT(*) FROM content_category) AS c"))
				->where("parent=?",$this->_user->parent)
				->limit($limit,$start)
			);
		}
		$this->view->users = $user;
		//ページャーを生成
		common::categorypager($keyword,$user[0]['c'],$limit,$p,"/editor/content/category");
	}
	//詳細
	public function categorydetailAction() {
		$postArr = $this->_db->getArray();
		//$this->view->data = $this->_db->getcontentDetail($postArr['id']);
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
			->from("content_category")
			->where("parent=?",$this->_user->parent)
			->where("`id`=?",$postArr['id'])
		);
		$this->view->data =$this->data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'の詳細';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　お知らせ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/category"><i class="fa fa-gift"></i>　お知らせカテゴリカテゴリ一覧</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
	}
	//編集
	public function categoryupdateAction() {
		$postArr = $this->_db->getArray();
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
			->from("content_category")
			->where("parent=?",$this->_user->parent)
			->where("`id`=?",$postArr['id'])
		);
		$this->view->data =$this->data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'] .'の商品カテゴリ編集';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　お知らせ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/category"><i class="fa fa-gift"></i>　お知らせカテゴリカテゴリ一覧</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/categorydetail/?id='.$postArr['id'].'"><i class="fa fa-gift"></i>　'.$this->view->data['name'] .'カテゴリ詳細</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
	//編集完了
	public function categoryupdatefinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
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
			$this->_db->update("content_category",$arr,"`id`=".$id);
			//詳細を表示
			$this->data = $this->_db->fetchAll(
				$this->_db->select()
				->from("content_category")
				->where("parent=?",$this->_user->parent)
				->where("`id`=?",$postArr['id'])
			);
			$this->view->data =$this->data[0];
			/*
			*/
		}
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　'.$this->view->data['name'].'お知らせカテゴリ編集完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　商品管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/category"><i class="fa fa-gift"></i>　商品カテゴリ一覧</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/categorydetail/?id='.$id.'"><i class="fa fa-gift"></i>　'.$this->view->data['name'] .'の商品カテゴリ詳細</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/categoryupdate/?id='.$id.'"><i class="fa fa-gift"></i>　'.$this->view->data['name'].'商品カテゴリ編集</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
	}
	//登録
	public function categoryregistrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>　お知らせカテゴリ登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>お知らせ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/category"><i class="fa fa-gift"></i>　お知らせカテゴリ一覧</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
												/*
		$this->view->user = $this->_user;
		$this->view->categorys = $this->_db->getContentCategory();
		$this->view->StockStatus = $this->_db->getStockStatus();
		*/
	}
	//登録完了
	public function categoryregistrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			//会員ラベルの付与と親をログインユーザーにする
			$postArr['parent'] = $this->_user->parent;
			//DBに登録
			if($lastId = $this->_db->insertAndGetLastId("content_category",$postArr)){
				//インサート処理後に行う処理を記載
				//$this->view->data = $postArr;
				header("location:".BASEURL."/editor/content/categoryregister2/?id=".$lastId);
			}
		}
	}
	public function categoryregister2Action() {
		$postArr = $this->_db->getArray();
		$this->data = $this->_db->fetchAll(
			$this->_db->select()
			->from("content_category")
			->where("parent=?",$this->_user->parent)
			->where("`id`=?",$postArr['id'])
		);
		$this->view->data =$this->data[0];
		//タイトルの定義
		$this->view->title = '<i class="fa fa-gift"></i>'.$this->view->data['name'].'　お知らせカテゴリ登録完了';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content"><i class="fa fa-gift"></i>　お知らせカテゴリ管理</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/editor/content/registrer"><i class="fa fa-gift"></i>　お知らせカテゴリ登録</a> <span class="divider">/</span>
														　'.$this->view->title.'
													</li>';
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
