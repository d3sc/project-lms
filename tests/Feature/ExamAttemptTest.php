<?php

namespace Tests\Feature;

use App\Models\AttemptQuestion;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\ExamAttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAttemptTest extends TestCase
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

    protected function addEssayQuestion(Exam $exam, string $content = 'Essay?'): Question
    {
        return Question::create([
            'exam_id' => $exam->id,
            'type' => 'essay',
            'content' => $content,
            'points' => 20,
            'order' => 2,
        ]);
    }

    public function test_start_exam_creates_attempt_with_snapshots_and_correct_fk()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam, 'Q1');
        $this->addMcQuestion($exam, 'Q2');

        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        $this->assertEquals($this->student->id, $attempt->user_id);
        $this->assertEquals($exam->id, $attempt->exam_id);
        $this->assertEquals('in_progress', $attempt->status);
        $this->assertEquals(1, $attempt->attempt_number);

        // snapshot FK uses attempt_id (NOT exam_attempt_id)
        $snapshots = AttemptQuestion::where('attempt_id', $attempt->id)->get();
        $this->assertCount(2, $snapshots);
        foreach ($snapshots as $sq) {
            $this->assertEquals($attempt->id, $sq->attempt_id);
            $this->assertNotNull($sq->question_snapshot);
            $this->assertGreaterThan(0, $sq->attemptOptions->count());
            foreach ($sq->attemptOptions as $opt) {
                $this->assertEquals($sq->id, $opt->attempt_question_id);
            }
        }
    }

    public function test_attempt_page_renders_without_error()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);

        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        $this->actingAs($this->student)
            ->get(route('student.exams.attempt', $attempt))
            ->assertStatus(200)
            ->assertSee($exam->title);
    }

    public function test_autosave_mc_answer_stores_attempt_question_option_id()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam, 'MC?');
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        $attempt->load('attemptQuestions.attemptOptions');
        $aq = $attempt->attemptQuestions->first();
        $correctOpt = $aq->attemptOptions->where('is_correct_snapshot', true)->first();

        $resp = $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt), [
                'attempt_question_id' => $aq->id,
                'option_id' => $correctOpt->id,
            ]);

        $resp->assertStatus(200)->assertJson(['saved' => true]);

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'attempt_question_id' => $aq->id,
            'attempt_question_option_id' => $correctOpt->id,
            'essay_answer' => null,
        ]);
    }

    public function test_autosave_rejects_option_from_different_attempt_idor()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);
        $service = app(ExamAttemptService::class);

        $attempt1 = $service->start($this->student, $exam);
        $attempt1->load('attemptQuestions.attemptOptions');
        $aq1 = $attempt1->attemptQuestions->first();
        $opt1 = $aq1->attemptOptions->first();

        // Another student creates second attempt for same exam
        $student2 = User::factory()->create(['role' => 'student', 'is_active' => true, 'email' => 's2@test.local', 'password' => bcrypt('p')]);
        $attempt2 = $service->start($student2, $exam);
        $attempt2->load('attemptQuestions.attemptOptions');
        $aq2 = $attempt2->attemptQuestions->first();
        $opt2 = $aq2->attemptOptions->first();

        // Student 1 tries to submit option from attempt2
        $resp = $this->actingAs($this->student)
            ->postJson(route('student.exams.answer', $attempt1), [
                'attempt_question_id' => $aq1->id,
                'option_id' => $opt2->id, // belongs to different attempt
            ]);

        $resp->assertStatus(404); // option belongs to another attempt -> model not found
    }

    public function test_submit_grades_mc_automatically_and_sets_status_graded()
    {
        $exam = $this->createExam(['duration_minutes' => null]); // no timer
        $this->addMcQuestion($exam, 'MC?');
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

        $resp = $this->actingAs($this->student)
            ->postJson(route('student.exams.submit', $attempt));

        $resp->assertStatus(200)->assertJson(['submitted' => true]);
        $attempt->refresh();
        $this->assertEquals('graded', $attempt->status);
        $this->assertEquals($exam->totalPoints(), $attempt->score); // 10 pts
    }

    public function test_other_user_cannot_access_attempt_403()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);
        $service = app(ExamAttemptService::class);
        $attempt = $service->start($this->student, $exam);

        $other = User::factory()->create(['role' => 'student', 'is_active' => true, 'email' => 'other@test.local', 'password' => bcrypt('p')]);

        $this->actingAs($other)
            ->get(route('student.exams.attempt', $attempt))
            ->assertStatus(403);
    }

    public function test_post_start_returns_302_to_attempt_page()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);

        $response = $this->actingAs($this->student)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::where('user_id', $this->student->id)
            ->where('exam_id', $exam->id)->firstOrFail();

        $response->assertStatus(302)
            ->assertRedirect(route('student.exams.attempt', $attempt));

        $this->assertSame(1, ExamAttempt::where('user_id', $this->student->id)
            ->where('exam_id', $exam->id)->count(), 'start must not create duplicates');
    }

    public function test_show_redirects_to_active_attempt_resume()
    {
        $exam = $this->createExam();
        $this->addMcQuestion($exam);

        $this->actingAs($this->student)->post(route('student.exams.start', $exam));
        $attempt = ExamAttempt::where('user_id', $this->student->id)
            ->where('exam_id', $exam->id)->firstOrFail();

        // Refresh show page -> should redirect to the same active attempt
        $this->actingAs($this->student)
            ->get(route('student.exams.show', $exam))
            ->assertStatus(302)
            ->assertRedirect(route('student.exams.attempt', $attempt));
    }

    public function test_start_exam_not_open_returns_404()
    {
        $exam = $this->createExam([
            'starts_at' => now()->addHour(), // future
            'ends_at' => now()->addDay(),
        ]);
        $this->addMcQuestion($exam);

        $this->actingAs($this->student)
            ->post(route('student.exams.start', $exam))
            ->assertStatus(404);
    }

    public function test_show_blocks_when_max_attempts_reached()
    {
        $exam = $this->createExam(['max_attempts' => 1]);
        $this->addMcQuestion($exam);

        $attempt = app(ExamAttemptService::class)->start($this->student, $exam);
        $attempt->update(['status' => 'graded']);

        $this->actingAs($this->student)
            ->get(route('student.exams.show', $exam))
            ->assertStatus(403);
    }
}
