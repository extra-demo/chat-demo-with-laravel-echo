<?php

namespace App\Console\Commands;

use App\Events\ChatRoomNotify;
use Illuminate\Console\Command;

class TriggerEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'triggerEvent:cm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'triggerEvent:cm';

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
     * @return int
     */
    public function handle()
    {
        broadcast(new ChatRoomNotify());
        return 0;
    }
}
