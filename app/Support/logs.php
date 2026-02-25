<?php

use App\Model\User;

if (! function_exists('create_log')) {

    /**
     * Create Lead log
     *
     * @param  mixed   $element
     * @param  string  $action
     * @param  string|null $created_at
     * @return void
     */
    function create_log($element, $action, $created_at = null)
    {
        $log = $element->logs()->create(['action' => $action]);

        if (auth()->user()) {
            $log->users()->associate(auth()->user())->save();
        }

        if (! empty($created_at)) {
            $log->update(['created_at' => $created_at]);
        }
    }
}
