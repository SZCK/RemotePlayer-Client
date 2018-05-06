<?php
    namespace RemotePlayer;
    
    require_once "vendor/autoload.php";
    
    const PIN = [
        "button" => 26,
        "LED" => 13,
        "sensor" => [
                "man" => 17,
            ]
    ];
    
    use \Calcinai\PHPi as Pi;
    use \Calcinai\PHPi\Pin\PinFunction;
    use \Calcinai\PHPi\Pin;
    use Calcinai\PHPi\External\Generic\LED;
    use Calcinai\PHPi\External\Generic\Button;
    
    $queue = new \SplQueue();
    
    $board = Pi\Factory::create();
    
    $led = new LED($board->getPin(PIN['LED']));

//$led2 = new LED($board->getPin(24));
//$led2->on();

    // $led->flash(3, 1, 0.5);
    
    $loop = $board->getLoop();
    
    $loop->addPeriodicTimer(5, function () use($queue, $board, $loop, $led) {
        if(!$queue->isEmpty()){
            $led->flash(2, 0.5, 0.5);
        }
    });
    
    $button = new Button($board->getPin(PIN['button']));
    $button->on("press", function() use ($queue, $led){
        if(!$queue->isEmpty()){
            playSound($queue->pop());
        }else{
            $led->flash(1, 0.5, 1);
        }
    });
    
    $connector = new \React\Socket\Connector($loop);
    
    $connector->connect("unix:///home/pi/RemotePlayer/ws.sock")->then(function($conn) use ($board, $loop, $queue){
        $conn->on("data", function($data) use ($board, $loop, $queue){
            echo "RECV: ".$data.PHP_EOL;
            $data = json_decode($data, 1);
            switch($data["type"]){
                case "sendVoice":
                    $voice = base64_decode($data['voice']);
                    
                    $autoplay = $data['autoplay'];
                    
                    if($autoplay && checkSensorMan($board->getPin(PIN['sensor']['man']))){
                        playSound($voice);
                    }else{
                        $queue->push($voice);
                    }
                default:
                    return;
            }
        });
    });
    
    function checkSensorMan($pin){
        $pin->setFunction(PinFunction::INPUT);
        if($pin->getLevel() == Pin::LEVEL_HIGH){
            return true;
        }else{
            return false;
        }
    }
    
    function playSound($sound){
        global $led;
        $led->on();
        file_put_contents("./temp.sound.mp3", $sound);
        system("mplayer ./temp.sound.mp3");
        echo "played sound\r\n";
        $led->off();
    }
    
    $board->getLoop()->run();

//$led2->off();
