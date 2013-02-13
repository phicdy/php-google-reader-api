<?php

class GoogleReader {
	//ログイン
	private $data=array(
		'Email' => null,
		'Passwd' => null,
		'service'=> 'reader'
	);


	function __construct($email,$passwd) {
		$this->data['Email'] = $email;
		$this->data['Passwd'] = $passwd;
	}

	function getFeed($feedUrl) {
		$url='https://www.google.com/accounts/ClientLogin';
		$headers = array(
			"Content-Type: application/x-www-form-urlencoded",
			'Content-Length: '.strlen(http_build_query($this->data)),
		);

		$options = array(
			'http' => array(
				'method' => 'POST',
				'content' => http_build_query($this->data),
				'header' => implode("\r\n", $headers),
			)
		);
		$contents = file_get_contents($url, false, stream_context_create($options));

		//Authキーを取得
		preg_match('/Auth=(.+)/',$contents,$match);
		$auth = $match[1];

		//データ取得
		$headers = array(
			'Authorization: GoogleLogin auth='.$auth
		);
		//$feedUrl = "http://www.google.com/reader/api/0/user-info";
		$feedUrl = "http://www.google.com/reader/atom/feed/".$feedUrl."?n=1000&xt=user/-/state/com.google/read";
		$options = array(
			'http' => array(
				'method' => 'GET',
				'header' => implode("\r\n", $headers),
			)
		);
		$contents = file_get_contents($feedUrl, false, stream_context_create($options));

		$xml = new SimpleXMLElement($contents);
		unset($contents);

		//記事URL取得
		$urls = array();
		$analyzer = new Jin115Analyzer();
		$fp = fopen("urls.html","w");
		foreach($xml->entry as $key=>$entry) {
			$url = (string)$entry->link["href"];
			//if($analyzer->analyzeAA($url)){
			//	array_push($urls,$url);
			//}
			//array_push($urls,$url);

			fwrite($fp,$url."\n");
		}
	}
}

?>
