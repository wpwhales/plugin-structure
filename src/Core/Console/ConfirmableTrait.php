<?php

namespace WPWCore\Console;

trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     *
     * @param string $warning
     * @param \Closure|bool|null $callback
     * @return bool
     */
    public function confirmToProceed($warning = 'Application In Production', $callback = null)
    {
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;

        $shouldConfirm = \WPWCore\Collections\value($callback);

        if ($shouldConfirm) {
            if ($this->hasOption('force') && $this->option('force')) {
                return true;
            }

            $this->components->alert($warning);

            $confirmed = $this->components->confirm('Do you really wish to run this command?');

            if (!$confirmed) {
                $this->newLine();

                $this->components->warn('Command canceled.');

                return false;
            }
        }

        return true;
    }

    /**
     * Get the default confirmation callback.
     *
     * @return \Closure
     */
    protected function getDefaultConfirmCallback()
    {

        return function () {
            return $this->getLaravel()->environment() === 'production';
        };
    }
}
