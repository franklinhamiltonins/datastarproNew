<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Model\LeadsModel\Contact;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestingBeforeklaviyo;
use App\Model\SmtpConfiguration;
use Config;

use App\Traits\KlaviyoFunctionsTrait;

class AddEmailToKlaviyo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,KlaviyoFunctionsTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 40; // Timeout in seconds
    public $tries = 1; // Maximum number of attempts
    protected $contact ;

    public function __construct(object $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $this->tempmakinglog();

        $this->newklaviyoapi();
        
    }

    public function newklaviyoapi()
    {

        $header =  $this->getklaviyoheader();

        $post_field = $this->getklaviyoprofilecreation_body($this->contact->id);

        if($post_field['success']){
            $profile_id = $this->callklaviyoprofile_creation($header,$post_field['payload']);

            if(!empty($profile_id)){
                $post_field2 = $this->getklaviyoprofile_linktolist($profile_id);

                $this->callklaviyoprofile_linktolist($header,$post_field2);

                unset($post_field2);
            }
        }

        unset($header,$post_field);

    }

    public function tempmakinglog()
    {
        // sms_provider_queue_log
        // code...
        $this->sendingtestemail();
        DB::table('sms_provider_queue_log')
        ->where('contact_id',$this->contact->id)
        ->update([
            'execution_date' => date('Y-m-d'),
            'execution_time' => date('H:i:s'),
        ]);
    }

    public function sendingtestemail()
    {
        $this->setTestingDynamicSMTP();
        $data['subject'] = "Contact id - ".$this->contact->id."   Contact Email".$this->contact->c_email;
        $data['content'] = "Contact id - ".$this->contact->id."   Contact Email".$this->contact->c_email;


        Mail::to("lal.yadav@codeclouds.in")
        ->cc(['rohit.kumar@codeclouds.com'])
        ->send(new TestingBeforeklaviyo($data));
    }

    public function setTestingDynamicSMTP()
    {
        $smtp_data = $this->checkTestingMailConfiguration();

        if (($smtp_data > 0)) {

            $configuration = SmtpConfiguration::where("user_id", 20)->first();
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

    public function checkTestingMailConfiguration()
    {
        $whereCond = [
            ['username', '!=', ''],
            ['password', '!=', ''],
            ['host', '!=', ''],
            ['port', '!=', ''],
            ['encryption', '!=', ''],
            ['from_name', '!=', ''],

        ];
        $smtp_count = SmtpConfiguration::where('user_id', 20)
            ->where($whereCond)
            ->count();
        return $smtp_count;
    }
}
