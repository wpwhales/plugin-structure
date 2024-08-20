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

#[AsCommand(name: 'make:shortcode')]
class ShortcodeMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:shortcode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new wordpress shortcode ';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Shortcode';


    public function __construct(Filesystem $files, $config)
    {
        $this->config = $config;


        parent::__construct($files);
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {


        $stub = '/stubs/shortcode.stub';

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
        return $rootNamespace . '\Shortcodes';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in the base namespace.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["{{viewName}}"] = $this->getView();


        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        $status = parent::handle();

        if ($status === false) {
            return false;
        }

        $stub = $this->files->get($this->resolveStubPath("/stubs/shortcode-config.stub"));
        if (!$this->files->exists($this->configPath("shortcodes.php"))) {
            $this->files->put($this->configPath("shortcodes.php"), $stub);
        }

        $this->writeView(function () {
            $this->components->info($this->type . ' view file created successfully.');
        });


        $name = $this->qualifyClass($this->getNameInput());

        //it has been created now let's add it inside the hooks file

        $configPath = $this->laravel->configPath('shortcodes.php');

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
            $this->info("Shortcode name '{$name}' has been added to the config file.");
        } else {
            $this->info("Shortcode name '{$name}' already exists in the config file.");
        }

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

    /**
     * Write the view for the component.
     *
     * @param callable|null $onSuccess
     * @return void
     */
    protected function writeView($onSuccess = null)
    {
        $path = $this->viewPath(
            str_replace('.', '/', 'shortcodes.' . $this->getView()) . '.blade.php'
        );

        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        if ($this->files->exists($path) && !$this->option('force')) {
            $this->components->error('Shortcode View already exists.');

            return;
        }

        file_put_contents(
            $path,
            '
<?php if (!defined(\'ABSPATH\')) die();?>
<div>
    <!-- ' . Inspiring::quotes()->random() . ' -->
</div>'
        );

        if ($onSuccess) {
            $onSuccess();
        }
    }

    /**
     * Get the view name relative to the components directory.
     *
     * @return string view
     */
    protected function getView()
    {
        $name = str_replace('\\', '/', $this->argument('name'));

        return \WPWCore\Collections\collect(explode('/', $name))
            ->map(function ($part) {
                return Str::kebab($part);
            })
            ->implode('.');
    }


}
