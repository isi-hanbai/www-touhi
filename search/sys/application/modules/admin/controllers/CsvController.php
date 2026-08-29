<?php
// IndexController
class Admin_CsvController extends Common_AdminController {
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
		}
		$this->_db = new Model_Adminimageuser();
	}
	//一覧
	public function indexAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　CSV管理';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/csv"　class="btn disabled"><i class="fa fa-cog"></i>　CSV管理</a> <span class="divider">/</span>
													</li>';

		//GETパラメータを取得
		$keyword = $this->_request->getParam("keyword");
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		//CSVリストの読み込み
		$sql = "SELECT csv.*,
		(SELECT COUNT(*) FROM csvColumn AS col WHERE csv.id = col.csv) AS n,
		(SELECT COUNT(*) FROM csvColumn AS col2 WHERE csv.id = col2.csv AND col2.sort > 0) AS valid
		FROM `csv`";
		//検索クエリを作成
		$whereArr = array();
		//検索キーワードが指定された場合
		if(!empty($keyword)){
			$key = mb_convert_kana($keyword,"s","UTF-8");
			$keyArr = mb_split(" ",$key);
			foreach($keyArr as $v){
				$whereArr[] = "csv.name LIKE '%".$v."%'";
			}
		}
		$whereArr[] = "csv.parent=".$this->_user->id;
		//WHERE句を生成
		if(!empty($whereArr)){
			$sql.= " WHERE ".implode(" and ",$whereArr);
			$where = " WHERE ".implode(" and ",$whereArr);
		}
		//LIMIT句を生成
		$start = $p*$limit;
		$sql.= " LIMIT {$start} , {$limit}";
		//DBから取得
		$sql2 = "SELECT COUNT(*) AS n FROM csv".$where;
		$result = $this->_db->fetchAll($sql);
		//$result2 = $this->_db->fetchAll($sql2);
		$jiin = array($result,$result2[0]['n']);
		$this->view->users = $jiin[0];


		//ページャーを生成
		common::userpager($keyword,$jiin[1],$limit,$p,"/admin/csv/");
	}
	//登録
	public function registrerAction() {
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　CSV登録';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/csv"><i class="fa fa-cog"></i>　CSV管理</a> <span class="divider">/</span>
														'.$this->view->title.'
													</li>';
		$this->view->user = $this->_user;
	}
	//登録完了
	public function registrerfinishAction() {
		if($this->_request->isPost()){
			//POSTデータのエスケープ処理
			$postArr = $this->_db->postArray();
			$postArr['created'] = date("Y-m-d H:i:s");
			$postArr['parent'] = $this->_user->id;
			//DBに登録
			$arr = array();
			foreach($postArr as $k=>$v){
				//idとpwactiveを除外
				if($k == "id"){
					$id = $v;
				}elseif($k == "items" || $k == "sort" || $k == "before"){
					${$k} = json_decode(htmlspecialchars_decode($v),true);
				}else{
					$arr[$k] = $v;
				}
			}
			if($lastId = $this->_db->insertAndGetLastId("csv",$arr)){
				//使用する物
				$itemArr = array();
				$i=0;
				foreach($sort as $v){
					$i++;
					foreach($items as $vv){
						if($v == $vv['id']){
							$vv['csvColumn'] = $vv['id']*1;//整数に変換
							$vv['sort'] = $i;
							$vv['csv'] = $lastId;
							unset($vv['id']);
							$itemArr[$v] =$vv;
						}
					}
				}

				$column = $this->_db->fetchAll(
					$this->_db->select()
					->from("csvColumn","COUNT(*) AS c")
					->where("csv=?",$lastId)
				);
				if($column[0]['c'] >0){
				//すでにカラムの登録がある場合
					$beforeArr = $this->_db->fetchAll(
						$this->_db->select()
						->from("csvColumn")
						->where("csv=?",$lastId)
						->order("csvColumn")
					);
					foreach($beforeArr as $k=>$v){
						unset($beforeArr[$k]['id']);
						unset($beforeArr[$k]['sort']);
					}
				}else{
				//カラムの登録がない場合
					$beforeArr = array();
					for($i=0;$i<count($before);$i++){
						$beforeArr[] = array(
							"csvColumn"=>$i,
							"name"=>$before[$i],
							"csv"=>$lastId
						);
					}
				}
				foreach($beforeArr as $v){
					foreach($itemArr as $vv){
						if($v['csvColumn'] == $vv['csvColumn']){
							$v['sort'] = $vv['sort'];
						}
					}
					var_dump($v);
					$this->_db->insert("csvColumn",$v);
				}
				//インサート処理後に行う処理を記載
				header("location:".BASEURL."/admin/csv/update/?id=".$lastId."&register=1#setting");
			}
		}
	}
	//編集
	public function updateAction() {
		$getArr = $this->_db->getArray();
		$detail = $this->_db->fetchAll(
			$this->_db->select()
			->from("csv")
			->where("id=?",$getArr['id'])
		);
		/**/
		//カラム情報を取得
		$column = $this->_db->fetchAll(
			$this->_db->select()
			->from("csvColumn")
			->where("csv=?",$getArr['id'])
			->order("sort")
		);
		if(!empty($column)){
			$before = array();
			$sort = array();
			$items = array();
			$valid = array();
			foreach($column as $v){
				if($v['sort'] != 0){
					$sort[]= $v['csvColumn'];
					$arr = array(
						"id" => $v['csvColumn'],
						"cid" => $v['id'],
						"dateFormat" => $v['dateFormat'],
						"name"=> $v['name']
					);
					$items[] = $arr;
				}else{
					$valid[] = $v;
				}
				$before[] = $v['name'];
			}
			$detail[0]['before'] = json_encode($before);
			$detail[0]['sort'] = json_encode($sort);
			$detail[0]['items'] = json_encode($items);
			$detail[0]['invalid'] = $items;
			$detail[0]['valid'] = $valid;
			$detail[0]['validA'] = json_encode($valid);
			$detail[0]['csv'] = 1;
		}


		$this->view->detail = $detail[0];
		if($_GET['register']==1){
			$this->view->msg = "登録が完了しました";
		}elseif($_GET['update']==1){
			$this->view->msg = "更新が完了しました";
		}
		//タイトルの定義
		$this->view->title = '<i class="fa fa-user"></i>　'.$this->view->detail['name'].'';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/admin/"><i class="fa fa-tachometer"></i>　ダッシュボード</a> <span class="divider">/</span>
														<a href="'.BASEURL.'/admin/csv"><i class="fa fa-cog"></i>　CSV管理</a> <span class="divider">/</span>
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
				//idとpwactiveを除外
				if($k == "id"){
					$id = $v;
				}elseif($k == "items" || $k == "sort" || $k == "before"){
					${$k} = json_decode(htmlspecialchars_decode($v),true);
				}else{
					$arr[$k] = $v;
				}
			}
			//使用する物
			$itemArr = array();
			$i=0;
			foreach($sort as $v){
				$i++;
				foreach($items as $vv){
					if($v == $vv['id']){
						$vv['csvColumn'] = $vv['id']*1;//整数に変換
						$vv['sort'] = $i;
						$vv['csv'] = $id;
						unset($vv['id']);
						$itemArr[$v] =$vv;
					}
				}
			}

			$column = $this->_db->fetchAll(
				$this->_db->select()
				->from("csvColumn","COUNT(*) AS c")
				->where("csv=?",$id)
			);
			if($column[0]['c'] >0){
			//すでにカラムの登録がある場合
				$beforeArr = $this->_db->fetchAll(
					$this->_db->select()
					->from("csvColumn")
					->where("csv=?",$id)
					->order("csvColumn")
				);
				foreach($beforeArr as $k=>$v){
					unset($beforeArr[$k]['id']);
					unset($beforeArr[$k]['sort']);
				}
			}else{
			//カラムの登録がない場合
				$beforeArr = array();
				for($i=0;$i<count($before);$i++){
					$beforeArr[] = array(
						"csvColumn"=>$i,
						"name"=>$before[$i],
						"csv"=>$id
					);
				}
			}
			var_dump($beforeArr);
			$this->_db->update("csv",$arr,"`id`=".$id);
			//一旦削除して、新たに登録(有効なカラム)
			$this->_db->delete("csvColumn","`csv`=".$id);
			foreach($beforeArr as $v){
				foreach($itemArr as $vv){
					if($v['csvColumn'] == $vv['csvColumn']){
						$v['sort'] = $vv['sort'];
					}
				}
				var_dump($v);
				$this->_db->insert("csvColumn",$v);
			}

			header("location:".BASEURL."/admin/csv/update/?id=".$id."&update=1#setting");
		}
	}
	public function uploadAction() {
	}
	public function upload5Action() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			$postArr = $this->_db->postArray();
			//CSVの文字コードを調べる
	    $command = "file -i " . $path;
	    $output = [];
	    $status = "";
	    exec($command, $output, $status);
			//sjis-winの場合は、$charsetにunknown-8bitが出力される
    	preg_match("/charset=(.*)/", $output[0], $charset);
			//CSVを配列に変換
			$handle = fopen( $path, "r" );
			if(in_array("unknown-8bit", $charset)){
				//sjis-winの場合は、文字コードをUTF-8に変換
				stream_filter_register(
					'sjis_to_utf8_encoding_filter',
					SjisToUtf8EncodingFilter::class
				);
				stream_filter_append($handle, 'sjis_to_utf8_encoding_filter');
			}
			$array = array();
			while ($array[] = fgetcsv( $handle ));
			fclose( $handle );
			$minetype= array("text/csv","application/octet-stream","text/plain","application/vnd.ms-excel");
			$filename = explode(".",$_FILES['userfile']['name']);
			//echo end($filename);
			//if($_FILES['userfile']['type'] == "text/csv" || $_FILES['userfile']['type'] == "application/octet-stream"){
			if(in_array($_FILES['userfile']['type'],$minetype)){
				$this->view->csvHeading = json_encode($array[0]);
				//echo $_FILES['userfile']['type'];
			}else{
				echo "csvファイル以外は受付できません".$_FILES['userfile']['type'];
				exit;
			}
		}else{
			$this->view->error = "aaaaa";
		}
	}
}

final class SjisToUtf8EncodingFilter extends \php_user_filter
{
    /**
     * Buffer size limit (bytes)
     *
     * @var int
     */
    private static $bufferSizeLimit = 1024;

    /**
     * @var string
     */
    private $buffer = '';

    public static function setBufferSizeLimit(int $bufferSizeLimit): void
    {
        self::$bufferSizeLimit = $bufferSizeLimit;
    }

    /**
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        $isBucketAppended = false;
        $previousData = $this->buffer;
        $deferredData = '';

        while ($bucket = \stream_bucket_make_writeable($in)) {
            $data = $previousData . $bucket->data; // 前回後回しにしたデータと今回のチャンクデータを繋げる
            $consumed += $bucket->datalen;

            // 受け取ったチャンクデータの最後から1文字ずつ削っていって、SJIS的に区切れがいいところまでデータを減らす
            while ($this->needsToNarrowEncodingDataScope($data)) {
                $deferredData = \substr($data, -1) . $deferredData; // 削ったデータは後回しデータに付け加える
                $data = \substr($data, 0, -1);
            }

            if ($data) { // ここに来た段階で $data は区切りが良いSJIS文字列になっている
                $bucket->data = $this->encode($data);
                \stream_bucket_append($out, $bucket);
                $isBucketAppended = true;
            }
        }

        $this->buffer = $deferredData; // 後回しデータ: チャンクデータの句切れが悪くエンコードできなかった残りを次回の処理に回す
        $this->assertBufferSizeIsSmallEnough(); // メモリ不足回避策: バッファを使いすぎてないことを保証する
        return $isBucketAppended ? \PSFS_PASS_ON : \PSFS_FEED_ME;
    }

    private function needsToNarrowEncodingDataScope(string $string): bool
    {
        return !($string === '' || $this->isValidEncoding($string));
    }

    private function isValidEncoding(string $string): bool
    {
        return \mb_check_encoding($string, 'SJIS-win');
    }

    private function encode(string $string): string
    {
        return \mb_convert_encoding($string, 'UTF-8', 'SJIS-win');
    }

    private function assertBufferSizeIsSmallEnough(): void
    {
        \assert(
            \strlen($this->buffer) <= self::$bufferSizeLimit,
            \sprintf(
                'Streaming buffer size must less than or equal to %u bytes, but %u bytes allocated',
                self::$bufferSizeLimit,
                \strlen($this->buffer)
            )
        );
    }
}
?>
