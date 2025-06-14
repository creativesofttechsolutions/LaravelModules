<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMakeRule extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:make-rule 
                        {module : The name of the module} 
                        {name : The name of the rule}';

    protected $description = 'Create a new custom validation rule class in a specific module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = Str::studly($this->argument('name'));

        $filesystem = new Filesystem;
        $rulePath = config('modules.path') . "/{$module}/Rules/{$name}.php";

        // Check if the module exists
        if (! $filesystem->exists(config('modules.path') . "/{$module}")) {
            $this->error("Module {$module} does not exist.");

            return 1;
        }

        // Check if the rule already exists
        if ($filesystem->exists($rulePath)) {
            $this->error("Rule {$name} already exists in module {$module}.");

            return 1;
        }

        // Create the rule
        $namespace = "Modules\\{$module}\\Rules";
        $ruleContent = <<<EOT
<?php

namespace {$namespace};

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class {$name} implements ValidationRule
{
    public function validate(string \$attribute, mixed \$value, Closure \$fail): void
    {
        // TODO: Add validation logic here
    }
}
EOT;

        $filesystem->ensureDirectoryExists(dirname($rulePath));
        $filesystem->put($rulePath, $ruleContent);

        $this->info("✅ Rule {$name} created successfully in module {$module}.");
        $this->line("📄 File Path: {$rulePath}");

        return 0;
    }
}
