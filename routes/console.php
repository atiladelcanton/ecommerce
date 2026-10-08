<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('saba:about', function (): void {
    $this->info('SABA Máquinas e-commerce starter');
})->purpose('Display project identification');
