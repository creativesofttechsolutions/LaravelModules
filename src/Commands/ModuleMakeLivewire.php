<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMakeLivewire extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:make-livewire {module : The name of the module} {name : The name of the livewire component}';

    protected $description = 'Create a new livewire component for a specific module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));

        $moduleName = strtolower(Str::studly($module)); // StudlyCase
        $lowerComponentName = Str::kebab($name); // kebab-case

        $path = config('modules.path') . "/{$module}" . "/Livewire/{$name}.php";

        $filesystem = new Filesystem;

        if (! $filesystem->exists(config('modules.path') . "/{$module}")) {
            $this->error("Module {$module} does not exist.");

            return 1;
        }

        if ($filesystem->exists($path)) {
            $this->error("Component {$name} already exists in module {$module}.");

            return 1;
        }

        $namespace = "Modules\\{$module}\\Livewire";

        $content = <<<EOT
<?php

namespace {$namespace};

use App\Livewire\BaseLivewireComponent;

class {$name} extends BaseLivewireComponent
{

    public function mount()
    {
        parent::base_mount();
    }

    public function render()
    {

        parent::setViewPath();
        
        \$viewPath = "livewire.{$lowerComponentName}";
        if (!view()->exists(\$viewPath)) {
            // Fallback to default view if theme-specific view doesn't exist
            \$viewPath = '{$moduleName}::livewire.{$lowerComponentName}';
        }
        return view(\$viewPath);
    }
}

EOT;

        $filesystem->put($path, $content);

        $this->info("Livewire Component {$name} created successfully in module {$module}.");
        $this->line("File Path: {$path}");

        $this->createViewFile($module, $name, $lowerComponentName);

        return 0;
    }

    protected function createViewFile($moduleName, $name, $lowerComponentName)
    {
        $basePath = base_path("modules/{$moduleName}");

        // Ensure views directory exists
        $viewsPath = "{$basePath}/Resources/views/livewire";
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }

        $content = <<<EOT

<div>
{$name} Livewire Component
</div>

EOT;

        $path = "{$viewsPath}/{$lowerComponentName}.blade.php";
        file_put_contents($path, $content);

        $this->info("Livewire Component view {$lowerComponentName} created successfully in module {$moduleName}.");
        $this->line("File Path: {$path}");
    }
}
