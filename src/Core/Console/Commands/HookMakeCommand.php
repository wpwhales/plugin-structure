<?php

namespace WPWCore\Console\Commands;

use WPWCore\Console\GeneratorCommand;
use WPWCore\Filesystem\Filesystem;
use WPWhales\Support\Facades\Config;
use WPWCore\Console\Commands\Inspiring;
use WPWhales\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'make:hook')]
class HookMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new wordpress hooks class ';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Hook';


    public function __construct(Filesystem $files, $config)
    {
        $this->config = $config;


        parent::__construct($files);
    }
    /**
     * Get the first config directory path from the application configuration.
     *
     * @param string $path
     * @return string
     */
    protected function configPath($path = '')
    {
        $config = $this->laravel['config']['config.paths'][0] ?? $this->laravel->configPath();

        return $config . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function handle()
    {

        $status = parent::handle();

        if ($status === false) {
            return false;
        }

        if (!$this->files->exists($this->configPath("hooks.php"))) {
            $this->files->put($this->configPath("hooks.php"), '<?php return []; ?>');
        }


        $name = $this->qualifyClass($this->getNameInput());

        //it has been created now let's add it inside the hooks file

        $configPath = $this->laravel->configPath('hooks.php');

        // Read the config file
        $config = include $configPath;

        // Check if the class name is already in the array
        if (!in_array($name, $config)) {
            // Append the class name
            $config[] = $name;

            // Write the updated array back to the file
            $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";

            file_put_contents($configPath, $content);
//
            $this->info("Hook name '{$name}' has been added to the config file.");
        } else {
            $this->info("Hook name '{$name}' already exists in the config file.");
        }

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {


        $stub = '/stubs/hook.stub';

        return $this->resolveStubPath($stub);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }


    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Hooks';
    }








}
