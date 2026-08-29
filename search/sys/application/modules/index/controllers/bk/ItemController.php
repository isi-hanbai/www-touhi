<?php
//コンポーネントのロード
class ItemController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	public function init(){
		$this->_db = new Model_Indexitem();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_setting = $setting->setting("88");
		if($category = $this->_request->getParam("category")){
			$cate = $this->_db->fetchAll(
				$this->_db->select()
				->from("item_category")
				->where("parent=88")
				->where("id=?",$category)
			);
		}else{
			$cate = $this->_db->fetchAll(
				$this->_db->select()
				->from("item_category")
				->where("parent=88")
			);
		}
		$this->view->category = $cate;
	}
	public function indexAction() {
		//header("location:http://52.69.242.229/jm/%e3%83%a9%e3%83%96%e3%82%bf%e3%82%a4%e3%83%a0%e3%81%ab%e3%81%a8%e3%81%8d%e3%82%81%e3%81%8d%e3%81%a8%e3%80%81%e7%94%98%e7%be%8e%e3%81%aa%e6%bd%a4%e3%81%84%e3%82%92");
		/**/
		if($this->_request->isGet()){
			$postArr = $this->_db->getArray();
			$keyword=$postArr['keyword'];
			if($postArr['p']){
				$p=$postArr['p'];
			}else{
				$p=0;
			}
			if($postArr['limit']){
				$limit=$postArr['limit'];
			}else{
				$limit = $this->_setting['global']['listLimit'];
			}
			//getパラメータが存在した場合
			if($postArr['category']){
				//「category」パラメータがあった場合
				$cate = $this->_db->fetchAll(
					$this->_db->select()
					->from("item_category",array("name"))
					->where("parent=88")
					->where("id=?",$postArr['category'])
					->limit(1)
				);
			}
		}
		if($cate){
			if($keyword){
				$this->view->title = '「'.$cate[0]['name'].'」内の「'.$keyword.'」を含む商品一覧';
			}else{
				$this->view->title = $cate[0]['name'].'の商品一覧';
			}
			$item = $this->_db->getItems($postArr['category'],$keyword,$p,$limit,88);
		}else{
			if($keyword){
				$this->view->title = '　「'.$keyword.'」を含む商品一覧';
			}else{
				$this->view->title = '商品一覧';
			}
			$item = $this->_db->getItems(NULL,$keyword,$p,$limit,88);
		}
		/*
		//価格を税込計算にする
		foreach($item[0] as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[0][$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		*/
		//$this->view->title = '<i class="fa fa-tachometer"></i> '.$this->view->data['name'];
		$this->view->bread = '<li><a href="'.BASEURL.'"/">'.$this->_setting['global']['SiteName'].'</a></li><li>'.$this->view->title.'</li>';
		$this->view->item = $item[0];
		common::pagerf($postArr['category'],$keyword,$item[1],$limit,$p,"/item/");
	}
	public function categoryAction() {
		if($this->_request->isGet()){
			$postArr = $this->_db->getArray();
			$keyword=$postArr['keyword'];
			if($postArr['p']){
				$p=$postArr['p'];
			}else{
				$p=0;
			}
			if($postArr['limit']){
				$limit=$postArr['limit'];
			}else{
				$limit = $this->_setting['global']['listLimit'];
			}
			//getパラメータが存在した場合
			if($postArr['id']){
				//「category」パラメータがあった場合
				$cate = $this->_db->fetchAll(
					$this->_db->select()
					->from("item_category")
					->where("parent=88")
					->where("id=?",$postArr['id'])
					->limit(1)
				);
			}
		}
		if($cate){
			if($keyword){
				$this->view->title = '<i class="fa fa-tachometer"></i>　「'.$cate[0]['name'].'」内の「'.$keyword.'」を含む商品一覧';
			}else{
				$this->view->title = '<i class="fa fa-tachometer"></i>　'.$cate[0]['name'].'の商品一覧';
			}
			$item = $this->_db->getItems($postArr['id'],$keyword,$p,$limit,88);
			$this->view->cate = $cate;
		}else{
			if($keyword){
				$this->view->title = '<i class="fa fa-tachometer"></i>　「'.$keyword.'」を含む商品一覧';
			}else{
				$this->view->title = '<i class="fa fa-tachometer"></i>　全商品一覧';
			}
			$item = $this->_db->getItems(NULL,$keyword,$p,$limit,88);
		}
		//価格を税込計算にする
		foreach($item[0] as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[0][$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		//$this->view->title = '<i class="fa fa-tachometer"></i> '.$this->view->data['name'];
		$this->view->bread = '<li><a href="'.BASEURL.'"/">'.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		$this->view->item = $item[0];
		
		common::pagerf($postArr['category'],$keyword,$item[1],$limit,$p,"/item/");
	}
	public function detailAction() {
		if($this->_request->isGet()){
			$postArr = $this->_db->getArray();
			$data = $this->_db->getItemDetail($postArr['id']);
			//価格を税込計算にする
			if($data['tax'] ==0){
				//外税の場合
				$data['taxprice'] = $data['price']*(1+$this->_setting['tax']['ratio']/100);
			}
			/*
			*/
			if($data['tag']){
				//$data['tag'] =  preg_split('/[　 ]/', $data['tag']);
				$data['tag'] = mb_convert_kana($data['tag'],"s","UTF-8");
				$data['tag'] = explode(" ",$data['tag']);
			}
			$this->view->data = $data;
			$this->view->image = $this->_db->fetchAll(
				$this->_db->select()
				->from("image")
				->where("item=?",$postArr['id'])
			);
		}
		$seibun = $this->_db->getItemOfSeibun($postArr['id']);
		$this->view->seibun = $seibun;
		$review = $this->_db->fetchAll(
			$this->_db->select()
			->from("review")
			->where("`item`=?",$postArr['id'])
		);
		$this->view->review = $review;
		$this->view->seibun = $seibun;
		$this->view->title = ''.$this->view->data['name'];
		$this->view->bread = '<li><a href="'.BASEURL.'"/">'.$this->_setting['global']['SiteName'].'</a></li>
													<li><a href="'.BASEURL.'/item" 　class="btn disabled">商品一覧</a></li>
													<li><a href="'.BASEURL.'/item?category='.$data['categoryId'].'" 　class="btn disabled">'.$data['categoryName'].'</a></li>
													<li>'.$this->view->title.'</li>';
	}
	public function wakuimageAction(){
		$getArr = $this->_db->getArray();
		$path = BASEURL."/".urldecode($getArr['url']);
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
		if($img_extension == "gif"){
			$img = imagecreatefromgif($path);
		}elseif($img_extension == "jpg"){
			$img = imagecreatefromjpeg($path);
		}elseif($img_extension == "png"){
			$img = imagecreatefrompng($path);
			//ブレンドモードを無効にする
			imagealphablending($img, false);
			//完全なアルファチャネル情報を保存するフラグをonにする
			imagesavealpha($img, true);
		}
		
		$logoimg = imagecreatefrompng(BASEURL."/img/itemFrame.png");
		//ブレンドモードを無効にする
		imagealphablending($logoimg, false);
		//完全なアルファチャネル情報を保存するフラグをonにする
		imagesavealpha($logoimg, true);
		# 元画像の幅と高さを取得
		$img_w = imagesx($img);
		$img_h = imagesy($img);
		# ロゴの幅と高さを取得
		$logo_w = imagesx($logoimg);
		$logo_h = imagesy($logoimg);
		//元を作成
		#ロゴをコピー
		
		imagecopy($logoimg,$img,0, 0,0, 0,$logo_w, $logo_h);
		if($img_extension == "gif"){
			header('Content-Type: image/gif');
			imagegif($logoimg);
		}elseif($img_extension == "jpg"){
			header('Content-Type: image/jpeg');
			imagejpeg($logoimg);
		}elseif($img_extension == "png"){
			header('Content-Type: image/png');
			imagepng($logoimg);
		}
	}
}
?>