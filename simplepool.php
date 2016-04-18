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
    public static $app;

    public function run()
    {
        require __DIR__ . '/../bootstrap/autoload.php';
        self::$app =  require_once __DIR__ . '/../bootstrap/app.php';
        /** @var \Illuminate\Foundation\Application $app */
        $app = self::$app;

        /** @var Illuminate\Contracts\Console\Kernel::class $kernel */
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        $commandBus = $app->make(\SmoothPhp\Contracts\CommandBus\CommandBus::class);
    }
    public function getApp() { return self::$app; }

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

