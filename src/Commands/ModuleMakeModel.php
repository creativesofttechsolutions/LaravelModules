<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMakeModel extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:make-model 
                        {module : The name of the module} 
                        {name : The name of the model} 
                        {--m|migration : Create a migration for the model}';

    protected $description = 'Create a new model for a specific module, optionally with a migration';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));

        $modulePath = config('modules.path') . "/{$module}";

        if (! is_dir($modulePath)) {
            $this->error("❌ Module '{$module}' does not exist.");

            return Command::FAILURE;
        }

        $filesystem = new Filesystem;
        $modelPath = config('modules.path') . "/{$module}/Models/{$name}.php";

        // Check if the module exists
        if (! $filesystem->exists($modulePath)) {
            $this->error("Module {$module} does not exist.");

            return 1;
        }

        // Check if the model already exists
        if ($filesystem->exists($modelPath)) {
            $this->error("Model {$name} already exists in module {$module}.");

            return 1;
        }

        // Create the model
        $namespace = "Modules\\{$module}\\Models";
        $modelContent = <<<EOT
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$fillable = [];
}
EOT;

        $filesystem->ensureDirectoryExists(dirname($modelPath));
        $filesystem->put($modelPath, $modelContent);

        $this->info("✅ Model {$name} created successfully in module {$module}.");
        $this->line("📄 File Path: {$modelPath}");
        // Check if the migration flag (-m) is provided
        if ($this->option('migration')) {
            $this->call('module:make-migration', [
                'module' => $module,
                'name' => 'create_'.Str::snake($name).'_table',
            ]);
        }

        return 0;
    }
}
