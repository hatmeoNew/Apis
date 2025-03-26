<?php
namespace NexaMerchant\Apis\Console\Commands;
use Illuminate\Support\Facades\Artisan;

use NexaMerchant\Apps\Console\Commands\CommandInterface;

class Version extends CommandInterface 

{
    protected $signature = 'Apis:Version';

    protected $description = 'Version of Apis';

    public function getAppVer() {
        return config("Apis.version");
    }

    public function getAppName() {
        return config("Apis.name");
    }

    public function handle()
    {
        // read the version from the config file
        $this->info("Version: " . $this->getAppVer());
    }
}