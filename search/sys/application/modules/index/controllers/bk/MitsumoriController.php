<?php
//コンポーネントのロード
class MitsumoriController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	public function init(){
		$this->_db = new Model_Indexcontent();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_setting = $setting->setting("88");
		$this->view->siteName = $this->_setting['global']['SiteName'];
		$this->view->homeTitle = $this->_setting['global']['homeTitle'];
		$this->view->description = $this->_setting['global']['description'];
	}
	public function indexAction() {
		//葬儀商品カテゴリを取得
		$cate = $this->_db->fetchAll(
			$this->_db->select()
			->from("sougi_item_category")
			->where("level=3")
		);
		$this->view->category = $cate;
		//会場を取得
		$kaijou = $this->_db->fetchAll(
			$this->_db->select()
			->from("kaijou")
			->where("isFront=1")
		);
		$this->view->kaijou = $kaijou;
		$items = array();
		foreach($cate as $v){
			$i = $this->_db->fetchAll(
				"SELECT i.* ,r.sougi_item,r.k
				FROM sougi_item i
				LEFT JOIN(
					SELECT sougi_item,GROUP_CONCAT(kaijou) AS k from sougi_item_kaijou_relation
					group by sougi_item
				) r on r.sougi_item=i.id
				WHERE i.level=3 AND i.category=".$v['id']
			);
			$items[$v['id']] =$i;
		}
		$this->view->items = $items;
		//var_dump($items);
		//ご葬儀オンライン見積り
		$this->view->title = '<i class="fa fa-clipboard"></i> ご葬儀オンライン見積り';
		$this->view->bread = '<ul class="breadcrumb"><li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li></ul>';
		
	}
	public function resultAction(){
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			var_dump($postArr);
		}
	}
}
?>