<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model {
	use SeederTrait;
	use SoftDeletes;

	protected $table = 'districts';
	protected $fillable = [
		'state_id',
		'name',
		'short_name',
		'code',
	];

	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function state() {
		return $this->belongsTo('Abs\LocationPkg\State');
	}
}
