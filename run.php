<?php

	# エラー表示
	ini_set( 'display_errors', 1 );

	# フォルダパス
	$folder = 'ここにフルパス(/で終わる)を記述';

	# ライブラリ読み込み
	require $folder . 'vendor/autoload.php';
	use Abraham\TwitterOAuth\TwitterOAuth;

	# 投稿の有無 (投稿: true)
	$posting = true;
	$posting_twtr    = false;
	$posting_msky    = false;
	$posting_nostr   = false;
	$posting_bsky    = false;
	$posting_concrnt = false;

	# 旧Twitter API Key
	$twtr_apikey      = 'API Key (Consumer Key)';
	$twtr_apisecret   = 'API Secret (Consumer Secret)';
	$twtr_accesstoken = 'OAuth Access Token';
	$twtr_tokensecret = 'OAuth Access Token Secret';

	# 設定
	$userid = 'Foursquare の設定ページに書かれているユーザーID (数字) を記述';
	$token = 'アクセストークンを記述';

	# API Endpoint
	$api = 'https://api.foursquare.com/v2/users/' . $userid . '/checkins?v=20220722&oauth_token=' . $token;

	# 取得
	$ch = curl_init($api);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, 'Foursquare_API');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$json = mb_convert_encoding($response, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$result = json_decode($json, true);

	# ファイル読み込み
	$filename = $folder . 'data.csv';
	$filer = fopen($filename, 'r');
	$rcsv = fgetcsv($filer);
	$checkin_data[0] = $rcsv[1];
	$checkin_data[1] = $rcsv[2];
	$checkin_data[2] = $rcsv[3];
	$checkin_data[3] = $rcsv[4];
	$checkin_data[4] = $rcsv[5];
	fclose($filer);

	# チェックインID
	$now_checkin[0] = $result['response']['checkins']['items'][0]['id'];
	# チェックイン数
	$now_checkin[1] = $result['response']['checkins']['count'];
	# ベニュー名
	$now_checkin[2] = $result['response']['checkins']['items'][0]['venue']['name'];

	# 都道府県
	$now_checkin[3] = $result['response']['checkins']['items'][0]['venue']['location']['state'];
	# 市区町村
	$now_checkin[4] = $result['response']['checkins']['items'][0]['venue']['location']['city'];
	# 住所
	$now_checkin[5] = $result['response']['checkins']['items'][0]['venue']['location']['address'];
	# 国名
	$now_checkin[6] = $result['response']['checkins']['items'][0]['venue']['location']['cc'];

	# 共有用リンク取得
	$api2 = 'https://api.foursquare.com/v2/checkins/' . $now_checkin[0] . '?v=20220722&oauth_token=' . $token;
	$ch = curl_init($api2);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, 'Foursquare_API');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response2 = curl_exec($ch);
	curl_close($ch);
	$json2 = mb_convert_encoding($response2, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$result2 = json_decode($json2, true);
	$now_checkin[7] = $result2['response']['checkin']['checkinShortUrl'];

	# プライベートかどうか
	if ($result['response']['checkins']['items'][0]['private'] == true) {

		$now_checkin[8] = 1;

	 } else {

		$now_checkin[8] = 0;

	}

	# ファイル書き込み
	# チェックインID, チェックイン数, ベニュー名, 都道府県, 市区町村, 住所, 国名, 共有用リンク, プライベートかどうか
	if (!empty($result['response'])) {
    	$filew = fopen($filename, 'w');
    	$req_date = date("Y-m-d H:i T", $_SERVER['REQUEST_TIME']);
    	$wcsv = array($req_date, $now_checkin[0], $now_checkin[1], $now_checkin[2], $now_checkin[3], $now_checkin[4], $now_checkin[5], $now_checkin[6], $now_checkin[7], $now_checkin[8]);
    	fputcsv($filew, $wcsv);
    	fclose($filew);
	}

	# 投稿データ作成
	if (($checkin_data[0] != $now_checkin[0]) && isset($now_checkin[0])) {

		if ($now_checkin[8] == 0) {

			$post_arr = array();
			$post_arr[] = "I'm at ";
			$post_arr[] = $now_checkin[2];
			$post_arr[] = " in ";
			$post_arr[] = $now_checkin[3] . $now_checkin[4] . $now_checkin[5];
			$post_arr[] = " (";
			$post_arr[] = $now_checkin[6];
			$post_arr[] = ")\n";
			$post_arr[] = $now_checkin[7];
			$post_arr[] = "\n";
			$post_arr[] = "Total Checkin Count：";
			$post_arr[] = $now_checkin[1];

			$post_arr1 = $post_arr;
			$post_arr2 = $post_arr;
			$post_arr3 = $post_arr;
			$post_arr4 = $post_arr;
			$post_arr5 = $post_arr;

			$post_arr1[] = "\n#Swarm_to_X";
			$post_arr2[] = "\n#Swarm_to_Misskey";
			$post_arr3[] = "\n#Swarm_to_Nostr";
			$post_arr4[] = "\n#Swarm_to_Bluesky";
			$post_arr5[] = "\n#Swarm_to_Concrnt";

			$post_data1 = implode($post_arr1);
			$post_data2 = implode($post_arr2);
			$post_data3 = implode($post_arr3);
			$post_data4 = implode($post_arr4);
			$post_data5 = implode($post_arr5);

		} else {

			$post_data1 == "";
			$post_data2 == "";
			$post_data3 == "";
			$post_data4 == "";
			$post_data5 == "";

		}

	} else {

		$post_data1 = "";
		$post_data2 = "";
		$post_data3 = "";
		$post_data4 = "";
		$post_data5 = "";

	}

	# 投稿
	if ($posting == true) {

		# 旧Twitter
		if ($posting_twtr == true) {

			if (!empty($post_data1)) {

				$twtr_connection = new TwitterOAuth($twtr_apikey, $twtr_apisecret, $twtr_accesstoken, $twtr_tokensecret);
				$twtr_connection->setApiVersion('2');
				$twtr_result = $twtr_connection->post('tweets', ['text' => $post_data1], ['jsonPayload' => true]);

			}

		}

		# Misskey
		# misskey.io の場合、サーバーとアクセストークンを設定するだけで動きます
		if ($posting_msky == true) {

			if (!empty($post_data2)) {

				$data = [
					'i' => 'Misskey Access Token',
					'text' => $post_data2,
					'visibility' => 'public'
				];

				$json_data = json_encode($data);

				$put_url = 'https://misskey.io/api/notes/create';

				$ch = curl_init($put_url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_exec($ch);
				curl_close($ch);

			}

		}

		# Nostr
		# https://github.com/mattn/algia を使う前提のサンプル
		if ($posting_nostr == true) {

			if (!empty($post_data3)) {

				$data = [
					'note' => $post_data3
				];

				$json_data = json_encode($data);

				$put_url = 'http://127.0.0.1:10000/post';

				$ch = curl_init($put_url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_exec($ch);
				curl_close($ch);

			}

		}

		# Bluesky
		# https://github.com/mattn/bsky を使う前提のサンプル
		if ($posting_bsky == true) {

			if (!empty($post_data4)) {

				$data = [
					'note' => $post_data4
				];

				$json_data = json_encode($data);

				$put_url = 'http://127.0.0.1:10010/post';

				$ch = curl_init($put_url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_exec($ch);
				curl_close($ch);

			}

		}

		# Concurrent
		# https://github.com/rassi0429/concurrent-webhook を使う前提のサンプル
		if ($posting_concrnt == true) {

			if (!empty($post_data5)) {

				$data = [
					'text' => $post_data5
				];

				$json_data = json_encode($data);

				$put_url = 'http://127.0.0.1:10020/post';

				$ch = curl_init($put_url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_exec($ch);
				curl_close($ch);

			}

		}

	}

?>
