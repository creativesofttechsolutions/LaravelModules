<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;

class ModuleMakeMiddleware extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:make-middleware {module : The name of the module} {name : The name of the middleware}';

    protected $description = 'Create a new middleware for a specific module';

    public function handle(): int
    {
        $filesystem = new Filesystem;

        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));

        // Ensure it ends with 'Middleware'
        if (! str_ends_with($name, 'Middleware')) {
            $name .= 'Middleware';
        }

        $modulePath = config('modules.path') . "/{$module}";
        $middlewarePath = "{$modulePath}/Middleware";
        $filePath = "{$middlewarePath}/{$name}.php";

        // ✅ Check if the module exists
        if (! $filesystem->isDirectory($modulePath)) {
            $this->error("❌ Module '{$module}' does not exist.");

            return Command::FAILURE;
        }

        // ✅ Create Middleware directory if not exists
        if (! $filesystem->isDirectory($middlewarePath)) {
            $filesystem->makeDirectory($middlewarePath, 0755, true);
        }

        if ($filesystem->exists($filePath)) {
            $this->error("❌ Middleware '{$name}' already exists in module '{$module}'.");

            return Command::FAILURE;
        }

        $namespace = "Modules\\{$module}\\Middleware";

        $stub = <<<PHP
<?php

namespace {$namespace};

use Closure;
use Illuminate\Http\Request;

class {$name}
{
    public function handle(Request \$request, Closure \$next)
    {
        // TODO: Implement middleware logic
        return \$next(\$request);
    }
}
PHP;

        $filesystem->put($filePath, $stub);

        $this->info("✅ Middleware '{$name}' created successfully in module '{$module}'.");
        $this->line("📂 Path: {$filePath}");

        return Command::SUCCESS;
    }
}
