<?php
// IndexController
class Api_CsvController extends Common_ApiController {
	private $_db;
	private $_user;
	public function init() {
		require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
		$this->_db = new Model_Apiimageuser();
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		$this->_user = $auth->getIdentity();
	}
	//選択されたデータの削除
	public function indexAction() {
		if(is_uploaded_file($path = $_FILES['userfile']['tmp_name'])){
			$postArr = $this->_db->postArray();

			$minetype= array("text/csv","application/octet-stream","text/plain","application/vnd.ms-excel");
			$filename = explode(".",$_FILES['userfile']['name']);
			//echo end($filename);
			//if($_FILES['userfile']['type'] == "text/csv" || $_FILES['userfile']['type'] == "application/octet-stream"){
			if(in_array($_FILES['userfile']['type'],$minetype)){
			//if($_FILES['userfile']['type'] == "text/csv"){
				//CSVの文字コードを調べる
		    $command = "file -i " . $path;
		    $output = [];
		    $status = "";
		    exec($command, $output, $status);
	    	preg_match("/charset=(.*)/", $output[0], $charset);
				//sjis-winの場合は、$charsetにunknown-8bitが出力される
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
				//CSVから読み込んだデータを配列に格納
				$array = array();
				while ($array[] = fgetcsv( $handle ));
				fclose( $handle );
				//カラム情報を取得
				$data = $this->_db->fetchAll(
					$this->_db->select()
					->from(array("col"=>"csvColumn"))
					->joinLeft(array("csv"=>"csv"),"col.csv=csv.id",array("csv.id AS csvid","csv.firstLine","csv.name AS csvname","csv.parent"))
					->where("csv.id=?",$postArr['id'])
					->where("csv.parent=?",$this->_user->id)
					->where("col.sort > 0")
					->order("col.sort")
				);
				$arr = array();
				//１行目を読み込まない場合は、
				if($data[0]['firstLine'] == 0){
					$n = 0;
				}else{
					$n = 1;
				}
				//カラム情報に合わせて、データを成形
				for($i=$n;$i<count($array);$i++){
					foreach($data as $v){
						foreach($array[$i] as $k=>$vv){
							if($v['csvColumn'] == $k){
								//日付フォーマットのときは、YYYY.mm.ddに変換
								//if(preg_match('/^([0-9]{4})\/([0-9]{1,2})\/([0-9]{1,2})$/', $vv)){
								if(preg_match('/^([1-9][0-9]{3})\/([1-9]{1}|1[0-2]{1})\/([1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/', $vv)){
								//if($v['dateFormat'] == 1){
									$dd = explode("/",$vv);
									$vv = $dd[0].".".str_pad($dd[1], 2, 0, STR_PAD_LEFT).str_pad($dd[2], 2, 0, STR_PAD_LEFT);
								}
								$arr[$i][$k] = $vv;
							}
						}
					}
				}
				mb_convert_variables('SJIS-win','UTF-8',$arr);
				//ダウンロードするファイル名を定義
				$uploadFileName = explode(".",$_FILES['userfile']['name']);
				$downloadFileName = $data[0]['csvname']."-".$uploadFileName[0].'-'.date("YmdHis").'.csv';
				//csvをダウンロードする
			  header('Content-Type: application/octet-stream');
			  header('Content-Disposition: attachment; filename='.$downloadFileName);
			  $stream = fopen('php://output', 'w');
			  foreach($arr as $row){
			    fputcsv($stream, $row, ",", "\"");
			  }
			}else{
				echo "Upload is Only CSV File";
				exit;
			}
		}else{
			echo "disable to Upload File";
		}
	}


	public function updatedateAction(){
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			//var_dump($postArr);
			$this->_db->update("csvColumn",array("dateFormat"=>1),$this->_db->quoteInto("id=?",$postArr['id']));
			echo "OK";
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
