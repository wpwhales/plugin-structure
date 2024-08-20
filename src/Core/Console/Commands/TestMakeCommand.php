<?php

namespace WPWCore\Console\Commands;

use WPWCore\Console\GeneratorCommand;
use WPWhales\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function WPWCore\base_path;

#[AsCommand(name: 'make:test')]
class TestMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Test';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('unit')) {
            $name = "test.unit.stub";
        } elseif ($this->option('wordpress')) {
            $name = "test-wordpress.stub";
        } elseif ($this->option('wordpress-ajax')) {
            $name = "test-wordpress-ajax.stub";
        } else {
            $name = "test-wordpress.stub";
        }

        return $this->resolveStubPath('/stubs/' . $name);

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
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return base_path('tests/phpunit/testcases') . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'Tests';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the test already exists'],
            ['unit', 'u', InputOption::VALUE_NONE, 'Create a unit test'],
            ['wordpress', 'w', InputOption::VALUE_NONE, 'Create a Wordpress test'],
            ['wordpress-ajax', 'wa', InputOption::VALUE_NONE, 'Create a Ajax Wordpress test'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }

        $type = $this->components->choice('Which type of test would you like', [
            'unit',
            'wordpress',
            'wordpress ajax',
        ], default: 1);

        match ($type) {

            'unit' => $input->setOption('unit', true),
            'wordpress' => $input->setOption('wordpress', true),
            'wordpress ajax' => $input->setOption('wordpress-ajax', true),
        };
    }
}
