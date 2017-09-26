<?php

namespace Germey\Geetest;

use Illuminate\Routing\Controller;

class GeetestController extends Controller
{
	use GeetestCaptcha;
	public function __construct()
	{
		$this->middleware('web');
	}
}
