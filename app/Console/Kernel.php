<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		// Commands\UpdateEmailContacts::class,
		Commands\SendSms::class,
		Commands\AddEmailToKlaviyo::class,
		Commands\UpdateContactPhone::class,
		Commands\SendArbitaryKlaviyo::class,
		Commands\SmsSendToQueue::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		// $schedule->command('command:UpdateCampaignLeadActions')->daily();
		// $schedule->command('command:smssend')->everyMinute();

		$schedule->command('command:sms-send-to-queue 7')->everyThirtyMinutes();
		$schedule->command('sendarbitary:klaviyo 3 7')->everyThirtyMinutes();
		// $schedule->command('command:scrap-yellow-pages')->everyFiveMinutes();
		// $schedule->command('command:scrap-contact-match')->everyThreeMinutes();

		// $schedule->command('command:sms-send-to-queue')->everyMinute();
		// $schedule->command('command:sms-send-from-queue')->everyMinute();
		// $schedule->command('command:add-email-to-klaviyo')->everyFiveMinutes();
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
