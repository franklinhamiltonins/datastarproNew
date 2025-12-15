<?php

// app/Listeners/SendNewMessageNotification.php

namespace App\Listeners;

use App\Events\NewMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewMessageNotification implements ShouldQueue
{
	use InteractsWithQueue;

	public function handle(NewMessage $event)
	{
		// Send notification logic here
	}
}
