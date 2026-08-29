<?php
class common {
	//暗号化
	public function encode_ssl($str = NULL){
		if(!$str){
			return false;
		}else{
			return openssl_encrypt($str, 'AES-128-ECB', $this->call_key());
		}
	}
	
	//複合化
	public function decode_ssl($str = NULL){
		if(!$str){
			return false;
		}else{
			return openssl_decrypt($str, 'AES-128-ECB', $this->call_key());
		}
	}
	
	
	//暗号化キーの呼び出し
	private function call_key(){
		return file_get_contents(dirname(__FILE__)."/Common/txt.txt");
	}
	//商品用ページャーの生成
	public function pager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\"><span aria-hidden=\"true\">&laquo; 前へ</span></a></li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
			}
			$pager.= "</ul></nav>";
			$this->view->pager = $pager;
		}
	}
	//商品用ページャーの生成
	public function pagerf($category = "",$keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\"><span aria-hidden=\"true\">&laquo; 前へ</span></a></li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li><a href=\"".BASEURL.$page."?category=".$category."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
			}
			$pager.= "</ul></nav>";
			$this->view->pager = $pager;
		}
	}
	//商品カテゴリ用ページャーの生成
	public function categorypager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\"><span aria-hidden=\"true\">&laquo; 前へ</span></a></li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li><a href=\"".BASEURL.$page."?keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
			}
			$pager.= "</ul></nav>";
			$this->view->pager = $pager;
		}
	}
	//注文用ページャーの生成
	public function orderpager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\"><前へ</a></li>";
			}
			if($p > 5){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0\"><<最初へ</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+5 || $i < $p-5){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+5 < ceil($n/$limit)){
				$pager.= "<li class=\"active\" ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".ceil($n/$limit)."\">…</a></li>";
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">最後へ（".ceil($n/$limit)."）>></a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."\"><span aria-hidden=\"true\">次&raquo;</span></a></li>";
			}
			$pager.= "</ul></nav>";
			$this->view->pager = $pager;
		}
	}
	
	//会員用ページャーの生成
	public function userpager($keyword="",$n=0,$limit=10,$p=0,$page){
		if($n > $limit){
			$pager = "<nav><ul class=\"pagination pagination-sm\">";
			if(!empty($p)){
				$prev = $p-1;
				$pager.= "<li><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$prev."\" aria-label=\"Previous\">
<span aria-hidden=\"true\">&laquo; 前へ</span>
</a>
</li>";
			}
			if($p > 2){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=0\">1</a></li>";
			}
			for($i=0;$i<ceil($n/$limit);$i++){
				$pn = $i+1;
				if($i>$p+2 || $i < $p-2){
				}else{
					if($i == $p){
						$avtive = " class=\"active\"";
					}else{
						$avtive = "";
					}
					$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$i."\">".$pn."</a></li>";
				}
			}
			if($p+2 < ceil($n/$limit)){
				$pager.= "<li$avtive ><a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".(ceil($n/$limit)-1)."\">".ceil($n/$limit)."</a></li>";
			}
			if($start <$n-1){
				$next = $p+1;
				$pager.= "<li>
<a href=\"".BASEURL.$page."?kind=".$kind."&keyword=".$keyword."&limit=".$limit."&p=".$next."\" aria-label=\"Next\">
<span aria-hidden=\"true\">次&raquo;</span>
</a>
</li>";
			}
			$pager.= "</ul></div>";
			$this->view->pager = $pager;
		}
	}
	
	//ユーザーの権限に応じてリダイレクト
	public function redirect($authority = 0,$merchant=NULL){
		if($authority == 1){
			//管理者
			header("Location:".BASEURL."/admin/");
		}elseif($authority == 2){
			//画像提供者
			header("Location:".BASEURL."/editor/");
		}elseif($authority == 3){
			//ユーザー
			header("Location:".BASEURL."/member/");
		}elseif($authority == 4){
			//マーチャント
			header("Location:".BASEURL."/merchant/");
		}else{
			//ログイン画面へ
			if($merchant){
				//マーチャント配下の顧客の場合
				header("Location:".BASEURL."/login/?m=".$merchant);
			}else{
				//マーチャント配下の顧客以外の場合
				header("Location:".BASEURL."/login/");
			}
		}
	}
	//文字列を暗号化
	public function pwHash($pw){
		return sha1("serialize".md5($pw)."by_kmjcrew");
	}
}
?>