<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new class extends Migration
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Connection $connection
     * @return Builder
     */
    public function getBuilder(Connection $connection)
    {
        $this->builder = new Builder($connection);

        return $this->builder;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(Builder $builder)
    {
        $builder->dropIfExists('persons');
        $builder->create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->dateTime('birth_date');
            $table->enum('sex', ['M', 'F']);
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('tag');
        $builder->create('tag', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('active');
            $table->timestamps();
        });

        $builder->dropIfExists('taggable');
        $builder->create('taggable', function (Blueprint $table){
            $table->foreignId('tag_id')->constrained('tag');
            $table->morphs('taggable', 'taggable_class');
            $table->unsignedInteger('order')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $builder->dropIfExists('country');
        $builder->create('country', function (Blueprint $table) {
            $table->id();
            $table->char('iso_code', 5)->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('administrative_division');
        $builder->create('administrative_division', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrative_division_id')->nullable()->constrained('administrative_division');
            $table->foreignId('country_id')->constrained('country');
            $table->foreignId('tag_id')->constrained('tag');
            $table->string('abbr', 10)->unique();
            $table->string('name');
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('location');
        $builder->create('location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('country');
            $table->foreignId('administrative_division_id')->constrained('administrative_division');
            $table->string('abbr', 10)->unique()->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('addresses');
        $builder->create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained();
            $table->foreignId('country_id')->constrained('country');
            $table->foreignId('location_id')->constrained('location');
            $table->text('description');
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('tasks');
        $builder->create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('persons');
            $table->string('title');
            $table->text('description');
            $table->boolean('complete')->default(true);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('task_follower');
        $builder->create('task_follower', function (Blueprint $table){
            $table->foreignId('task_id')->constrained();
            $table->foreignId('person_id')->constrained();

            $table->unique(['person_id', 'task_id'], 'task_follower_unique');
        });

        $builder->dropIfExists('comments');
        $builder->create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('persons');
            $table->foreignId('task_id')->constrained();
            $table->longText('body');
            $table->unsignedInteger('likes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('attachments');
        $builder->create('attachments', function (Blueprint $table){
            $table->id();
            $table->morphs('owner');
            $table->string('path');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
