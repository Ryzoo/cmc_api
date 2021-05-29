<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 09.07.18
 * Time: 20:15
 */

namespace Core\System;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailSender
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $config = Config::config("mail");
        try {
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            $this->mailer->isSMTP();                                      // Set mailer to use SMTP
            $this->mailer->Host = $config["host"];  // Specify main and backup SMTP servers
            $this->mailer->SMTPAuth = true;                               // Enable SMTP authentication
            $this->mailer->Username = $config["email"];                 // SMTP username
            $this->mailer->Password = $config["password"];                           // SMTP password
            $this->mailer->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $this->mailer->Port = 587;                                    // TCP port to connect to

            $this->mailer->setFrom($config["email"], $config["name"]);
            $this->mailer->isHTML(true);

        } catch (Exception $e) {
            Response::error($e,500);
        }
    }

    public function sendEmail($toEmail, $toName='',string $subject,array $param, string $themeName = 'base'):bool {

        $this->mailer->addAddress($toEmail, $toName);
        $this->mailer->Subject = $subject;
        $this->mailer->Body =$this->getThemeBody($themeName, $param);

        try{
            $this->mailer->send();
        }catch(Exception $e){
            Response::error($e,500);
        }

        $this->reset();
        return true;
    }

    public function getThemeBody(string $name,array $params){
        $dir = __DIR__ ."/../../resources/EmailTemplate/".$name.".html";
        $theme = file_get_contents($dir);

        foreach ( $params as $param){
            $theme = str_replace('{'.$param["name"].'}', $param["content"], $theme);
        }

        return $theme;
    }

    public function reset(){
        $this->mailer->clearAllRecipients();
    }
}