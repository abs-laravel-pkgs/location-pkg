<?php

namespace Abs\LocationPkg\Api;

use App\Country;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CountryController extends Controller {
	public $successStatus = 200;

	public function getDropDownList(Request $r) {
		$this->data['country_list'] = Country::getDropDownList($r->all());
		$this->data['success'] = true;
		return response()->json($this->data, $this->successStatus);
	}

}
