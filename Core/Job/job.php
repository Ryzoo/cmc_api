<?php

use Core\Model\Job;

require_once __DIR__.'/../../vendor/autoload.php';

$jobsArray = Job::where("is_run" ,"=","TRUE")->get();

$jobs = [];

foreach($jobsArray as $job){
    $jobName = "Core\\Job\\" . $job->get("class");
    $name = $job->get("name");
    $date = $job->get("date");
    $jobs[] = new  $jobName($name, $date);
}

$jobby = new Jobby\Jobby();

foreach ($jobs as $job) {
    echo "Start closure for " . $job->getName();
    $job->closure();
    $jobby->add($job->getName(), [
        'closure'  => function() use ($job){
            $job->closure();
            return true;
        },
        'debug' => true,
        'enabled' => true,
        'schedule' => $job->getDate(),
        'output' => 'logs/command.log',
    ]);
}

$jobby->run();