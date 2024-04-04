<?php

namespace WPWCore\ActionScheduler\Console;

use WPWCore\Console\Command;
use WPWCore\Filesystem\FilesystemManager;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use WPWhales\Support\Facades\DB;
use function WPWCore\check_content_for_ABSPATH_constant;

#[AsCommand(name: 'schedule:remove-canceled')]
class ScheduleRemoveCanceledCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:remove-canceled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove the canceled actions from the table.";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = DB::table("actionscheduler_actions as  a")
            ->join("actionscheduler_groups as  g", "g.group_id", "a.group_id")
            ->where("a.hook", "LIKE", "wpwcore_command_%")
            ->where("g.slug", "wpw-core")
            ->where("a.status", "=", "canceled")
            ->delete();

        DB::table("actionscheduler_logs")->whereNotIn("action_id",DB::table("actionscheduler_actions")->select("action_id")->pluck("action_id"))->delete();


        $this->info("Canceled action/hook are cleared from the table");

        return 0;

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

        $filesystem->put($path, $str . $content);
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