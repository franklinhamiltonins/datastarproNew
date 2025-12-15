<?php

namespace App\Events;

use App\Model\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $message;

	public function __construct(Message $message)
	{
		$this->message = $message;
	}

	public function broadcastOn()
	{
		return ['chat.' . $this->message->contact_id];
	}
}
