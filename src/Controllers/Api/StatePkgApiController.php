<?php

namespace Abs\LocationPkg\Controllers\Api;

use App\Models\Masters\Locations\State;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;

class StatePkgApiController extends BaseController {
	use CrudTrait;
	public $model = State::class;
}
