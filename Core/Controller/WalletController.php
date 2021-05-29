<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 10.09.18
 * Time: 18:30
 */

namespace Core\Controller;

use Core\Model\Wallet;
use Core\Model\WalletHistory;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class WalletController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getWallet(Request $request){
        $wallet = Wallet::getUserWallet();
        $walletHistory = WalletHistory::where("wallet_id","=",$wallet->id)->orderBy('date',true)->get();

        Response::json([
            "status" => $wallet->status,
            "history" => $walletHistory
        ],200);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("amount")->length(1,11);

        $wallet = Wallet::getUserWallet();
        $wallet->addAmount($request->get("amount"));

        $walletHistory = $wallet->getHistory();

        Response::json([
            "status" => $wallet->status,
            "history" => $walletHistory
        ],200);
    }
}