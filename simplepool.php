<?php

class DeployPool extends Pool
{
    public function __construct($size)
    {
        parent::__construct($size, DeployWorker::class, []);
    }

    public function submitJob(DeployJob $deployJob)
    {
        parent::submit(new DeployRunner($deployJob));
    }
}

class DeployWorker extends Worker
{
    public function run()
    {

    }
}

class DeployRunner extends Collectable
{
    /** @var DeployJob */
    private $deployJob;

    /**
     * DeployRunner constructor.
     * @param DeployJob $
     */
    public function __construct(DeployJob $deployJob)
    {
        $this->deployJob = $deployJob;
    }

    public function run()
    {
        echo "Hello World\n";
        $sleep = rand(1, 3);

        echo "Sleeping for {$sleep}" . PHP_EOL;
        time_nanosleep($sleep, 0);
        echo "Bye World\n";

        $this->setGarbage();

    }
}

class DeployJob
{
    private $message;

    /**
     * DeployJob constructor.
     * @param $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage() : string
    {
        return $this->message;
    }

}

$pool = new DeployPool(4);

for ($i = 0; $i < 8; $i++) {
    $pool->submitJob(new DeployJob('Boo'));

}

while ($pool->collect(
    function (Collectable $task) {
        return $task->isGarbage();
    }
)) {
    continue;
}


$pool->shutdown();

