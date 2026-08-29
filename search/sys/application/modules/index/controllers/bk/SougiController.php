<?php
//コンポーネントのロード
class SougiController extends Common_IndexController {
	public $_setting;
	public $_db;
	//初期化メソッドの定義
	public function init(){
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_db = new Model_Indexgeneral;
		$this->_setting = $setting->setting("88");
		$this->view->setting = $this->_setting;
	}
	public function indexAction() {
		$this->images = $this->_db->fetchAll(
			$this->_db->select()
				->from("sougiImage")
				->where("`sougi`=?",$_GET['id'])
		);
		$this->imageOrder = $this->_db->fetchAll(
			$this->_db->select()
				->from("sougiImageOrder")
				->where("`sougi`=?",$_GET['id'])
		);
		if($this->imageOrder[0]){
			$order = explode(",",$this->imageOrder[0]['order']);
			$arra = array();
			foreach($order as $v){
				foreach($this->images as $vv){
					if($v == $vv['id']){
						$arra[]=$vv;
					}
				}
			}
			$this->view->images = $arra;
		}else{
			$this->view->images = $this->images;
		}
	}
}
?>