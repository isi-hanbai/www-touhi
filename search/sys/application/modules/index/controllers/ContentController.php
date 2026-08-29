<?php
//コンポーネントのロード
class ContentController extends Common_IndexController {
	//初期化メソッドの定義
	private $_db;
	public $_setting;
	public function init(){
		$this->_db = new Model_Indexcontent();
		//設定の読み込み
		$setting = new Model_Indexsettings;
		$this->_setting = $setting->setting("88");
	}
	private function resolveCategoryIdByName($name, $parent = 2998) {
		$db = new Model_Indexgeneral();
		$result = $db->fetchAll(
			$db->select()
				->from("content_category")
				->where("parent=?", $parent)
				->where("name=?", $name)
				->limit(1)
		);
		return !empty($result) ? (int)$result[0]['id'] : null;
	}
	public function indexAction() {
		//GETパラメータを取得
		$postArr = $this->_db->getArray();
		$p = $postArr["p"];
		$limit = $postArr["limit"];
		$category = $postArr["category"];
		if(empty($category)){
			$category = $this->resolveCategoryIdByName("健康一口メモ");
		}
		if($limit<1){
			$limit = 12;
		}
		$start = $limit*$p;
		$parent = 2998;
		//WHERE句を生成
		$whereArr = array();
		if($category){
			$whereArr[] = $this->_db->quoteInto("category=?",$category);
			$cate = $this->_db->fetchAll(
				$this->_db->select()
				->from("content_category",array("id","name"))
				->where("id=?",$category)
				->limit(1)
			);
		}
		$whereArr[] = "parent=".$parent;
		$where = "";
		if(!empty($whereArr)){
			$where =  " WHERE ".implode(" and ",$whereArr);
		}
		$sql = "SELECT *,
						(
							SELECT COUNT(*) FROM `content`{$where}
						) AS c
						FROM `content`{$where}";
		//検索クエリを作成
		//ORDER句を生成
		$sql.= " ORDER BY id DESC";
		//LIMIT句を生成
		$start = $p*$limit;
		$sql.= " LIMIT {$start} , {$limit}";
		//DBから取得
		$result = $this->_db->fetchAll($sql);
		$this->view->data = $result;
		if($cate[0]["name"]){
			$title = $cate[0]["name"];
		}else{
			$title = "お知らせ";
		}
		$this->view->title = $title;
		$this->view->bread = '<li><a href="https://www.touhi-ishikai.jp/"><i class="fa fa-tachometer"></i>HOME</a></li>
													<li>'.$this->view->title.'</li>';

		$this->view->pager = $this->pager("",$category,$result[0]['c'],$limit,$p,"/content/");

	}
	public function detailAction() {
		$getArr =$this->_db->getArray();
		$result = $this->_db->fetchAll(
			$this->_db->select()
			->from(array("c"=>"content"))
			->joinLeft(array("cc"=>"content_category"),"cc.id = c.category",array("cc.name AS cName"))
			->where("c.id=?",$getArr['id'])
			->limit(1)
		);
		$this->view->data = $result[0];
		$this->view->title = $result[0]['name'];
		$this->view->bread = '<li><a href="https://www.touhi-ishikai.jp/"><i class="fa fa-tachometer"></i>HOME</a></li>
													<li><a href="'.BASEURL.'/content/?category='.$result[0]['category'].'"><i class="fa fa-tachometer"></i>'.$result[0]['cName'].'</a></li>
													<li>'.$this->view->title.'</li>';
	}

		//ページャーの生成
		private function pager($keyword="",$category,$n=0,$limit=10,$p=0,$page){
			$start = $p*$limit;
			if($n > $limit){
				$pager= "<div class=\"text-center\">";
				$pager.= "<nav><ul class=\"pagination pagination-lg hover-style-2 justify-content-center margin-top-70\">";
				if(!empty($p)){
					$prev = $p-1;
					$pager.= "<li class=\"page-item\"><a class=\"page-link\" href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\"><span aria-hidden=\"true\">&laquo; 前</span></a></li>";
				}
				if($p > 2){
					$pager.= "<li class=\"page-item\"><a class=\"page-link\" href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
				}
				for($i=0;$i<ceil($n/$limit);$i++){
					$pn = $i+1;
					if($i>$p+2 || $i < $p-2){
					}else{
						if($i == $p){
							$avtive = " class=\"page-item active\"";
						}else{
							$avtive = " class=\"page-item\"";
						}
						$pager.= "<li$avtive ><a class=\"page-link\" href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
					}
				}
				if($p+2 < ceil($n/$limit)){
					$pager.= "<li$avtive ><a class=\"page-link\" href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
				}
				if($start <$n-1){
					$next = $p+1;
					$pager.= "<li class=\"page-item\"><a class=\"page-link\" href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
				}
				$pager.= "</ul></nav></div>";
				return $pager;
			}
		}
}
?>
