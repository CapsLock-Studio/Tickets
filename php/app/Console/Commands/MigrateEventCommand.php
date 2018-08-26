<?php

namespace App\Console\Commands;

use Parsedown;
use App\Event;
use Illuminate\Console\Command;

class MigrateEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate event to database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mark      = new Parsedown();
        $path      = base_path('events');
        $events    = Event::whereNotNull('template')->get();
        $templates = array_pluck($events, 'template');
        foreach (glob($path.'/*') as $templatePath) {
            $information = file_get_contents($templatePath.'/information.json');
            $information = json_decode($information, true);
            $description = file_get_contents($templatePath.'/description.md');
            $description = $mark->text($description);

            $template = basename($templatePath);
            if (!in_array($template, $templates)) {
                Event::create($information + compact('description', 'template'));
                $this->info($template.' is migrated');
            }
        }
    }
}