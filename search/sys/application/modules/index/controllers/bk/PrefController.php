<?php
//コンポーネントのロード
class PrefController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	public function init(){
		$this->_db = new Model_Indexpref();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_setting = $setting->setting("88");
		$this->view->setting = $this->_setting;
		
	}
	public function indexAction() {
		$this->view->title = '<i class="fa fa-tachometer"></i> 地域別最短配達日検索';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		$prefArea = $this->_db->getPref();
		$this->view->prefArea = $prefArea;
		$item = $this->_db->getSokuItem();
		foreach($item as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		$this->view->soku = $item;
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","pref")
		);
		$this->view->html =$html;
	}
	public function prefAction() {
		$getArr = $this->_db->getArray();
		$ctyArr = $this->_db->getCityOfPref($getArr['pref']);
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$ctyArr['name'].'の市区町村一覧 地域別最短配達日検索';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li><a href="'.BASEURL.'/pref/"><i class="fa fa-tachometer"></i>　地域別最短配達日検索</a></li>
													<li>'.$this->view->title.'</li>';
		$prefArea = $this->_db->getPref();
		$this->view->ctyArr = $ctyArr;
		$item = $this->_db->getSokuItem();
		foreach($item as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		$this->view->soku = $item;
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","pref")
		);
		$this->view->html =$html;
	}
	public function cityAction() {
		$getArr = $this->_db->getArray();
													
		$town =$this->_db->getTownOfCity($getArr['city']);
		
		$this->view->city = $getArr['city'];
		$this->view->town = $town;
		$this->view->title = '<i class="fa fa-tachometer"></i> '.$getArr['city'].'の最短配達日検索';
		$this->view->bread = '<li><a href="'.BASEURL.'"/"><i class="fa fa-tachometer"></i>　'.$this->_setting['global']['SiteName'].'</a></li>
													<li><a href="'.BASEURL.'/pref/"><i class="fa fa-tachometer"></i>　地域別最短配達日検索</a></li>
													<li><a href="'.BASEURL.'/pref/pref/?pref='.$town[0]['pref'].'"><i class="fa fa-tachometer"></i>　'.$town[0]['prefName'].'</a></li>
													<li>'.$this->view->title.'</li>';
		$item = $this->_db->getSokuItem();
		foreach($item as $k=>$v){
			if($v['tax'] ==0){
				//外税の場合
				$item[$k]['price'] = $v['price']*(1+$this->_setting['tax']['ratio']/100);
			}
		}
		$this->view->soku = $item;
		$html = $this->_db->fetchAll(
			$this->_db->select()
			->from("html2")
			->where("parent=?",88)
			->where("page=?","city")
		);
		$this->view->html =$html;
	}
}
?>