<?php

namespace App\Console\Commands;

use App\Services\AutoNotifyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:auto-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自動処理';

    protected $log;
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
        try {
            $service = app(AutoNotifyService::class);
            $result = $service->run();
            error_log($result);
            Log::debug(now().'==> notified count:'. $result);

        } catch (Throwable $e) {
            error_log($e);
            report($e);
            return;
        }
    }
}
