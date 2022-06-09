<?php
session_start();

$identity = getIdentity();
$key = $identity['key'];

$mode = $_GET['mode'];
switch ($mode) {
  case "find":
    $search = urldecode($_GET['term']);
    return searchAddress($key,$search);
    break;
  case "gete":
    $address = urldecode($_GET['add']);
    $addressId = $_GET['aid'];
    return getEircode($key,$address,$addressId);
    break;
  case "geta":
    $eircode = urldecode($_GET['ec']);
    return getAddress($key,$eircode);
    break;
  default:
    return "Error";
}



//this gives us our existing key, or a new one if needed
function getIdentity() {
    $url = 'https://api-finder.eircode.ie/Latest/findergetidentity?';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:82.0) Gecko/20100101 Firefox/82.0',
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.5',
        'Origin: https://finder.eircode.ie',
        'DNT: 1',
        'Connection: keep-alive',
        'Referer: https://finder.eircode.ie/'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ($ch);
    if (curl_error($ch)){
        echo curl_error($ch);
    }
    curl_close ($ch);

    $response = json_decode($response,true);
    //key/value/trackingId
    return $response;
}


//this returns the full address based on the above search, it will continue to
//return multiple address until one is selected as valid

function searchAddress($key, $search) {
    $url = "https://api-finder.eircode.ie/Latest/finderautocomplete?";
    $url .= "key=" . $key;
    $url .= "&address=" . urlencode($search);
    $url .= "&language=en";
    $url .= "&geographicAddress=true";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:82.0) Gecko/20100101 Firefox/82.0',
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.5',
        'Origin: https://finder.eircode.ie',
        'DNT: 1',
        'Connection: keep-alive',
        'Referer: https://finder.eircode.ie/'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ($ch);
    if (curl_error($ch)){
        echo curl_error($ch);
    }
    $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $response_size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close ($ch);
    
    if ($response_size <= 0) {
        //TODO - display Error on screen
        return false;
    }


    $addresses = json_decode($response,true);
    $temp = json_encode($addresses['options']);
    echo $temp;

    //echo urlencode($temp);
}

//https://api-finder.eircode.ie/Latest/finderfindaddress?key=_b053ca00-aae2-4efa-a047-446dec11235b//txn=ef9L4tW1nI072PMafRQEEcbH0laAbzrl0KWzSNmSPDD2OWDtHHLvBOOT-ujXqkz1
//address=APARTMENT 8, KIRWAN'S CORNER, KIRWANS LANE, GALWAY
//addressId=X3hysX0Cikx3J_Du8QTv-4fO6Asfw0MZ0vI35WHE14k=
//limit=-1
//geographicAddress=True
//__reserved.Originator=AutoComplete



//validation https://stackoverflow.com/a/33408964
function getEircode($key,$address, $addressId=null) {


    $url = "https://api-finder.eircode.ie/Latest/finderfindaddress?";
    $url .= "key=" . $key;
    $url .= "&address=" . urlencode($address);
    $url .= "&addressId=" . $addressId;
    $url .= "&language=en";
    $url .= "&geographicAddress=true";

    $log = fopen('php_calls.log','a+');
    fwrite($log, $url . PHP_EOL);
    fclose($log);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = [
        'authority: api-finder.eircode.ie',
        'accept: application/json, text/plain, */*',
        'accept-language: en-GB,en-US;q=0.9,en;q=0.8',
        'referer: https://finder.eircode.ie/',
        'origin: https://finder.eircode.ie',
        'sec-fetch-site: same-site',
        'sec-fetch-mode: cors',
        'sec-fetch-dest: empty',
        'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $output = curl_exec ($ch);
    if (curl_error($ch)){
        echo curl_error($ch);
    }
    curl_close ($ch);
    //echo "<pre>" . print_r($output,true) . "</pre>";
    
    $address = json_decode($output,true);
    if(isset($address['postcode'])) {
        echo $address['postcode'];
    }
    else {
        echo "Unavailable";
    }
    //echo "<pre>" . print_r($address,true) . "</pre>";
}








function getAddress($key,$eircode) {


    $url = "https://api-finder.eircode.ie/Latest/finderfindaddress?";
    $url .= "key=" . $key;
    $url .= "&address=" . $eircode;
    $url .= "&language=en";
    $url .= "&geographicAddress=true";
    //$url .= "&clientVersion=8e00ec22";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = [
        'authority: api-finder.eircode.ie',
        'accept: application/json, text/plain, */*',
        'accept-language: en-GB,en-US;q=0.9,en;q=0.8',
        'referer: https://finder.eircode.ie/',
        'origin: https://finder.eircode.ie',
        'sec-fetch-site: same-site',
        'sec-fetch-mode: cors',
        'sec-fetch-dest: empty',
        'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $output = curl_exec ($ch);
    if (curl_error($ch)){
        echo curl_error($ch);
    }
    curl_close ($ch);

    $output = json_decode($output,true);
    //echo "<pre>" . print_r($output,true) . "</pre>";
    $address = $output['postalAddress'];
    echo json_encode($address);
    //echo "<pre>" . print_r($address,true) . "</pre>";
}
?>

