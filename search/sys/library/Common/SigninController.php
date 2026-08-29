<?php
class Common_SigninController extends Common_BaseController {
  public function postDispatch() {
	//レイアウトの設定
	$option = array(
		"layout" => "index",
		"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
	);
	Zend_Layout::startMvc($option);
  /*
	$db = new Model_Indexgeneral();
	//サイドバー用のカテゴリ
	$this->view->itemCategorys = $db->fetchAll(
		$db->select()
		->from("item_category")
		->where("display=0")
		->where("parent=88")
	);
	//サイドバー用の用途一覧
	$this->view->itemYouto = $db->fetchAll(
		$db->select()
		->from("youto")
		->where("active=0")
		->where("parent=88")
		->order("order")
	);
	//サイドバー用のコンテンツカテゴリ一覧
	$ContentCategory = $db->fetchAll(
		$db->select()
		->from("content_category")
		->where("display=0")
		->where("parent=88")
	);
	foreach($ContentCategory as $k=>$v){
		$result = $db->fetchAll(
			$db->select()
			->from("content")
			->where("category=?",$v['id'])
			->where("parent=88")
		);
		$ContentCategory[$k]['pages'] = $result;
	}
	$this->view->ContentCategory = $ContentCategory;

	//設定を読み込み
	$setting = new Model_Indexsettings;
	$this->view->setting = $setting->setting("88");
  */
  }
}
