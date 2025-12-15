<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect, Response;
use App\Model\EmailProvider;

class EmailProviderController extends Controller
{

	/*
	* Get single provider details
	*/
	public function getProviderDetails(Request $request) {
		$provider = EmailProvider::find($request->id);
		return response()->json(['provider' => $provider, 'message' => '']);
	}

}
