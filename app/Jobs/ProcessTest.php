<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessTest implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    protected  $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function handle(): void
    {
        Log::channel('daily')->debug($this->input.' Add process log.');
        sleep(3);
        Log::channel('daily')->debug($this->input.' Successprocess log.');
    }
}
