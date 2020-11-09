<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\models\Actions;
use Carbon\Carbon;

class ActionCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'action';
    protected $signature = "action";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Serve the application on the PHP development server";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public static function fire()
    {

        

    }

    public static function handle(){
      $now = Carbon::now();
      Actions::where('finished_at', '<', $now)->where('finished', 0)->update(['finished' => 1]);
    }


}
