<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateMailDomainsGlobalSignatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('mail_domains_global_signature', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Name');
        });

        $prefix = Capsule::connection()->getTablePrefix();
        Capsule::statement("ALTER TABLE {$prefix}mail_domains_global_signature ADD Signature MEDIUMBLOB NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('mail_domains_global_signature');
    }
}
