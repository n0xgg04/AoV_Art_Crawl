<?php
function getAPIList($skinId,$mode){
    switch($mode){
        case "art":
        return json_decode(str_replace('##ID##',$skinId,'{
            "tw":"https://dl.ops.kgtw.garenanow.com/CHT/HeroTrainingLoadingNew_B36/##ID##.jpg",
            "cn":"https://image.ngame.proximabeta.com/eoa/CNEXP/CHS/HeroTrainingLoading/##ID##.jpg",
            "th":"https://dlmobilegarena-a.akamaihd.net/kgth/hok/HeroTrainingLoadingNew/##ID##.jpg",
            "kr":"http://penta.gcdn.netmarble.com/penta/16Languages/HeroTrainingLoading/##ID##.jpg",
            "jp":"https://image.ngame.proximabeta.com/eoa/Languages/JP_Tencent_JP/image/HeroTrainingLoading/##ID##.jpg",
            "us" : "http://image.ngame.proximabeta.com/eoa/Tencent/EU/EN/HeroTrainingLoading/##ID##.jpg"
        }
        '),true);
        break;

        case "mart":
            $hero=substr($skinId,0,3);
            $skin=str_replace($hero,'',$skinId);
            if($skin[0]=='0') $skin=substr($skin,1,strlen($skin));
            return json_decode(str_replace('##IDICON##',"30{$hero}{$skin}",'
            {
                "tw":"https://dl.ops.kgtw.garenanow.com/CHT/HeroHeadPath/##IDICON##.jpg",
                "cn":"https://image.ngame.proximabeta.com/eoa/CNEXP/CHS/HeadFrame/##IDICON##.jpg",
                "vn":"https://dl.ops.kgvn.garenanow.com/hok/HeroHeadPath/##IDICON##.jpg",
                "jp":"https://image.ngame.proximabeta.com/eoa/Languages/JP_Tencent_JP/image/HeroHead/##IDICON##.jpg"
                ,"us":"http://image.ngame.proximabeta.com/eoa/Tencent/EU/EN/CDNHeroHeadPath/##IDICON##.jpg"
            }
            '),true);
        break;

        case "hart":
            $hero=substr($skinId,0,3);
            $skin=str_replace($hero,'',$skinId);
            if($skin[0]=='0') $skin=substr($skin,1,strlen($skin));
            return json_decode(str_replace('##IDICON##',"30{$hero}{$skin}",'
        {
            "tw":"https://dl.ops.kgtw.garenanow.com/CHT/HeroHeadPath/##IDICON##.jpg",
            "cn":"https://image.ngame.proximabeta.com/eoa/CNEXP/CHS/HeadFrame/##IDICON##.jpg",
            "vn":"https://dl.ops.kgvn.garenanow.com/hok/HeroHeadPath/##IDICON##.jpg",
            "jp":"https://image.ngame.proximabeta.com/eoa/Languages/JP_Tencent_JP/image/HeroHead/##IDICON##.jpg"
            ,"us":"http://image.ngame.proximabeta.com/eoa/Tencent/EU/EN/CDNHeroHeadPath/##IDICON##.jpg"
        }
        '),true);
        break;

        default:
         return array();
        break;
  }
}

function response($api){
    $ch = curl_init($api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    return $result;
}


function noti($id,$sv,$sp){
    $conf=json_decode(file_get_contents('./json/config.json'),true);
    switch($sp){
        case "ART":
            $link="https://nxz.pw/art/{$id}";
        break;

        case "MART":
            $hero=substr($id,0,3);
            $skin=str_replace($hero,'',$id);
            if($skin[0]=='0') $skin=substr($skin,1,strlen($skin));
            $link="https://nxz.pw/art/miniart.php?id=30{$hero}{$skin}";
        break;
     
        case "HART":
            $hero=substr($id,0,3);
            $skin=str_replace($hero,'',$id);
            if($skin[0]=='0') $skin=substr($skin,1,strlen($skin));
            $link="https://nxz.pw/art/headart.php?id=30{$hero}{$skin}";
        break;

    }
    sendChat($conf['telegram']['token'],$conf['telegram']['chatId'],"[NOX AUTO CRAWL TOOL v2.0.0]\n\n Tìm thấy {$sp} mới ({$id}) tại server [{$sv}]\n({$link})[Download] ");
}

function sendChat($token,$chatId,$text){
    $result=json_decode(response('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chatId.'&text='.urlencode($text)).'&parse_mode=MarkdownV2',true);
}


function is_url_image($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    $output = curl_exec($ch);
    curl_close($ch);

    $headers = array();
    foreach(explode("\n",$output) as $line){
        $parts = explode(':' ,$line);
        if(count($parts) == 2){
            $headers[trim($parts[0])] = trim($parts[1]);
        }

    }

    return isset($headers["Content-Type"]) && strpos($headers['Content-Type'], 'image/') === 0;
}

function grab_image($url,$saveto){
    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($saveto)){
        unlink($saveto);
    }
    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);
}

function delTree($dir)
    { 
        if(!is_dir($dir)) return 1;
        $files = array_diff(scandir($dir), array('.', '..')); 
        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
        }
        return rmdir($dir); 
    } 

function resetArtList($mode){
    global $argv;
    if(!is_dir("./{$mode}/")) return 0;
    if(empty($argv['3'])&&empty($argv['4']))
    foreach(glob("./{$mode}/") as $folder){
        delTree($folder);
    } else {
        if(!empty($argv['3'])&&!empty($argv['4'])){
            $kt=json_decode(file_get_contents("./json/hero.json"),true);
            for($i=$argv[3];$i<=$argv[4];$i++) if(in_array($i,$kt))  delTree("./{$mode}/{$i}");
        }else{
            delTree("./{$mode}/{$argv[3]}");
        }
    }
}

function resett($dir,$mode){
    $data=json_decode(file_get_contents($dir),true);
    $data[$mode]=array();
    file_put_contents($dir,json_encode($data));
}

function resetArtFolder($mode){
    global $argv;
    if(empty($argv['3'])&&empty($argv['4']))
    foreach(glob("./data/") as $folder){
        resett("{$folder}/list.json",$mode);
    } else {
        if(!empty($argv['3'])&&!empty($argv['4'])){
            $kt=json_decode(file_get_contents("./json/hero.json"),true);
            for($i=$argv[3];$i<=$argv[4];$i++) if(in_array($i,$kt))  resett("./data/{$i}/list.json",$mode);
        }else{
            resett("./data/{$argv[3]}/list.json",$mode);
        }
    }
}