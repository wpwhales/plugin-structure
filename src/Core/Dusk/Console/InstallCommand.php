<?php

namespace WPWCore\Dusk\Console;

use WPWCore\Console\Command;
use function WPWCore\base_path;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dusk:install
                {--proxy= : The proxy to download the binary through (example: "tcp://127.0.0.1:9000")}
                {--ssl-no-verify : Bypass SSL certificate verification when installing through a proxy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Dusk into the application';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if (! is_dir(base_path('tests/Browser/Pages'))) {
            mkdir(base_path('tests/Browser/Pages'), 0755, true);
        }

        if (! is_dir(base_path('tests/Browser/Components'))) {
            mkdir(base_path('tests/Browser/Components'), 0755, true);
        }

        if (! is_dir(base_path('tests/Browser/screenshots'))) {
            $this->createScreenshotsDirectory();
        }

        if (! is_dir(base_path('tests/Browser/console'))) {
            $this->createConsoleDirectory();
        }

        if (! is_dir(base_path('tests/Browser/source'))) {
            $this->createSourceDirectory();
        }

        $stubs = [
            'ExampleTest.stub' => base_path('tests/Browser/ExampleTest.php'),
            'HomePage.stub' => base_path('tests/Browser/Pages/HomePage.php'),
            'DuskTestCase.stub' => base_path('tests/Browser/DuskTestCase.php'),
            'Page.stub' => base_path('tests/Browser/Pages/Page.php'),
            'Bootstrap.stub' => base_path('tests/Browser/bootstrap.php'),
        ];

        $autoload = $this->getAutoloadNamespace();
        $namespace = trim(key($autoload),"\\");
        foreach ($stubs as $stub => $file) {

            if (! is_file($file)) {
                $content = file_get_contents(__DIR__.'/../../../../stubs/'.$stub);
                $content = str_replace("{{ namespace }}",$namespace,$content);
                file_put_contents($file,$content);

            }
        }

        $this->info('Dusk scaffolding installed successfully.');

        $this->comment('Downloading ChromeDriver binaries...');

        $driverCommandArgs = [];

        if ($this->option('proxy')) {
            $driverCommandArgs['--proxy'] = $this->option('proxy');
        }

        if ($this->option('ssl-no-verify')) {
            $driverCommandArgs['--ssl-no-verify'] = true;
        }

        $this->call('dusk:chrome-driver', $driverCommandArgs);
    }

    protected function getAutoloadNamespace(){
        $composerJsonPath = base_path('composer.json');
        if (!file_exists($composerJsonPath)) {
            throw new \Exception('composer.json file not found');
        }

        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        if (!isset($composerJson['autoload']['psr-4'])) {
            throw new \Exception('PSR-4 autoload section not found in composer.json');
        }

        return $composerJson['autoload-dev']['psr-4'];
    }

    /**
     * Create the screenshots directory.
     *
     * @return void
     */
    protected function createScreenshotsDirectory()
    {
        mkdir(base_path('tests/Browser/screenshots'), 0755, true);

        file_put_contents(base_path('tests/Browser/screenshots/.gitignore'), '*
!.gitignore
');
    }

    /**
     * Create the console directory.
     *
     * @return void
     */
    protected function createConsoleDirectory()
    {
        mkdir(base_path('tests/Browser/console'), 0755, true);

        file_put_contents(base_path('tests/Browser/console/.gitignore'), '*
!.gitignore
');
    }

    /**
     * Create the source directory.
     *
     * @return void
     */
    protected function createSourceDirectory()
    {
        mkdir(base_path('tests/Browser/source'), 0755, true);

        file_put_contents(base_path('tests/Browser/source/.gitignore'), '*
!.gitignore
');
    }
}
