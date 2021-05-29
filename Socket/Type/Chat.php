<?php

namespace Socket\Type;

use Core\Model\Message;
use Core\Model\User;
use DateTime;
use Core\System\Database;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Socket\Models\UserModel;

class Chat implements MessageComponentInterface {

	/** @var array */
	protected $clients;

	/** @var DateTime */
	protected $lastUpdateTime;

	public function __construct() {
		$this->clients = [];
		$this->lastUpdateTime = new DateTime('now');
	}

	public function onOpen(ConnectionInterface $conn) {
		$user = new UserModel();
		$user->client = $conn;
		$this->clients[] = $user;
    }

	public function onMessage(ConnectionInterface $from, $msg) {
		$this->update();

		if(strlen($msg)>4 && substr($msg,0,4) == 'init' ){
			$this->initializeUser($from,$msg);
		}else{
			$msg = str_replace("to:","",$msg);

			$to = intval(explode("---",$msg)[0]);
			$message = explode("---",$msg)[1];
			$user = $this->getUser($from);

			if(strlen($message)>0 && isset($user)){
                $messageElement = Message::create([
                    "user_from" => (int)$user->id,
                    "user_to" => (int)$to,
                    "date" => (new DateTime())->format('Y-m-d H:i:s'),
                    "isRead" => 0,
                    "message" => $message
                ]);

                if(isset($user) ){
                    foreach ($this->clients as $client) {
                        if ($client->user->id == $to || $client->user->id == $user->id) {
                            echo "Wysłano '{$message}' do {$client->user->firstname}\n";
                            $msg = json_encode(array(
                                "user"=> $this->getUser($from),
                                "msg" => $messageElement
                            ));
                            $client->client->send($msg);
                        }
                    }
                }
            }

			unset($messageElement);
		}
	}

	public function onClose(ConnectionInterface $conn) {
		foreach($this->clients as $key => $client){
			if($client->client->resourceId === $conn->resourceId){
				echo "{$client->user->firstname} odłączony.\n";
				array_splice($this->clients, $key, 1);
				break;
			}
		}
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";
		$conn->close();
	}

    private function update(){
        $now = new DateTime('now');
        $now->modify('-2 hour');
        if( $this->lastUpdateTime <= $now ){
            $this->lastUpdateTime = new DateTime('now');
            Database::reInitConnection();
        }
    }

    private function initializeUser($conn, $msg){
        $msg = str_replace("init:","",$msg);

        $id = intval(explode("---",$msg)[1]);
        $login_token = explode("---",$msg)[0];

        foreach($this->clients as $key => $client){
            if($client->client->resourceId === $conn->resourceId){
                $this->clients[$key]->user = User::find($id);
                if( isset($this->clients[$key]->user) && $this->clients[$key]->user->login_token == $login_token){
                    echo "{$client->user->firstname} dodany.\n";
                }

                break;
            }
        }
    }

    private function getUser($conn){
        foreach($this->clients as $key => $client){
            if($client->client->resourceId === $conn->resourceId){
                return $this->clients[$key]->user;
            }
        }
        return null;
    }
}