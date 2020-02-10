<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShippingMethodsCreate extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('shipping_methods')) {
			Schema::create('shipping_methods', function (Blueprint $table) {
				$table->unsignedInteger('company_id');
				$table->string('code', 191);
				$table->string('name', 191);
				$table->string('description', 255)->nullable();
				$table->string('delivery_time', 255)->nullable();
				$table->unsignedInteger('logo_id')->nullable();
				$table->unsignedDecimal('charge', 8, 2)->default(0);
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softdeletes();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');

				$table->foreign('logo_id')->references('id')->on('attachments')->onDelete('SET NULL')->onUpdate('cascade');

				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["company_id", "code"]);
				$table->unique(["company_id", "name"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('shipping_methods');
	}
}
