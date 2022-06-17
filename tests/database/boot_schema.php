<?php

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
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
     * @param Container|null $container
     * @return Connection
     */
    public function boot(Container $container = null)
    {
        $configurations = require dirname(__FILE__, 3) . '/config/database.php';
        $connection = data_get($configurations, 'default', env('DB_CONNECTION'));

        $capsule = new Manager($container);
        $capsule->addConnection(data_get($configurations, "connections.{$connection}"));

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $this->builder = new Builder($connection = $capsule->getConnection());

        return $connection;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $builder = $this->getBuilder();

        $builder->dropIfExists('users');
        $builder->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('posts');
        $builder->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();
        });

        $builder->dropIfExists('comments');
        $builder->create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id');
            $table->string('title');
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('post_id')->on('posts')->references('id');
        });

        $builder->dropIfExists('replies');
        $builder->create('replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id');
            $table->foreignId('reply_id')->nullable();
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('comment_id')->on('comments')->references('id');
            $table->foreign('reply_id')->on('replies')->references('id');
        });

        //singular on purpose
        $builder->dropIfExists('tag');
        $builder->create('tag', function (Blueprint $table) {
            $table->id();
            $table->string('title');
        });

        $builder->dropIfExists('post_tag');
        $builder->create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id');
            $table->foreignId('tag_id');

            $table->foreign('post_id')->on('posts')->references('id');
            $table->foreign('tag_id')->on('tag')->references('id');

            $table->unique(['post_id', 'tag_id']);
        });

        $builder->dropIfExists('authors');
        $builder->create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->foreign('user_id')->on('users')->references('id');
            $table->morphs('of_content');
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
