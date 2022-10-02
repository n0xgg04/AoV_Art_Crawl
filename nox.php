<?php
include 'function.php';

$argv=$_SERVER['argv'];
$conf=json_decode(file_get_contents('./json/config.json'),true);
echo "--------------------------------------------------\nNoxTool Cmd for Nox Administrator (v2.0.0)\nType \"php nox.php -h\" to see all command.\n--------------------------------------------------\n";

if(isset($argv[1])){
    switch($argv[1]){
        case "-ca": checkart("art");
        break;

        case "-cm": checkart("mart");
        break;

        case "-ch": checkart("hart");
        break;

        case "-r":
            switch($argv[2]){
                case "a":
                    resetArtList("art");
                    resetArtFolder("art");
                    echo "\nDone !";
                break;

                case "ha":
                    resetArtList("hart");
                    resetArtFolder("hart");
                    echo "\nDone !";

                case "ma":
                    resetArtList("mart");
                    resetArtFolder("mart");
                    echo "\nDone !";
                break;

                default:
                    echo "\nWrong option !:\n artlist : reset checked art list\n artfolder : delete all arts downloaded in local.";
                break;
            }
        break;

        case "-d":
        break;

        case "-h":
            echo "\nNoxTool (PHP cmd) for Nox administrator. Command :\n-r <reset mode> : reset data - Type \"-r -h\" to see all arg\n-d <file or dir> : Decompress file by Zstd\n-cp <file or dir> : Compress file by Zstd\n-c <save/nosave> <auto refresh: on/off> : Crawl all art ( Save to local on/off)";
        break;

        default : echo "\n Use -h to see all command.";
        break;
    }
}else{
    echo "\n Unknown arg !";
}

function checkart($mode){
    global $conf;
    global $argv;
    $saveInLocal=0;
    $maxSkip=3;
    echo "\nPress Ctrl + C to stop...";

    if(isset($argv[2])) {
        if($argv[2]=="s"){
            $saveInLocal=1;
            if(!is_dir('./art/')) mkdir('./art/',0777,true);
            echo "\nArts will be saved to ./{$mode}/";
        }

        if($argv[2]=="fs"){
            $saveInLocal=1;
            echo "\nCheck from id {$argv[3]} to {$argv[4]} ...";
            if(!is_dir('./art/')) mkdir('./art/',0777,true);
            echo "\nArts will be saved to ./{$mode}/";
        }
    }

     $kt=json_decode(file_get_contents("./json/hero.json"),true);
     if($argv[2]=="fs"){
        $heroList=array();
        if(empty($argv[4])) $argv[4]=$argv[3];
        for($i=$argv[3];$i<=$argv[4];$i++) if(in_array($i,$kt)) $heroList[]=$i;
     }else{
        $heroList=$kt;
     }

    $founded=array();
    while(true){
        $new="";
        foreach($heroList as $heroId){
            if(!is_dir('./data/'.$heroId)) mkdir('./data/'.$heroId,0777,true);
            if($saveInLocal) if(!is_dir('./art/'.$heroId)) mkdir('./art/'.$heroId,0777,true);
            if($saveInLocal) if(!is_dir('./mart/'.$heroId)) mkdir('./mart/'.$heroId,0777,true);
            if($saveInLocal) if(!is_dir('./hart/'.$heroId)) mkdir('./hart/'.$heroId,0777,true);
            if(!file_exists('./data/'.$heroId.'/list.json')) file_put_contents('./data/'.$heroId.'/list.json',json_encode(array(
                'art' => array(),
                'mart'=>array(),
                'hart'=>array()
            )));
            $arr=json_decode(file_get_contents('./data/'.$heroId.'/list.json'),true);
            $checked=$arr[$mode];
            $skip=0;
            for($skinId=0;$skinId<=20;$skinId++){
                if($skinId<10) $skin="0".$skinId; else $skin=$skinId;
                if(in_array($heroId.$skin,$checked)) continue;
                $API=getAPIList($heroId.$skin,$mode);
                $ok=0;
                foreach($API as $server => $eAPI){
                    $f=0;
                    if(is_url_image($eAPI)){
                        $new.=",".$heroId.$skin;
                        echo "\n\e[0;31;42mFound new {$mode} ({$heroId}{$skin}) in server [{$server}]!\e[0m";
                        $checked[]=$heroId.$skin;
                        $ok=1;
                        if($saveInLocal) grab_image($eAPI,"./{$mode}/{$heroId}/{$heroId}{$skin}.jpg");
                        if($conf['notify']=="on") noti($heroId.$skin,$server,strtoupper($mode));
                        $f=1;
                      }
                      if($f) break;
                    }
                    if($ok) continue;

                    if($skip==$maxSkip){
                        echo "\n";
                        $skip=0;
                        break(1);
                    }else $skip++;
                    echo "\nNot found {$mode} ({$heroId}{$skin}) in all server !";
                }
                $arr[$mode]=$checked;
                file_put_contents('./data/'.$heroId.'/list.json',json_encode($arr));
               /*  system("cls");
                system("clear"); */
               // echo "\nFound: ".$new;
            }
        /* system("cls");
        system("clear");  */
        echo "\nFound: ".$new;
        echo "\nCheck again after 10 sec...";
        sleep(10);
    }
}

