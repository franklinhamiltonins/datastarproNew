<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\SmtpConfiguration;
use Illuminate\Support\Facades\Crypt;
use App\Model\Email;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;

trait SMTPRelatedTrait
{
    public function checkMailConfigurationUserWise($mail_agent_id)
	{
		$whereCond = [
			['username', '!=', ''],
			['password', '!=', ''],
			['host', '!=', ''],
			['port', '!=', ''],
			['encryption', '!=', ''],
			['from_name', '!=', ''],

		];
		$smtp_count = SmtpConfiguration::where('user_id', $mail_agent_id)
			->where($whereCond)
			->count();
		return $smtp_count;
	}

	public function setDynamicSMTPUserWise($mail_agent_id)
	{
		$smtp_data = $this->checkMailConfigurationUserWise($mail_agent_id);

		if (($smtp_data > 0)) {
			$configuration = SmtpConfiguration::where("user_id", $mail_agent_id)->first();
			$password = Crypt::decryptString("$configuration->password");

			$config = array(
				'driver'     => 'smtp',
				'transport' => 'smtp',
				'host'       => $configuration->host,
				'port'       => $configuration->port,
				'username'   => $configuration->username,
				'password'   => "$password",
				'encryption' => $configuration->encryption,
				'from'       => ['address' => $configuration->username, 'name' => $configuration->from_name],
				'sendmail'   => '/usr/sbin/sendmail -bs',
				'pretend'    => false,
			);

			Config::set('mail', $config);
		}
	}
}
