<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsPrivacyController extends Controller
{
    public function termsCondition()
    {
        return view('policy.terms-condition');
    }

    public function privacyPolicy()
    {
        return view('policy.privacy-policy');
    }
}
