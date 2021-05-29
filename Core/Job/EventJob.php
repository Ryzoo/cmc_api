<?php
// * * * * * cd path/to/Job && php job.php 1>> /dev/null 2>&1

namespace Core\Job;

use Core\Model\Notification;
use Core\System\Contract\BaseJob;

class EventJob extends BaseJob
{
    public function __construct(string $name, string $date)
    {
        parent::__construct($name, $date);
    }

    public function closure()
    {
        Notification::create([
            "title" => "Test",
            "content" => "Test",
            "icon" => "",
            "url" => "",
            "isRead" => false,
            "date" => (new \DateTime())->format('Y-m-d H:i:s'),
            "user_id" => 2
        ]);
    }
}