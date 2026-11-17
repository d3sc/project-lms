<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\ExamAttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin', 'is_active' => true,
            'email' => 'admin@test.local', 'password' => bcrypt('password'),
        ]);
        $this->student = User::factory()->create([
            'role' => 'student', 'is_active' => true,
            'email' => 'student@test.local', 'password' => bcrypt('password'),
        ]);
    }

    protected function createExam(array $attrs = []): Exam
    {
        return Exam::create(array_merge([
            'title' => 'Test Exam',
            'description' => 'Desc',
            'instructions' => 'Instr',
            'duration_minutes' => 60,
            'max_attempts' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'show_result' => true,
            'show_correct_answers' => false,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'published_at' => now()->subMinute(),
            'created_by' => $this->admin->id,
        ], $attrs));
    }

    protected function addMcQuestion(Exam $exam, string $content = 'MC?'): Question
    {
        $q = Question::create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'content' => $content,
            'points' => 10,
            'order' => 1,
        ]);
        QuestionOption::create(['question_id' => $q->id, 'content' => 'Wrong', 'is_correct' => false, 'order' => 1]);
        QuestionOption::create(['question_id' => $q->id, 'content' => 'Correct', 'is_correct' => true, 'order' => 2]);

        return $q;
    }

    /** @test */
    public function test_randomization_order_is_consistent_across_requests_for_same_attempt()
    {
        $exam = $this->createExam(['randomize_questions' => true, 'randomize_options' => true]);
        $this->addMcQuestion($exam, 'Q1');
        $this->addMcQuestion($exam, 'Q2');
        $this->addMcQuestion($exam, 'Q3');

        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        // First request
        $resp1 = $this->actingAs($this->student)
            ->get(route('student.exams.attempt', $attempt));
        $resp1->assertStatus(200);
        $order1 = [];
        foreach ($attempt->fresh()->attemptQuestions as $aq) {
            $order1[] = $aq->question_id;
        }

        // Second request (simulating refresh)
        $resp2 = $this->actingAs($this->student)
            ->get(route('student.exams.attempt', $attempt));
        $resp2->assertStatus(200);
        $order2 = [];
        foreach ($attempt->fresh()->attemptQuestions as $aq) {
            $order2[] = $aq->question_id;
        }

        // Order must be identical
        $this->assertEquals($order1, $order2);
    }

    /** @test */
    public function test_randomization_differs_across_different_attempts()
    {
        // Seed RNG to make shuffle deterministic for this test
        srand(42);

        $exam = $this->createExam(['randomize_questions' => true, 'max_attempts' => 2]);
        $q1 = $this->addMcQuestion($exam, 'Q1');
        $q2 = $this->addMcQuestion($exam, 'Q2');
        $q3 = $this->addMcQuestion($exam, 'Q3');

        $service = app(ExamAttemptService::class);
        $attempt1 = $service->start($this->student, $exam);
        $order1 = $attempt1->attemptQuestions->pluck('question_id')->toArray();

        // Second attempt by same student
        $attempt2 = $service->start($this->student, $exam);
        $order2 = $attempt2->attemptQuestions->pluck('question_id')->toArray();

        // Both should be valid permutations of the original
        $originalOrder = [$q1->id, $q2->id, $q3->id];
        sort($order1); sort($order2); sort($originalOrder);
        $this->assertEquals($originalOrder, $order1);
        $this->assertEquals($originalOrder, $order2);

        // With this seed, the two attempts should have DIFFERENT orders
        $this->assertNotEquals($attempt1->attemptQuestions->pluck('question_id')->toArray(),
            $attempt2->attemptQuestions->pluck('question_id')->toArray());
    }

    /** @test */
    public function test_timer_expiration_prevents_further_answers()
    {
        $exam = $this->createExam(['duration_minutes' => 1]); // 1 minute
        $this->addMcQuestion($exam, 'MC?');
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);
        $attempt->load('attemptQuestions.attemptOptions');
        $aq = $attempt->attemptQuestions->first();
        $correctOpt = $aq->attemptOptions->where('is_correct_snapshot', true)->first();

        // Manually expire the attempt
        $attempt->expires_at = now()->subSecond();
        $attempt->save();

        // Attempt to answer after expiry - controller returns 410 Gone
        $resp = $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt), [
                'attempt_question_id' => $aq->id,
                'option_id' => $correctOpt->id,
            ]);

        $resp->assertStatus(410); // Gone - attempt expired
        $resp->assertJsonFragment(['expired' => true]);
    }

    /** @test */
    public function test_expired_attempt_auto_submits_on_access()
    {
        $exam = $this->createExam(['duration_minutes' => 1]);
        $this->addMcQuestion($exam, 'MC?');
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        // Expire the attempt
        $attempt->expires_at = now()->subSecond();
        $attempt->save();

        // Access attempt page - should auto-submit and redirect to results
        $resp = $this->actingAs($this->student)
            ->get(route('student.exams.attempt', $attempt));

        $resp->assertStatus(302); // redirect to results
        $attempt->refresh();
        $this->assertEquals('graded', $attempt->status);
        $this->assertNotNull($attempt->submitted_at);
    }

    /** @test */
    public function test_monitoring_feed_returns_active_attempts()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        $resp = $this->actingAs($this->admin)
            ->getJson(route('admin.monitoring.feed'));

        $resp->assertStatus(200);
        $data = $resp->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('attempts', $data);

        // Find our attempt in the response
        $ourAttempt = collect($data['attempts'])->firstWhere('id', $attempt->id);
        $this->assertNotNull($ourAttempt, 'Our attempt should appear in monitoring feed');
        $this->assertEquals($this->student->name, $ourAttempt['student']);
        $this->assertEquals($exam->title, $ourAttempt['exam']);
        $this->assertEquals(1, $ourAttempt['attempt_number']);
        $this->assertArrayHasKey('remaining_seconds', $ourAttempt);
        $this->assertArrayHasKey('answered', $ourAttempt);
        $this->assertArrayHasKey('total_questions', $ourAttempt);
        $this->assertArrayHasKey('last_activity', $ourAttempt);
    }

    /** @test */
    public function test_monitoring_data_alias_works()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        $resp = $this->actingAs($this->admin)
            ->getJson(route('admin.monitoring.data'));

        $resp->assertStatus(200);
        $data = $resp->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('attempts', $data);

        // Find our attempt in the response
        $ourAttempt = collect($data['attempts'])->firstWhere('id', $attempt->id);
        $this->assertNotNull($ourAttempt, 'Our attempt should appear in monitoring data');
    }

    /** @test */
    public function test_csv_export_contains_expected_columns()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);
        $attempt->load('attemptQuestions.attemptOptions');
        $aq = $attempt->attemptQuestions->first();
        $correctOpt = $aq->attemptOptions->where('is_correct_snapshot', true)->first();

        $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt), [
                'attempt_question_id' => $aq->id,
                'option_id' => $correctOpt->id,
            ]);

        $this->actingAs($this->student)
            ->postJson(route('student.exams.submit', $attempt));

        $resp = $this->actingAs($this->admin)
            ->get(route('admin.results.export'));

        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $content = $resp->streamedContent();
        $this->assertStringContainsString('attempt_id,exam,student,email,attempt_number,status,score,max_score,percentage,started_at,submitted_at,graded_at', $content);
        $this->assertStringContainsString($this->student->name, $content);
        $this->assertStringContainsString($exam->title, $content);
    }

    /** @test */
    public function test_checkbox_unchecked_saves_false_for_show_correct_answers()
    {
        $exam = $this->createExam(['show_correct_answers' => true]);

        // Unchecked checkbox = field absent in request
        $payload = [
            'title' => 'Updated Exam',
            'description' => 'Desc',
            'instructions' => 'Instr',
            'duration_minutes' => 60,
            'max_attempts' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'show_result' => true,
            // 'show_correct_answers' intentionally omitted (unchecked)
            'starts_at' => now()->subMinute()->toDateTimeString(),
            'ends_at' => now()->addHour()->toDateTimeString(),
            'published_at' => now()->subMinute()->toDateTimeString(),
        ];

        $resp = $this->actingAs($this->admin)
            ->put(route('admin.exams.update', $exam), $payload);

        $resp->assertStatus(302); // redirect on success
        $exam->refresh();
        $this->assertFalse($exam->show_correct_answers);
    }

    /** @test */
    public function test_checkbox_checked_saves_true_for_show_correct_answers()
    {
        $exam = $this->createExam(['show_correct_answers' => false]);

        // Checked checkbox = "on" in request
        $payload = [
            'title' => 'Updated Exam',
            'description' => 'Desc',
            'instructions' => 'Instr',
            'duration_minutes' => 60,
            'max_attempts' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'show_result' => true,
            'show_correct_answers' => 'on', // checked
            'starts_at' => now()->subMinute()->toDateTimeString(),
            'ends_at' => now()->addHour()->toDateTimeString(),
            'published_at' => now()->subMinute()->toDateTimeString(),
        ];

        $resp = $this->actingAs($this->admin)
            ->put(route('admin.exams.update', $exam), $payload);

        $resp->assertStatus(302);
        $exam->refresh();
        $this->assertTrue($exam->show_correct_answers);
    }

    /** @test */
    public function test_autosave_upserts_prevents_duplicate_answers()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam, 'MC?');
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);
        $attempt->load('attemptQuestions.attemptOptions');
        $aq = $attempt->attemptQuestions->first();
        $correctOpt = $aq->attemptOptions->where('is_correct_snapshot', true)->first();

        // First save
        $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt), [
                'attempt_question_id' => $aq->id,
                'option_id' => $correctOpt->id,
            ]);

        // Second save (same question) - should update, not insert duplicate
        $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt), [
                'attempt_question_id' => $aq->id,
                'option_id' => $correctOpt->id,
            ]);

        $this->assertDatabaseCount('answers', 1);
    }

    /** @test */
    public function test_multiple_attempts_independent_history()
    {
        $exam = $this->createExam(['max_attempts' => 3]);
        $this->addMcQuestion($exam, 'Q1');
        $this->addMcQuestion($exam, 'Q2');
        $service = app(ExamAttemptService::class);

        // Attempt 1
        $attempt1 = $service->start($this->student, $exam);
        $attempt1->load('attemptQuestions.attemptOptions');
        foreach ($attempt1->attemptQuestions as $aq) {
            $opt = $aq->attemptOptions->where('is_correct_snapshot', true)->first();
            $this->actingAs($this->student)
                ->postJson(route('student.exams.answer', $attempt1), [
                    'attempt_question_id' => $aq->id,
                    'option_id' => $opt->id,
                ]);
        }
        $this->actingAs($this->student)
            ->postJson(route('student.exams.submit', $attempt1));

        // Attempt 2
        $attempt2 = $service->start($this->student, $exam);
        $attempt2->load('attemptQuestions.attemptOptions');
        foreach ($attempt2->attemptQuestions as $aq) {
            $opt = $aq->attemptOptions->where('is_correct_snapshot', false)->first(); // wrong answers
            $this->actingAs($this->student)
                ->postJson(route('student.exams.answer', $attempt2), [
                    'attempt_question_id' => $aq->id,
                    'option_id' => $opt->id,
                ]);
        }
        $this->actingAs($this->student)
            ->postJson(route('student.exams.submit', $attempt2));

        // Both attempts exist with different scores
        $this->assertDatabaseCount('exam_attempts', 2);
        $attempt1->refresh();
        $attempt2->refresh();
        $this->assertEquals(20, $attempt1->score); // 2 correct * 10
        $this->assertEquals(0, $attempt2->score);  // 2 wrong
        $this->assertEquals(1, $attempt1->attempt_number);
        $this->assertEquals(2, $attempt2->attempt_number);
    }

    /** @test */
    public function test_student_results_index_shows_only_own_attempts()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);
        $attempt->load('attemptQuestions.attemptOptions');
        $aq = $attempt->attemptQuestions->first();
        $correctOpt = $aq->attemptOptions->where('is_correct_snapshot', true)->first();

        $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt), [
                'attempt_question_id' => $aq->id,
                'option_id' => $correctOpt->id,
            ]);
        $this->actingAs($this->student)
            ->postJson(route('student.exams.submit', $attempt));

        $resp = $this->actingAs($this->student)
            ->get(route('student.exams.results.index'));

        $resp->assertStatus(200);
        $resp->assertSee($exam->title);
        $resp->assertSee('10'); // score
    }
}
