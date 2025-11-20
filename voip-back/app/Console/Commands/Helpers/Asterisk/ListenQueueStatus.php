<?php

namespace App\Console\Commands\Helpers\Asterisk;

use App\IPTelephony\Services\Client\Asterisk\AmiService;
use Illuminate\Console\Command;

class ListenQueueStatus extends Command
{
    protected $signature = 'asterisk:listen_queue_status';

    protected string $host;
    protected string $port;
    protected string $username;
    protected string $secret;

    public function __construct()
    {
        parent::__construct();

        $this->host = '65.108.252.230';
        $this->port = '8089';
        $this->username = 'ovpastman';
        $this->secret = 'uLfuwePuFCesmDsPe2F5xsIV';
    }

    public function handle()
    {
        $socket = fsockopen(
            $this->host,
            $this->port,
            $ac_err_num,
            $ac_err_msg,
            20
        );

        if ($socket){
            $this->info("AMI connection: success");
            logger_info("AMI connection: success");
        } else{
            $this->info("AMI connection: failed");
            logger_info("AMI connection: failed", [
                'Error number' => $ac_err_num,
                'Error notice' => $ac_err_msg,
            ]);
        }

        fputs($socket, "Action: Login" .  PHP_EOL);
        fputs($socket, "Username: ". $this->username . PHP_EOL);
        fputs($socket, "Secret: ". $this->secret . PHP_EOL);

        while (!feof($socket)){
            $m = [];
            $mm = [];
            $res = [];
            $content= '';
            $j = 0;

            $content .= fread($socket,1024);
            $this->info($content);
            $this->info(json_encode(socket_get_status($socket)));

            $mm = explode ("\r\n", $content);

            for($i = 0; $i < count($mm); $i++){

                $m = explode(':' , $mm[$i]);

                if (isset($m[1])){

                    if(trim($m[0]) == 'Event'){
                        $j++;
                    }
                    $res[$j][trim($m[0])] = trim($m[1]);
                }

            }

            foreach($res as $massiv){
                switch ($massiv['Event']) {

                    case 'PeerStatus':

//                        peers_status_update($massiv['PeerStatus'],$massiv['Peer']);

                        echo $massiv['PeerStatus'];

                        break;

                }

                print_r($massiv);

            }

            sleep(0.005);
        }

    }
}


