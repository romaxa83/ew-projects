<?php

namespace App\IPTelephony\Services\Client\Asterisk;

class AmiSocketClient
{
    public array $ini = [
        'con' => false,
        'lastActionID' => 0,
        'lastRead' => [],
        'sleepTime' => 200000 // microsecond
    ];

    function __construct (
        string $host,
        string $port,
        string $username,
        string $secret,
    )
    {
        $this->ini["host"] = $host;
        $this->ini["port"] = $port;
        $this->ini["username"] = $username;
        $this->ini["secret"] = $secret;
    }

    function __destruct()
    {
        unset ($this->ini);
    }

    public function connect()
    {
        $this->ini["con"] = fsockopen(
            $this->ini["host"],
            $this->ini["port"],
            $ac_err_num,
            $ac_err_msg,
            20
        );

        if ($this->ini["con"]){
            stream_set_timeout($this->ini["con"], 0, 400000);
            logger_info("AMI connection: success");
       } else{
            logger_info("AMI connection: failed", [
                'Error number' => $ac_err_num,
                'Error notice' => $ac_err_msg,
            ]);
        }
    }

    public function disconnect()
    {
        if ($this->ini["con"]) {
            fclose($this->ini["con"]);
            logger_info("AMI connection: close");
        }
    }

    public function write(string $a)
    {
        $this->ini["lastActionID"] = rand(10000000,99999999);
        fwrite($this->ini["con"], "ActionID: ".$this->ini["lastActionID"]."\r\n$a\r\n\r\n");

        $this->sleepi();

        return $this->ini["lastActionID"];
    }

    public function sleepi ()
    {
        usleep($this->ini["sleepTime"]);
    }

    public function read(): array
    {
        $s = "";

        do {
            $s.= fread($this->ini["con"],1024);
//            sleep(0.00005);
            $statuses = socket_get_status($this->ini["con"]);
        } while ($statuses['unread_bytes']);

        return $this->ini["lastRead"] = $this->convertToArr($s);
    }

    public function listen()
    {
        while (!feof($this->ini["con"])){

            logger_info('LISTEN');
            $s = '';
            do {
                $s.= fread($this->ini["con"],1024);
                sleep(0.00005);
                $statuses = socket_get_status($this->ini["con"]);

                logger_info(json_encode($statuses));


            } while ($statuses['unread_bytes']);

        }

        logger_info('LISTEN END');
    }

    public function convertToArr(string $data): array
    {
        $tmp = [];
        $items = explode("\r\n", $data);

        $count = 0;
        for ($i = 0; $i < count($items); $i++) {
            if ($items[$i]=="") {
                $count++;
            }
            $m = explode(":", $items[$i]);
            if (isset($m[1])) {
                $tmp[$count][trim($m[0])] = trim($m[1]);
            }
        }

        return $tmp;
    }

    public function auth()
    {
        $auth  = "Action: Login" . PHP_EOL;
        $auth .= "Username: " . $this->ini["username"] . PHP_EOL;
        $auth .= "Secret: ". $this->ini["secret"] . PHP_EOL;
        $auth .= "Events: off" . PHP_EOL;

        return $this->write($auth);
    }
}
