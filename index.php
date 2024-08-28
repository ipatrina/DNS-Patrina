<?php

	// DNS Patrina
	// Version: 6.0.1
	// Date: 2024.07

	include 'config.php';
	error_reporting(E_ALL & ~E_WARNING);

	function returnOK() {
		global $host;
		http_response_code(200);
		if (true || startsWith($_SERVER["REQUEST_URI"], "/ph/update") || startsWith($_SERVER["REQUEST_URI"], "/dyndns/update") || startsWith($_SERVER["REQUEST_URI"], "/nic/update") || startsWith($_SERVER["REQUEST_URI"], "/v3/update")) {
			print("good ".$host);
		}
		else {
			print_status("200 OK");
		}
	}

	function returnFail() {
		http_response_code(503);
		print_status("503 Service Unavailable");
	}

	function print_status($content) {
		print "<html><head><title>".$content."</title></head><body><center><h1>".$content."</h1></center><hr><center>nginx</center></body></html>";
	}

	function startsWith(string $string, string $start) {
		return strrpos($string, $start, - strlen($string)) !== false;
	}

	function endsWith(string $string, string $end) {
		return ($offset = strlen($string) - strlen($end)) >= 0 && strpos($string, $end, $offset) !== false;
	}

	function match_fn($pattern, $str) {
		return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $str);
	}

	function match_string($pattern, $str)
	{
		$pattern = preg_replace('/([^*])/e', 'preg_quote("$1", "/")', $pattern);
		$pattern = str_replace('*', '.*', $pattern);
		return (bool) preg_match('/^' . $pattern . '$/i', $str);
	}

	function getXMLTag($xmlData, $record_id, $record_value, $tag) {
		for ($i = 0; $i < $xmlData->length; $i++) {
			if ($xmlData->item($i)->getElementsByTagName($record_id)->item(0)->nodeValue == $record_value) {
				return $xmlData->item($i)->getElementsByTagName($tag)->item(0)->nodeValue;
			}
		}
		return "";
	}

	http_response_code(500);

	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		http_response_code(401);
		header('WWW-Authenticate: Basic realm="Authentication Required"');
		print_status("401 Unauthorized");
		exit;
	}
	else {
		$username = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		$host = $_SERVER['REMOTE_ADDR'];
		$domain = "";

		if (isset($_GET['hostname'])) {
			$domain = $_GET['hostname'];
		}

		if (isset($_GET['myip'])) {
			$host = $_GET['myip'];
		}

		$user_auth = false;
		for ($row = 0; $row < count($users); $row++) {
			if ($username == $users[$row][0] && $password == $users[$row][1]) {
				$userHosts = explode(',', $users[$row][2]);
				for ($userHost = 0; $userHost < count($userHosts); $userHost++) {
					if (match_fn($userHosts[$userHost], $domain)) {
						$user_auth = true;
						break;
					}
				}
			}
		}

		if (!$user_auth || !strstr($domain, '.')) {
			http_response_code(401);
			header('WWW-Authenticate: Basic realm="Authentication Required"');
			print_status("401 Unauthorized");
			exit;
		}

		$sld = "";
		$subdomain = "";
		$provider = "";
		$parameter = "";
		for ($row = 0; $row < count($domains); $row++) {
			if (endsWith($domain, '.'.$domains[$row][0])) {
				$sld = $domains[$row][0];
				$subdomain = substr($domain, 0, strlen($domain) - strlen($sld) - 1);
				$provider = $domains[$row][1];
				$parameter = $domains[$row][2];
				break;
			}
		}

		$rrtype = "A";
		if (strpos($host, ":") !== false) {
			$rrtype = "AAAA";
		}

		if ($provider == "DNSOWL") {
			$dnsowl_xml = file_get_contents('https://www.namesilo.com/api/dnsListRecords?version=1&type=xml&key='.$parameter.'&domain='.$sld);
			$xml = new DOMDocument();
			$xml->loadXML($dnsowl_xml);
			$xmlData = $xml->getElementsByTagName('resource_record');

			if ($xml->getElementsByTagName('code')->item(0)->nodeValue == "300") {
				$dnsowl_record_id = getXMLTag($xmlData, "host", $domain, "record_id");
			}
			else {
				returnFail();
				exit;
			}

			if (strlen($dnsowl_record_id) > 0) {
				$dnsowl_xml = file_get_contents('https://www.namesilo.com/api/dnsDeleteRecord?version=1&type=xml&key='.$parameter.'&domain='.$sld.'&rrid='.$dnsowl_record_id);
				$xml = new DOMDocument();
				$xml->loadXML($dnsowl_xml);
				if ($xml->getElementsByTagName('code')->item(0)->nodeValue != "300") {
					returnFail();
					exit;
				}
			}

			$dnsowl_xml = file_get_contents('https://www.namesilo.com/api/dnsAddRecord?version=1&type=xml&key='.$parameter.'&domain='.$sld.'&rrtype='.$rrtype.'&rrhost='.$subdomain.'&rrvalue='.$host.'&rrttl=3600');
			$xml = new DOMDocument();
			$xml->loadXML($dnsowl_xml);
			if ($xml->getElementsByTagName('code')->item(0)->nodeValue == "300") {
				returnOK();
				exit;
			}
			else {
				returnFail();
				exit;
			}
		}
		elseif ($provider == "ZONE") {
		    $zone_file = $parameter."/".$domain.".".$rrtype;
			file_put_contents($zone_file, $host);
			chmod($zone_file, 0777);
			returnOK();
			exit;
		}
		else {
			http_response_code(403);
			print_status("403 Forbidden");
			exit;
		}
	}

?>
