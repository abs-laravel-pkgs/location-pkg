<?php

namespace Abs\LocationPkg\Api;

use App\Http\Controllers\Controller;
use App\State;
use Illuminate\Http\Request;

class StateController extends Controller {
	public $successStatus = 200;

	public function getDropDownList(Request $r) {
		$this->data['state_list'] = State::getDropDownList($r->all());
		$this->data['success'] = true;
		return response()->json($this->data, $this->successStatus);
	}

}
