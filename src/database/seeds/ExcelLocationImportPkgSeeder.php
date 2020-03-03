<?php
namespace Abs\LocationPkg\Database\Seeds;

use Illuminate\Database\Seeder;

class ExcelLocationImportPkgSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		// dd('dshjdgsfhjdsg');
		$items = Excel::selectSheetsByIndex(0)->load('public/excel-imports/locations.xlsx', function ($reader) {
			$reader->limitRows(1000);
			$reader->limitColumns(3);
			$records = $reader->get();
			foreach ($records as $key => $record) {
				try {
					$errors = [];
					if (empty($record->item_code)) {
						$errors[] = 'empty item code';
					} else {
						$item = ItemDetail::where([
							'code' => $record->item_code,
						])->first();
						if (!$item) {
							$errors[] = 'item code not found: ' . $record->item_code;
						}
					}
					if (isset($record->product_group)) {
						if (empty($record->product_group)) {
							$errors[] = 'empty product group';
						} else {
							$product_group = ProductGroup::firstOrCreate([
								'company_id' => 1,
								'name' => $record->product_group,
							]);
							if ($item) {
								$item->product_group_id = $product_group->id;
							}
						}
					}
					if (isset($record->pack_size)) {
						if (empty($record->pack_size)) {
							$errors[] = 'empty pack size';
						} else {
							if (isset($item)) {
								$item->package_qty = $record->pack_size;
							}
						}
					}
					if (isset($record->hsn_code)) {
						if (empty($record->hsn_code)) {
							$errors[] = 'empty hsn code';
						} else {
							$hsn_code = HsnCode::where('company_id', 1)->where('code', $record->hsn_code)->first();
							if (isset($item)) {
								$item->hsn_code_id = $hsn_code->id;
							}
						}
					}
					if (count($errors) > 0) {
						dump($key + 1, $errors, $record);
						continue;
					}
					if ($record->display_order) {
						$item->display_order = $record->display_order;
					}
					$item->save();
				} catch (Exception $e) {
					dd($e);
				}
			}
		});
	}
}