<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('duration_minutes');
            $table->integer('max_attempts')->default(1);
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);
            $table->boolean('show_result')->default(true);
            $table->boolean('show_correct_answers')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['published_at', 'starts_at', 'ends_at']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'essay']);
            $table->text('content');
            $table->text('explanation')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['exam_id', 'order']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'order']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('attempt_number');
            $table->enum('status', ['in_progress', 'submitted', 'grading', 'graded'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['exam_id', 'user_id', 'attempt_number']);
            $table->index(['user_id', 'status']);
            $table->index(['exam_id', 'status']);
        });

        Schema::create('attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('question_snapshot');
            $table->json('options_snapshot')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('points_snapshot')->default(1);
            $table->timestamps();

            $table->index(['attempt_id', 'order']);
        });

        Schema::create('attempt_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_question_id')->constrained('attempt_questions')->onDelete('cascade');
            $table->foreignId('option_id')->constrained('question_options')->onDelete('cascade');
            $table->text('content_snapshot');
            $table->boolean('is_correct_snapshot');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['attempt_question_id', 'order']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('attempt_question_id')->constrained('attempt_questions')->onDelete('cascade');
            $table->foreignId('attempt_question_option_id')->nullable()->constrained('attempt_question_options')->onDelete('cascade');
            $table->text('essay_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_earned', 5, 2)->nullable();
            $table->text('grader_notes')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'attempt_question_id']);
            $table->index(['attempt_question_id', 'is_correct']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('attempt_question_options');
        Schema::dropIfExists('attempt_questions');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exams');
    }
};
