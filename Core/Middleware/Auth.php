<?php
namespace Core\Middleware;

use Core\Model\User;
use Core\System\Contract\IMiddleware;
use Core\System\Request;

class Auth implements IMiddleware {

    public static function check(Request $request):bool{
        $tqn = $request->get("login_token");

        if(!$tqn){
            $tqn = $request->get("login_token",true);
        }

        if(!$tqn){
            $tqn = Auth::getBearerToken();
        }

        if(!$tqn) return false;

        $currentUser = User::where("login_token","LIKE",$tqn)->get();
        global $user;
        $user = isset($currentUser[0]) ? $currentUser[0] : null;

        return (($currentUser[0] ?? NULL) != NULL);
    }

    /**
     * Get header Authorization
     * */
    private static function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    private static function getBearerToken() {
        $headers = Auth::getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}