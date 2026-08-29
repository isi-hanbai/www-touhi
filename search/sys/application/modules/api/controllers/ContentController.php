<?php
// IndexController
class Api_ContentController extends Common_ApiController {
	private $_db;
	public function init() {
			require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
			$this->_db = new Model_Apiimageuser();
	}

	public function indexAction() {
		//GETパラメータを取得
		$postArr = $this->_db->getArray();
		$keyword = $postArr["keyword"];
		$category = $postArr["category"];
		$p = $postArr["p"];
		$limit = $postArr["limit"];
		if($limit<1){
			$limit = 15;
		}
		$start = $limit*$p;
		//$parent = $this->_user->parent;
		$parent = 2998;
		//WHERE句を生成
		$whereArr = array();
		//検索キーワードが指定された場合
		if(!empty($keyword)){
			$key = mb_convert_kana($keyword,"s","UTF-8");
			$keyArr = explode(" ",$key);
			foreach($keyArr as $v){
				$whereArr[] = $this->_db->quoteInto("content.name LIKE '%?%'",$v);
			}
		}
		if(!empty($category)){
			$whereArr[] = $this->_db->quoteInto("content.category=?",$category);
		}
		$whereArr[] = $this->_db->quoteInto("content.parent=?",$parent);
		$where = "";
		if(!empty($whereArr)){
			$where =  " WHERE ".implode(" AND ",$whereArr);
		}
		$sql = "SELECT content.*,content_category.id AS cid,content_category.name AS cName,
		(
			SELECT COUNT(*) FROM `content`".$where."
		) AS c
		FROM `content`
		LEFT JOIN `content_category`
		ON content.category = content_category.id".$where;

		//検索クエリを作成
		//ORDER句を生成
		$sql.= " ORDER BY id DESC";
		//LIMIT句を生成
		$start = $p*$limit;
		$sql.= " LIMIT {$start} , {$limit}";
		//DBから取得
		$result = $this->_db->fetchAll($sql);
		//ユーザーリストの読み込み
		header('Access-Control-Allow-Origin: *');
		$arr = array();
		foreach($result as $v){
			$arr["owl"][] = array("item"=>$v['content']);
		}
		echo json_encode($result);
		//echo $sql;
	}
	//選択されたデータの削除
	public function deleteAction() {
		if($this->_request->isPost()){
			$idArr = $this->_request->getPost("ids");
			$whereArr = array();
			if(is_array($idArr)){
				foreach($idArr as $v){
					$whereArr[] ="`id`=".$v;
				}
			}else{
				$whereArr[] ="`id`=".$idArr;
			}
			$where = implode(" OR ",$whereArr);
			if($this->_db->delete("content",$where)){
				echo "OK";
			}
		}
	}
	//選択されたデータの削除
	public function categorydeleteAction() {
		if($this->_request->isPost()){
			$idArr = $this->_request->getPost("ids");
			$whereArr = array();
			if(is_array($idArr)){
				foreach($idArr as $v){
					$whereArr[] ="`id`=".$v;
				}
			}else{
				$whereArr[] ="`id`=".$idArr;
			}
			$where = implode(" OR ",$whereArr);
			if($this->_db->delete("content_category",$where)){
				echo "OK";
			}
		}
	}

}
?>
