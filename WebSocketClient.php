<?php
    namespace RemotePlayer;
    
    require_once "vendor/autoload.php";
    
    const SERVER = "ws://us-srv-1.xtlsoft.top:30991";
    const DEVICE = 10001;
    const SECRET = ".PK0Ww2JTQE2.SdCbZOgUP5\$dYYDpbjpRU+w.05SysCx_G0&owb8&63/NJB2LK.ln+HmYv4fOpDGVlB9IC0CrLB4d2PPuP1IMrUtNGyjW1Pz$.rDB.6TLpFP.cnuR=2mnFF0-49QOH/JHp\$9p1K1.+y.aLtSIe5WJsFDfwbT4.lupglF7Vo6TM5TghuOmhsWAXiFckh7ksjzyu4o+acRN8sT/NqkUJ7dp+JshQhr9rRpMEDKFGjbwCVu+-FSDuVS";
    
    $worker = new \Workerman\Worker("unix:///home/pi/RemotePlayer/ws.sock");
    
    $worker->count = 1;
    $worker->onConnect = function($worker){
        
        $conn = new \Workerman\Connection\AsyncTcpConnection(SERVER);
        
        $conn->onMessage = function($conn, $msg) use ($worker){
            $worker->send($msg);
        };
        
        $conn->send(json_encode(["type"=>"register", "device"=>DEVICE, "secret"=>SECRET, "id"=>1]));
        $conn->connect();
        
        $worker->conn = $conn;
        
    };
    
    $worker->onMessage = function($worker, $msg){
        $worker->conn->send($msg);
    };
    $worker->onClose = function($worker){
        $worker->conn->close();
    };
    
    \Workerman\Worker::runAll();