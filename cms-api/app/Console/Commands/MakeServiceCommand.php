<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

   public function handle()
{
    $name = $this->argument('name');
    $filesystem = new Filesystem();
    
    // Ensure Services directory exists
    if (!is_dir(app_path('Services'))) {
        $filesystem->makeDirectory(app_path('Services'));
    }
    
    $servicePath = app_path('Services/'.$name.'.php');
    
    // Check if service already exists
    if ($filesystem->exists($servicePath)) {
        $this->error('Service already exists!');
        return;
    }
    
    // Default content if no stub exists
    $content = <<<EOD
<?php

namespace App\Services;

class {$name}
{
    public function __construct()
    {
        // Constructor logic
    }
    
    // Add your service methods here
}
EOD;
    
    $filesystem->put($servicePath, $content);
    
    $this->info("Service [app/Services/{$name}.php] created successfully.");
}
}