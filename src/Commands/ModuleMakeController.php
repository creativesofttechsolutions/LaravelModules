<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;

class ModuleMakeController extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:make-controller {module : The name of the module} {name : The name of the controller}';

    protected $description = 'Create a new controller for a specific module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));

        // Ensure the name ends with "Controller"
        if (! str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $path = config('modules.path') . "/{$module}" . "/Controllers/{$name}.php";

        $filesystem = new Filesystem;

        if (! $filesystem->exists(base_path("modules/{$module}"))) {
            $this->error("Module {$module} does not exist.");

            return 1;
        }

        if ($filesystem->exists($path)) {
            $this->error("Controller {$name} already exists in module {$module}.");

            return 1;
        }

        $namespace = "Modules\\{$module}\\Controllers";

        $content = <<<EOT
<?php

namespace {$namespace};

use App\Http\Controllers\Controller;

class {$name} extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Welcome to the {$name} of {$module} module.']);
    }
}
EOT;

        $filesystem->put($path, $content);

        $this->info("Controller {$name} created successfully in module {$module}.");
        $this->line("File Path: {$path}");

        return 0;
    }
}
