<?php
/**
 * Created by PhpStorm.
 * User: Femi 'Nefa
 * Date: 4/24/2017
 * Time: 12:33 AM
 */
ini_set('display_errors', true);
@session_start();

//check if this is an authentication request
if(isset($_GET['isante_session'])) {
    $iSession=@json_decode($_REQUEST['isante_session']);
    //if the user object does not have a sesssionID, go to login

    if(isset($iSession->sessionId)) {
        //else validate the session
        // $url=$session->user->person->links[0]->uri;
        $config = getConfiguration();
      //  print_r($config); die;
        if (empty(@$config['isante']['api_path'])) {
            $config = getConfiguration(['api_path' => $config['isante']['url'].'/openmrs/ws/rest/v1']);
        }
       // print_r($iSession->sessionId);
        $api_url = rtrim($config['isante']['api_path'], "/\\");
        //verify cookie
        $data = @json_decode(fetch($api_url . '/session', [
            'cookie' => 'jsessionid=' . $iSession->sessionId.';JSESSIONID=' . $iSession->sessionId.';']));

        if ($data->authenticated) {

            $data->user->settings=$config['isante']['url'].'/openmrs/adminui/myaccount/myAccount.page';
            $_SESSION['openmrs_user'] = $data->user;

            header("location:index.php?lang=".explode('_',$data->user->userProperties->defaultLocale)[0]);
        }
    }
}

if(!isset($_SESSION['openmrs_user'])) {
    //redirect back to isante server
    $config = getConfiguration();
    header("location:".$config['isante']['url']."/openmrs/owa/indicators/index.html");
    die;
}

function fetch( $url, $z=null ) {
    $ch =  curl_init();
    $useragent = isset($z['useragent']) ? $z['useragent'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POST, isset($z['post']) );
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = array(
        'Content-type: text/plain',
    );
    if( isset($z['cookie']) ) {
        $headers[]='Cookie: '.$z['cookie'];
    }
    print_r($headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);

    if( isset($z['post']) )         curl_setopt( $ch, CURLOPT_POSTFIELDS, $z['post'] );
    if( isset($z['refer']) )        curl_setopt( $ch, CURLOPT_REFERER, $z['refer'] );

    curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, ( isset($z['timeout']) ? $z['timeout'] : 5 ) );
    if(!file_exists('tmp')) mkdir('tmp');
    curl_setopt( $ch, CURLOPT_COOKIEJAR,  __DIR_.'/tmp/session' );
    curl_setopt( $ch, CURLOPT_COOKIEFILE,  __DIR_.'/tmp/session' );

    $result = curl_exec( $ch );
    curl_close( $ch );

    return $result;
}

/*
 * Load or store configuration
 */
function getConfiguration($conf=[]) {
    $config_file=__DIR__.'/isante_bridge.json';
    $config=['isante'=>['url'=>'']];


    if(!file_exists($config_file) && !empty($conf)) {
        $file = fopen($config_file, "w") or die("Unable to create config file in root directory!");
        fwrite($file, json_encode($config));
        fclose($file);
    }else{
        if(file_exists($config_file))
            $config=json_decode(file_get_contents($config_file), true);
    }
    foreach($conf as $k=>$v) {
        if(!isset($config['isante'][$k]) || empty($config['isante'][$k])) $config['isante'][$k]=$v;
    }
    $config['isante']['url']=rtrim($config['isante']['url'], '/\\');

    return $config;
}