<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use ReflectionClass;
use ReflectionMethod;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // Path to your controllers
        $controllerPath = app_path('Http/Controllers/API');

        // Get all controller files recursively if needed
        $controllerFiles = $this->getControllerFiles($controllerPath);

        foreach ($controllerFiles as $controllerFile) {
            // Build full class name including namespace
            $relativePath = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $controllerFile);
            $class = str_replace(DIRECTORY_SEPARATOR, '\\', rtrim($relativePath, '.php'));
            $fullClass = 'App\\' . str_replace('.php', '', $class);
            if (!class_exists($fullClass)) {
                continue; // skip if class doesn't exist
            }

            $reflection = new ReflectionClass($fullClass);

            // Ignore abstract classes or traits
            if ($reflection->isAbstract() || $reflection->isTrait()) {
                continue;
            }

            // Derive model name from controller name (e.g. CraftsmanController => Craftsman)
            $modelName = strtolower(str_replace('Controller', '', $reflection->getShortName()));

            // Scan public methods for common action names
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $methodName = $method->name;

                // Filter only action methods you want to consider
                if (in_array($methodName, ['index', 'show', 'store', 'update', 'destroy', 'create', 'edit', 'completeRegistration'])) {

                    // permission name example: 'Craftsman.index'
                    $permissionName = $modelName . '.' . $methodName;

                    Permission::firstOrCreate(
                        ['name' => $permissionName],
                        ['description' => ucfirst($methodName) . ' ' . ucfirst($modelName)]
                    );
                }
            }
        }
    }

    // Helper: Recursively get all controller files
    protected function getControllerFiles($path)
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }
}
