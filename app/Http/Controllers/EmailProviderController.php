<?php

namespace App\Http\Controllers;

use App\Model\EmailProvider;
use Illuminate\Http\Request;

class EmailProviderController extends Controller
{
    /*
    * Get single provider details
    */
    public function getProviderDetails(Request $request)
    {
        $provider = EmailProvider::find($request->id);

        return response()->json(['provider' => $provider, 'message' => '']);
    }
}
