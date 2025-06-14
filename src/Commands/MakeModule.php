<?php

namespace CreativeSoftTechSolutions\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModule extends Command implements PromptsForMissingInput
{
    protected $signature = 'module:create {name : The name of the module}';

    protected $description = 'Create a new module with default structure';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $moduleName = Str::studly($name); // StudlyCase
        $lowerModuleName = Str::kebab($name); // kebab-case
        $basePath = config('modules.path') . "/{$moduleName}";

        if ($this->filesystem->exists($basePath)) {
            $this->error("Module {$moduleName} already exists!");

            return 1;
        }

        // Create directory structure
        $directories = [
            "{$basePath}/Config",
            "{$basePath}/Controllers",
            "{$basePath}/Livewire",
            "{$basePath}/Middleware",
            "{$basePath}/Models",
            "{$basePath}/Migrations",
            "{$basePath}/Resources/views",
            "{$basePath}/Routes",
            "{$basePath}/Providers",
        ];

        foreach ($directories as $dir) {
            $this->filesystem->makeDirectory($dir, 0755, true);
            file_put_contents("{$dir}/.gitkeep", ''); // Ensure Git tracks this empty folder
        }

        // Generate stub files
        $this->createConfigFile($moduleName, $basePath);
        $this->createNavbarConfigFile($basePath);
        $this->createHooksFile($basePath);
        $this->createControllerFile($moduleName, $basePath);
        $this->createServiceProviderFile($moduleName, $basePath);
        $this->createRoutesFiles($moduleName, $lowerModuleName, $basePath);
        $this->createViewFile($moduleName, $basePath);

        $this->info("✅ Module {$moduleName} created successfully!");

        return 0;
    }

    protected function createConfigFile($moduleName, $basePath)
    {
        $content = <<<EOT
<?php

return [
    'name' => '{$moduleName}',
    'enabled' => true,
];
EOT;

        file_put_contents("{$basePath}/Config/config.php", $content);
    }

    protected function createNavbarConfigFile($basePath)
    {
        $content = <<<'EOT'
<?php

return [
    // Optional: Define AdminNavLinks, ClientNavLinks, or PublicNavLinks
];
EOT;

        file_put_contents("{$basePath}/Config/navbar_config.php", $content);
    }

    protected function createHooksFile($basePath)
    {
        $content = <<<'EOT'
<?php

// Example actions and filters
/*
hooks()->addAction('after_user_register', function () {
    logger('Module action: user registered');
});

hooks()->addFilter('user_display_name', function ($name) {
    return strtoupper($name);
});
*/
EOT;

        file_put_contents("{$basePath}/Config/hooks.php", $content);
    }

    protected function createControllerFile($moduleName, $basePath)
    {
        $namespace = "Modules\\{$moduleName}\\Controllers";
        $controllerName = "{$moduleName}Controller";
        $lowerName = strtolower($moduleName);

        $content = <<<EOT
<?php

namespace {$namespace};

use App\Http\Controllers\Controller;

class {$controllerName} extends Controller
{
    public function index()
    {
        return view('{$lowerName}::index', ['message' => 'Welcome to the {$moduleName} module!']);
    }
}
EOT;

        file_put_contents("{$basePath}/Controllers/{$controllerName}.php", $content);
    }

    protected function createServiceProviderFile($moduleName, $basePath)
    {
        $namespace = "Modules\\{$moduleName}\\Providers";
        $configName = strtolower($moduleName);

        $content = <<<EOT
<?php

namespace {$namespace};

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        \$this->mergeConfigFrom(__DIR__ . '/../Config/config.php', '{$configName}');
    }

    public function boot()
    {
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        \$this->loadViewsFrom(__DIR__ . '/../Resources/views', '{$configName}');
        \$this->loadMigrationsFrom(__DIR__ . '/../Migrations');

        if (file_exists(__DIR__ . '/../Config/hooks.php')) {
            require __DIR__ . '/../Config/hooks.php';
        }
        \$this->registerLivewireComponents();
    }
    protected function registerLivewireComponents()
    {
        \$namespace = 'Modules\\{$moduleName}\\Livewire';
        \$livewirePath = __DIR__ . '/../Livewire';
        if (File::exists(\$livewirePath)) {

            foreach (File::allFiles(\$livewirePath) as \$file) {
                \$className = \$namespace . '\\\' . \$file->getFilenameWithoutExtension();

                // Check if the class exists and is a Livewire component
                if (class_exists(\$className) && is_subclass_of(\$className, \Livewire\Component::class)) {
                    // Generate a component name (e.g., "multilevelmarketing.wallet")
                    \$componentName = '{$configName}.' . strtolower(\$file->getFilenameWithoutExtension());
                    Livewire::component(\$componentName, \$className);
                }
            }
        }
    }
}
EOT;

        file_put_contents("{$basePath}/Providers/{$moduleName}ServiceProvider.php", $content);
    }

    protected function createRoutesFiles($moduleName, $lowerModuleName, $basePath)
    {
        $controller = "{$moduleName}Controller";
        $namespace = "Modules\\{$moduleName}\\Controllers";

        $webRoutes = <<<EOT
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'prevent.back.history'])->group(function () {
    require __DIR__ . '/central.php';
    require __DIR__ . '/tenant.php';
});

EOT;

        $tenantRoutes = <<<EOT
<?php

use Illuminate\Support\Facades\Route;
use {$namespace}\\{$controller};

Route::middleware(['prevent.access.central'])->name('tenant.')->group(function () {

    Route::middleware(['tenant.auth', 'tenant.verified','tenant.set.template.client'])->group(function () {

        Route::get('/{$lowerModuleName}', [{$controller}::class, 'index'])->name('{$lowerModuleName}.index');
       
    }); 
});


EOT;

        $centralRoutes = <<<EOT
<?php

use Illuminate\Support\Facades\Route;
use {$namespace}\\{$controller};

Route::domain(config('tenancy.central_domain'))->middleware(['prevent.access.tenant'])->group(function () {
    Route::middleware(['auth', 'verified'])->name('central.')->group(function () {

        // Tenant Routes
        // Route::get('/{$lowerModuleName}', [{$controller}::class, 'index'])->name('tenant.{$lowerModuleName}.index');

        // Central Routes
        // Route::prefix('website/{website_id}/{$lowerModuleName}')->group(function () {
        //     Route::get('/index', [{$controller}::class, 'index'])->name('central.{$lowerModuleName}.index');
        // });

    });
});


EOT;

        file_put_contents("{$basePath}/Routes/web.php", $webRoutes);
        file_put_contents("{$basePath}/Routes/tenant.php", $tenantRoutes);
        file_put_contents("{$basePath}/Routes/central.php", $centralRoutes);
    }

    protected function createViewFile($moduleName, $basePath)
    {
        $content = <<<EOT

@extends('layouts.client')
@section('content')
    <div class="h-full w-full my-5 p-2 rounded-xl">
        Welcome to {$moduleName}

    </div>
@endsection

EOT;

        file_put_contents("{$basePath}/Resources/views/index.blade.php", $content);
    }
}
