<?php

	# 投稿の有無 (投稿: true)
	$posting = true;
	$posting_msky  = true;
	$posting_nostr = false;
	$posting_bsky  = false;

	# フォルダパス
	$folder = 'ここにフルパスを記述';

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
	# プライベートかどうか
	if ($result['response']['checkins']['items'][0]['private'] == true) {

		$now_checkin[3] = 1;

	 } else {

		$now_checkin[3] = 0;

	}

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
	$now_checkin[4] = $result2['response']['checkin']['checkinShortUrl'];

	# ファイル書き込み
	if (!empty($result['response'])) {
    	$filew = fopen($filename, 'w');
    	$req_date = date("Y-m-d H:i T", $_SERVER['REQUEST_TIME']);
    	$wcsv = array($req_date, $now_checkin[0], $now_checkin[1], $now_checkin[2], $now_checkin[3], $now_checkin[4]);
    	fputcsv($filew, $wcsv);
    	fclose($filew);
	}

	# 投稿データ作成
	if (($checkin_data[0] != $now_checkin[0]) && isset($now_checkin[0])) {

		if ($now_checkin[3] == 0) {

			$post_arr = array();
			$post_arr[] = "I'm at ";
			$post_arr[] = $now_checkin[2];
			$post_arr[] = "\n";
			$post_arr[] = $now_checkin[4];
			$post_arr[] = "\n";
			$post_arr[] = "Total Checkin Count: ";
			$post_arr[] = $now_checkin[1];

			$post_data = implode($post_arr);

		} else {

			$post_data == "";

		}

	} else {

		$post_data == "";

	}

	# 投稿
	if ($posting == true) {

		# Misskey
		# サーバーとアクセストークンを設定するだけで動きます
		if ($posting_msky == true) {

			if (!empty($post_data)) {

				$data = [
					'i' => 'Misskey Access Token',
					'text' => $post_data,
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

			if (!empty($post_data)) {

				$data2 = [
						'tweet' => $post_data
				];

				$json_data2 = json_encode($data2);

				$put_url2 = 'http://127.0.0.1:10000/post';

				$ch = curl_init($put_url2);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data2);
				curl_exec($ch);
				curl_close($ch);

			}

		}

		# Bluesky
		# https://github.com/mattn/bsky を使う前提のサンプル
		if ($posting_bsky == true) {

			if (!empty($post_data)) {

				$data3 = [
						'tweet' => $post_data
				];

				$json_data3 = json_encode($data3);

				$put_url3 = 'http://127.0.0.1:10010/post';

				$ch = curl_init($put_url3);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data3);
				curl_exec($ch);
				curl_close($ch);

			}

		}

	}

?>
