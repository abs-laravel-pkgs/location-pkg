<?php

namespace Abs\LocationPkg\Api;

use App\City;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CityController extends Controller {
	public $successStatus = 200;

	public function getDropDownList(Request $r) {
		$this->data['city_list'] = City::getDropDownList($r->all());
		$this->data['success'] = true;
		return response()->json($this->data, $this->successStatus);
	}

}
