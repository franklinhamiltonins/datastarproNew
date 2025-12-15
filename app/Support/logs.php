<?php
use App\Model\User;

if (!function_exists('create_log')) {
    /**
     * Create Lead log
     *
     * @param  string $lead, $action- action text
     * @return string
     */
    function create_log($element, $action,$created_at){
       $log =  $element->logs()->create([
            'action'=> $action,

        ]);

        $userUnAuth = User::where('email','oana.ghinescu@gosocialdev.eu')->first() ;
        if(auth()->user())
            $log->users()->associate(auth()->user())->save();//associate user
        else{
            if( $userUnAuth)
                $log->users()->associate($userUnAuth)->save();
            }

        if(!empty($created_at)){
            $log->update([
                'created_at'=> $created_at
            ]);
        }
    }
}

