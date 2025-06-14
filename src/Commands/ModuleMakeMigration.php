<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;

class ModuleMakeMigration extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:make-migration {module : The name of the module} {name : The name of the migration}';

    protected $description = 'Create a new migration for a specific module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $rawName = $this->argument('name');

        $modulePath = config('modules.path') . "/{$module}";

        if (! is_dir($modulePath)) {
            $this->error("❌ Module '{$module}' does not exist.");

            return Command::FAILURE;
        }

        $migrationClassName = Str::studly($rawName);
        $tableName = $this->guessTableName($rawName);

        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_".Str::snake($rawName).'.php';

        $path = config('modules.path') . "/{$module}/Migrations/{$filename}";

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $content = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
EOT;

        file_put_contents($path, $content);

        $this->info("✅ Migration {$migrationClassName} created successfully in module {$module}.");
        $this->line("📄 File Path: {$path}");

        return 0;
    }

    protected function guessTableName(string $migrationName): string
    {
        // Case: create_blog_posts_table => blog_posts
        if (preg_match('/create_(.*?)_table/', $migrationName, $matches)) {
            return $matches[1];
        }

        // Case: add_column_to_users_table => users
        if (preg_match('/.*_to_(.*?)_table/', $migrationName, $matches)) {
            return $matches[1];
        }

        // Default fallback to snake_case of migration name
        return Str::snake($migrationName);
    }
}
