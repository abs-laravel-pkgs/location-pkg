<?php

namespace Abs\LocationPkg\Controllers\Api;

use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use App\Models\Masters\Locations\State;

class StatePkgApiController extends BaseController {
	use CrudTrait;
	public $model = State::class;
}
