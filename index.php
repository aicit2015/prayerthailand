<?php
require_once('./vendor/autoload.php');
//Namespace
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \GeniusTS\PrayerTimes\Prayer;
use \GeniusTS\PrayerTimes\Coordinates;

//$result = $connection->query("SELECT follow FROM count_follow_unfollow WHERE id=1");


    //$prayer = new Prayer(new Coordinates($longitude, $latitude));
    // Or
    //$prayer = new Prayer();
    
    
    /*
    $prayer->setCoordinates(100.4786648,6.994023);
    
    // Return an \GeniusTS\PrayerTimes\Times instance
    $times = $prayer->times('2019-8-9');
    $times->setTimeZone(+7);
    echo $times->fajr->format('h:i a');
    echo $times->sunrise->format('h:i a');
    echo $times->duhr->format('h:i a');
    echo $times->asr->format('h:i a');
    echo $times->maghrib->format('h:i a');
    echo $times->isha->format('h:i a');
    */

    $host = 'ec2-174-129-226-234.compute-1.amazonaws.com';
    $dbname = 'dbq16h95vt7ppb';
    $user = 'hzuzvppvlsptcc';
    $pass = '485c7881706ac7b7d3b10402147ad817ba37b262123a5427709c627ed0451d3c';
    $connection = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);

$channel_token ='n3ffnYJ+TbdquJCP2PT/QB2AQ+roUjUrmnpMiPuLcxAMWmUqqiwt+ySwpE8Nf7hv9ntTrhp+J7o2rIApPZsmSL8I0QLWwb0qm69U544aCB1Un5ikx6oXy82au7/+hkaUH2lpI57G8hmAYKjhvAlOlQdB04t89/1O/w1cDnyilFU=';
$channel_secret = 'edae9c39b22249b067cd729a0ac5ee40';
//Get message from Line API
$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (!is_null($events['events'])) {
//Loop through each event
foreach ($events['events'] as $event) {
//Line API send a lot of event type, we interested in message only.
    if ($event['type'] == 'unfollow') {
        $params = array(
            'id' => 1,
            );
        $statement = $connection->prepare('UPDATE count_follow_unfollow SET unfollow = (SELECT max(unfollow) + 1  FROM count_follow_unfollow WHERE id=1) WHERE id=:id');
        $statement->execute($params);
    
    }

    if ($event['type'] == 'follow') {

        $statement = $connection->prepare('UPDATE count_follow_unfollow SET follow +=1 WHERE  id=1');
        $statement->execute($params);
    //    Get replyToken
        $replyToken = $event['replyToken'];
    //    Greeting
        $respMessage = 'ขอบคุณที่แอดเราเป็นเพื่อน บริการสอบถามเวลาละหมาด เพียงแค่กดส่งโลเคชั่นมาหาเรา';
        $httpClient = new CurlHTTPClient($channel_token);
        $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));
        $textMessageBuilder = new TextMessageBuilder($respMessage);
        $response = $bot->replyMessage($replyToken, $textMessageBuilder);

        $params = array(
            'id' => 1,
            );
        $statement = $connection->prepare('UPDATE count_follow_unfollow SET follow = (SELECT max(follow) + 1  FROM count_follow_unfollow WHERE id=1) WHERE id=:id');
        $statement->execute($params);
        
        }


    if ($event['type'] == 'message') {
            //            Get replyToken

            //-----------------------------insert user to db
            $sql = sprintf("SELECT * FROM user_friend WHERE user_id='%s' ", $event['source']['userId']);
            $result = $connection->query($sql);
            error_log($sql);
            if($result == false || $result->rowCount() <=0) {
                 $params = array(
                'userID' => $event['source']['userId']
                );
                $statement = $connection->prepare('INSERT INTO user_friend ( user_id ) VALUES ( :userID )');
                $statement->execute($params);
            }

        $replyToken = $event['replyToken'];
            switch($event['message']['type']) {
                case 'location':
                    $address = $event['message']['address'];
                    $title = $event['message']['title'];
                     //Reply message
                    $respMessage = $title. '  ' . $address . "\n" ;
                    
                    $prayer = new Prayer();
                    $prayer->setMethod(Prayer::METHOD_MUSLIM_WORLD_LEAGUE);
                    $prayer->setCoordinates($event['message']['longitude'],$event['message']['latitude']);
                    
                    // Return an \GeniusTS\PrayerTimes\Times instance   DateTime())->format('Y-m-d H:i:s');
                    
                    


                    //-------------------------------------------------------------------------------------------------
                   

              
                    
                    $times = $prayer->times(date("Y-m-d"));
                    $times->setTimeZone(+7);
                       
                        $respMessage  .=  '---------------------' . "\n".
                                          'วันที่ ' . date("d-m-Y"). "\n".
                                          '---------------------' . "\n".
                                          'ฟัจรฺ          : ' . $times->fajr->format('h:i a') ."\n". 
                                          'อาทิตย์ขึ้น : ' .  $times->sunrise->format('h:i a') . "\n" . 
                                          'ซุฮฺริ          : ' . $times->duhr->format('h:i a') . "\n".
                                          'อัสริ          : ' .  $times->asr->format('h:i a') . "\n".
                                          'มัฆริบ       : ' . $times->maghrib->format('h:i a') ."\n".
                                          'อีชา          : ' .  $times->isha->format('h:i a')."\n".
                                          '---------------------' . "\n".
                                          'METHOD_MUSLIM_WORLD_LEAGUE ';

                break;
                default:
            //Reply message
                    $respMessage = 'Please send location only';
                break;
            }
            

            

            

        $httpClient = new CurlHTTPClient($channel_token);
        $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));
        $textMessageBuilder = new TextMessageBuilder($respMessage);
        $response = $bot->replyMessage($replyToken, $textMessageBuilder);
    }

    /*

    //$prayer = new Prayer(new Coordinates($longitude, $latitude));
    // Or
    $prayer = new Prayer();
    $prayer->setCoordinates($longitude, $latitude);

    // Return an \GeniusTS\PrayerTimes\Times instance
    $times = $prayer->times('2017-5-9');
    $times->setTimeZone(+3);
    //echo $times->fajr->format('h:i a');
    */


/*

if ($event['type'] == 'message') {
        // Get replyToken
        $replyToken = $event['replyToken'];
        $ask = $event['message']['text'];

        switch(strtolower($ask)) {
            case 'm':
            $respMessage = 'What sup man. Go away!';
            break;
            case 'f':
            $respMessage = 'Love you lady.';
            break;
            default:
            $respMessage = 'What is your sex? M or F';
            break;
        }
        
        
      */  
        
        
        
        
        
        
        //---------------------------`[บ้านๆ ]
        /*
        
        switch($event['message']['type']) {
            case 'location':
            $address = $event['message']['address'];
            //            Reply message
            $respMessage = 'Hello, your address is '. $address;
            break;
            case 'audio':
            $messageID = $event['message']['id'];
            //            Create audio file on server.
            $fileID = $event['message']['id'];
            $response = $bot->getMessageContent($fileID);
            $fileName = 'linebot.m4a';
            $file = fopen($fileName, 'w');
            fwrite($file, $response->getRawBody());
            //            Reply message
            $respMessage = 'Hello, your audio ID is '. $messageID;
            break;
            case 'video':
            $messageID = $event['message']['id'];
            //Create video file on server.
            $fileID = $event['message']['id'];
            $response = $bot->getMessageContent($fileID);
            $fileName = 'linebot.mp4';
            $file = fopen($fileName, 'w');
            fwrite($file, $response->getRawBody());
            //Reply message
            $respMessage = 'Hello, your video ID is '. $messageID;
            break;
            case 'sticker':
            $messageID = $event['message']['packageId'];
            //Reply message
            $respMessage = 'Hello, your Sticker Package ID is '. $messageID;
            break;
            case 'text':
                // Reply message
                $respMessage = 'Hello, your message is '. $event['message']['text'];
            break;
            case 'image':
                $messageID = $event['message']['id'];
                $respMessage = 'Hello, your image ID is '. $messageID;
            break;
            default:
                $respMessage = 'Please send text or image or Sticker';
            break;
            }
            */
            /*
            $httpClient = new CurlHTTPClient($channel_token);
            $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));
            $textMessageBuilder = new TextMessageBuilder($respMessage);
            $response = $bot->replyMessage($replyToken, $textMessageBuilder);

            */
        //}
    }
}
echo 'OK';
echo 'HELLO AIC BOT';

