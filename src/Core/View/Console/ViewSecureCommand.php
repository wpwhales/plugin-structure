<?php

namespace WPWCore\View\Console;

use WPWCore\Console\Command;
use WPWCore\Filesystem\FilesystemManager;
use WPWhales\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function WPWCore\check_content_for_ABSPATH_constant;

#[AsCommand(name: 'view:secure')]
class ViewSecureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:secure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Appends the ABSPATH constant check at the beggining of each file";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $compiled_files = [];
        $this->paths()->each(function ($path) use(&$compiled_files) {
            $prefix = $this->output->isVeryVerbose() ? '<fg=yellow;options=bold>DIR</> ' : '';

            $this->components->task($prefix . $path, null, OutputInterface::VERBOSITY_VERBOSE);

            $compiled_files = array_merge($this->processFiles($this->bladeFilesIn([$path])),$compiled_files);
        });

        $this->newLine();

        if(!empty($compiled_files)){
            $this->components->info('Secured  Files');
            $this->components->bulletList($compiled_files);
            $this->newLine();
        }

        $this->components->info('Blade templates secured successfully.');


    }

    protected function processFiles($collection)
    {
        $compiled_files = [];
        foreach ($collection as $path => $file) {

            $content = file_get_contents($path);

            if (!\WPWCore\check_content_for_ABSPATH_constant($content)) {
                //It means the ABSPATH constant is not available at the beginning
                //append it
                $this->addCodeToFile($path, $content);
                $compiled_files[] = $path;
            }

        }

        return $compiled_files;
    }


    protected function addCodeToFile($path, $content)
    {

        /**
         * @var $filesystem FilesystemManager
         */
        $filesystem = $this->laravel["files"];

        $str = "<?php if (!defined('ABSPATH')) die();?>";

        $filesystem->put($path,$str.$content);
    }

    /**
     * Get the Blade files in the given path.
     *
     * @param array $paths
     * @return \Illuminate\Support\Collection
     */
    protected function bladeFilesIn(array $paths)
    {
        $extensions = \WPWCore\Collections\collect($this->laravel['view']->getExtensions())
            ->filter(fn($value) => $value === 'blade')
            ->keys()
            ->map(fn($extension) => "*.{$extension}")
            ->all();

        return \WPWCore\Collections\collect(
            Finder::create()
                ->in($paths)
                ->exclude('vendor')
                ->name($extensions)
                ->files()
        );
    }

    /**
     * Get all of the possible view paths.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function paths()
    {
        $finder = $this->laravel['view']->getFinder();

        return \WPWCore\Collections\collect($finder->getPaths())->merge(
            \WPWCore\Collections\collect($finder->getHints())->flatten()
        );
    }
}