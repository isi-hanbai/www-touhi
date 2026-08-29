<?php
// IndexController
class Api_ImageController extends Common_ApiController {
	private $_db;
	public function init() {
			require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
			$this->_db = new Model_Apiimageuser();
	}
	
	//GDテスト
	public function indexAction() {
		$getArr = $this->_db->getArray();
		//ファイル種類を判別
		$f_type = explode(".",$getArr['url']);
		//元ファイルを読み込む
		if($f_type[1] == "JPG" || $f_type[1] == "jpg"){
			$image = ImageCreateFromJPEG(REALPATH."/".$getArr['url']);
		}elseif($f_type[1] == "PNG" || $f_type[1] == "png"){
			$image = ImageCreateFromPNG(REALPATH."/".$getArr['url']);
		}elseif($f_type[1] == "GIF" || $f_type[1] == "gif"){
			$image = ImageCreateFromGIF(REALPATH."/".$getArr['url']);
		}
		//縦横のサイズを取得
		$width = ImageSX($image); //横幅（ピクセル）
		$height = ImageSY($image); //縦幅（ピクセル）
		$fix = $getArr['size'];
		//サイズ調整（短辺の長さを116pxに固定）
		if($width < $height){//縦系
			$new_width = $fix;
			$rate = $new_width / $width; //圧縮比
			$new_height = $rate * $height;
			$sta_l = $new_height/2-$fix/2;
			$sta_w = 0;
		}else{//横系
			$new_height = $fix;
			$rate = $new_height / $height; //圧縮比
			$new_width = $rate * $width;
			$sta_l = 0;
			$sta_w = $new_width/2-$fix/2;
		}
		//空の画像を生成
		$new_image = ImageCreateTrueColor($fix,$fix);
		//画像を上記の空の画像にコピー
		ImageCopyResized($new_image,$image,0,0,$sta_w,$sta_l,$new_width,$new_height,$width,$height);
		//即納マークをコピー
		$mass = ImageCreateFromPng(BASEURL."/img/sokunou.png"); 
		$soku_margin = 20;
		$soku_x = $soku_margin;
		$soku_y = $soku_margin;
		//$soku_y = $fix-$soku_margin-56;
		ImageCopy($new_image, $mass, $soku_x, $soku_y, 0, 0, 92, 56); 
		//画像を出力
		if($f_type[1] == "JPG" || $f_type[1] == "jpg"){
			Header("Content-type: image/jpeg");
			ImageJPEG($new_image,NULL,100);
		}elseif($f_type[1] == "PNG" || $f_type[1] == "png"){
			Header("Content-type: image/png");
			ImagePNG($new_image,NULL,100);
		}elseif($f_type[1] == "GIF" || $f_type[1] == "gif"){
			Header("Content-type: image/gif");
			ImageGIF($new_image,NULL,100);
		}
		/*
		var_dump($getArr);
		*/
	}
	public function sqAction() {
		$getArr = $this->_db->getArray();
		//ファイル種類を判別
		$f_type = explode(".",$getArr['url']);
		//元ファイルを読み込む
		if($f_type[1] == "JPG" || $f_type[1] == "jpg"){
			$image = ImageCreateFromJPEG(REALPATH."/".$getArr['url']);
		}elseif($f_type[1] == "PNG" || $f_type[1] == "png"){
			$image = ImageCreateFromPNG(REALPATH."/".$getArr['url']);
		}elseif($f_type[1] == "GIF" || $f_type[1] == "gif"){
			$image = ImageCreateFromGIF(REALPATH."/".$getArr['url']);
		}
		//縦横のサイズを取得
		$width = ImageSX($image); //横幅（ピクセル）
		$height = ImageSY($image); //縦幅（ピクセル）
		$fix = $getArr['size'];
		//サイズ調整（短辺の長さを116pxに固定）
		if($width < $height){//縦系
			$new_width = $fix;
			$rate = $new_width / $width; //圧縮比
			$new_height = $rate * $height;
			$sta_l = $new_height/2-$fix/2;
			$sta_w = 0;
		}else{//横系
			$new_height = $fix;
			$rate = $new_height / $height; //圧縮比
			$new_width = $rate * $width;
			$sta_l = 0;
			$sta_w = $new_width/2-$fix/2;
		}
		//空の画像を生成
		$new_image = ImageCreateTrueColor($fix,$fix);
		//画像を上記の空の画像にコピー
		ImageCopyResized($new_image,$image,0,0,$sta_w,$sta_l,$new_width,$new_height,$width,$height);
		/*
		//即納マークをコピー
		$mass = ImageCreateFromPng(BASEURL."/img/sokunou.png"); 
		$soku_margin = 20;
		$soku_x = $soku_margin;
		$soku_y = $soku_margin;
		//$soku_y = $fix-$soku_margin-56;
		ImageCopy($new_image, $mass, $soku_x, $soku_y, 0, 0, 92, 56); 
		*/
		//画像を出力
		if($f_type[1] == "JPG" || $f_type[1] == "jpg"){
			Header("Content-type: image/jpeg");
			ImageJPEG($new_image,NULL,100);
		}elseif($f_type[1] == "PNG" || $f_type[1] == "png"){
			Header("Content-type: image/png");
			ImagePNG($new_image,NULL,100);
		}elseif($f_type[1] == "GIF" || $f_type[1] == "gif"){
			Header("Content-type: image/gif");
			ImageGIF($new_image,NULL,100);
		}
	}
	
}
?>
