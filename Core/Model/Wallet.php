<?php

namespace Core\Model;

use Core\System\BasicModel;
use Core\System\Response;

class Wallet extends BasicModel
{

    public static $table = "Wallet";
    public static $fields = ["id", "user_id", "status"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "float NOT NULL DEFAULT '0'",
    ];
    public static $relations = [];

    public $id;
    public $user_id;
    public $status;

    public static function getUserWallet(?User $user = null):Wallet
    {
        if (!$user) {
            $user = $GLOBALS['user'];
        }

        $wallet = Wallet::where("user_id", "=", $user->get("id"))->get();
        if (isset($wallet[0])) return $wallet[0];
        else Response::error("Nie można odnaleźć portfela. Działania przerwane!", 401);
    }

    public function addAmount($amount){
        $this->update([
            "status" => (float)$this->status + (float)$amount
        ]);

        WalletHistory::create([
            "wallet_id" => $this->id,
            "status" => $this->status,
            "date" => (new \DateTime())->format("Y-m-d H:i:s"),
            "action" => "Doładowanie portfela kwotą: " . $amount . "zł - " . (new \DateTime())->format("Y-m-d H:i:s")
        ]);
    }

    public function removeAmount($amount,$title){

        if((float) $this->status < (float)$amount){
            Response::error('Nie posiadasz odpowiedniej ilości środków na koncie');
        }

        $this->update([
            "status" => (float)$this->status - (float)$amount
        ]);

        WalletHistory::create([
            "wallet_id" => $this->id,
            "status" => $this->status,
            "date" => (new \DateTime())->format("Y-m-d H:i:s"),
            "action" => $title
        ]);
    }

    public function haveEnought(float $amount): bool
    {
        return ((float)$this->status) >= $amount;
    }

    public function getHistory(){
        return WalletHistory::where("wallet_id","=",$this->id)->orderBy("date",true)->get();
    }

}