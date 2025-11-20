<?php

namespace WezomCms\Core\Commands;

use Illuminate\Console\Command;

class WidgetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:widgets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered widgets';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Name', 'Class'];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $widgets = app('widget')->getWidgets();

        if (count($widgets) === 0) {
            $this->error("Your application doesn't have any widgets.");

            return;
        }

        $widgets = collect($widgets)
            ->filter(function ($widget) {
                return !is_array($widget);
            })
            ->map(function ($class, $name) {
                return ['name' => $name, 'class' => $class];
            })
            ->sortBy(function ($widget) {
                return $widget['name'];
            })
            ->all();

        $this->displayWidgets($widgets);
    }

    /**
     * Display the widget information on the console.
     *
     * @param  array  $widgets
     * @return void
     */
    protected function displayWidgets(array $widgets)
    {
        $this->table($this->headers, $widgets);
    }
}
