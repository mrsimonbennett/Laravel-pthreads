<?php declare(strict_types = 1);
namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class SimplePool
 * @package App\Console\Commands
 * @author Simon Bennett <simon@bennett.im>
 */
final class SimplePool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simplepool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $pool = new DeployPool(1);
        $pool->submitJob(new DeployJob('Boo'));


    }
}
class DeployPool extends \Pool
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

class DeployWorker extends \Worker
{
    public static $app;

    public function run()
    {
        require __DIR__ . '/../bootstrap/autoload.php';
        self::$app =  require_once __DIR__ . '/../bootstrap/app.php';
        /** @var \Illuminate\Foundation\Application $app */
        $app = self::$app;

        /** @var Illuminate\Contracts\Console\Kernel::class $kernel */
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
    public function getApp() { return self::$app; }

}

class DeployRunner extends \Collectable
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
        $sleep = rand(1, 5);

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
