<?php

namespace Abs\LocationPkg\Controllers\Api;

use App\Models\Masters\Locations\City;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;

class CityPkgApiController extends BaseController {
	use CrudTrait;
	public $model = City::class;
}
