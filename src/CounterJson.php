<?php
define("COUNTER_LOG", "/var/log/apache2/jsgi-map-counter.txt");

counterAPI();
/**
 * カウンターAPI
 */
function counterAPI() {
	if (preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
		// 圧縮可能であれば圧縮する
		ini_set("zlib.output_compression","On");
	}
	if (defined("ACCEPTABLE_REFERER") && !isset($_SERVER["HTTP_REFERER"])) {
		// リファラーが設定されているのに、リクエストにリファラーが無い
		sendResult();
		return;
	}
	$referer = $_SERVER["HTTP_REFERER"];
	if (defined("ACCEPTABLE_REFERER") && strpos($referer,ACCEPTABLE_REFERER) === FALSE) {
		// 設定されているリファラーとリクエストのリファラーが一致しない
		sendResult();
		return;
	}
	if (!defined("COUNTER_LOG")) {
		// ログの出力先が設定されていない
		sendResult();
		return;
	}		
	
	ini_set("date.timezone", "Asia/Tokyo");
	$outputData = array();
	
	$today = date("Ymd");
	$yesterday =date("Ymd",strtotime("-1 days"));
	$totalNum = 1;
	$todayNum = 1;
	$yesterdayNum = 0;
	if (!file_exists(COUNTER_LOG)) {
		// 初期状態
		$results = array("total"=>$totalNum,$today=>$todayNum,$yesterday=>0);
		if (file_put_contents(COUNTER_LOG,json_encode($results)) === FALSE) {
			// ログの出力が行えない
			sendResult();
			return;
		}
	} else {
		$fp = fopen(COUNTER_LOG,"r+");
		if ($fp && flock($fp,LOCK_EX)) {
			// ファイルからデータを読み込み
			$data = fread($fp,1024);
			$results = json_decode($data,true);
			// データを更新して書き込み
			$totalNum = $results["total"] + 1;
			if (isset($results[$today])) {
				$todayNum = $results[$today] + 1;
			}
			if (isset($results[$yesterday])) {
				$yesterdayNum = $results[$yesterday];
			}
			$newRecord = array(
				"total"=>$totalNum,
				$today=>$todayNum,
				$yesterday=>$yesterdayNum
			);
			rewind($fp);
			$fileSize = fwrite($fp,json_encode($newRecord));
			ftruncate($fp,$fileSize);
			flock($fp, LOCK_UN);
		} else {
			// ログの出力が行えない
			sendResult();
		}
	}
	$results = array(
		"total" => $totalNum,
		"today" => $todayNum,
		"yesterday" => $yesterdayNum
	);
	$outputData["results"] = &$results;
	
	sendResult($outputData);
}
/**
 * JSONデータを送信
 * @param array $result 送信する処理結果
 */
function sendResult(&$result = NULL) {
	header('Content-type: application/json; charset=utf-8');
	header('Access-Control-Allow-Origin: *');
	if ($result != NULL) {
		$jsonData = json_encode($result);
		echo $jsonData;
	} else {
		echo "{}";
	}
}
