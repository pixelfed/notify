<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:process')->everyMinute();
