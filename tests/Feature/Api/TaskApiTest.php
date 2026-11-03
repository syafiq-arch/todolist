<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can fetch only their own tasks', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $taskA1 = Task::create([
        'user_id' => $userA->id,
        'title' => 'Tugas User A 1',
        'status' => 'todo',
    ]);
    $taskA2 = Task::create([
        'user_id' => $userA->id,
        'title' => 'Tugas User A 2',
        'status' => 'done',
    ]);
    $taskB = Task::create([
        'user_id' => $userB->id,
        'title' => 'Tugas User B',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($userA);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['title' => 'Tugas User A 1'])
        ->assertJsonFragment(['title' => 'Tugas User A 2'])
        ->assertJsonMissing(['title' => 'Tugas User B']);
});

test('authenticated user can create a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'title' => 'Belajar TDD Laravel',
        'deadline' => '2026-11-10',
    ];

    $response = $this->postJson('/api/tasks', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Tugas berhasil dibuat',
            'data' => [
                'user_id' => $user->id,
                'title' => 'Belajar TDD Laravel',
                'deadline' => '2026-11-10',
                'status' => 'todo',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'user_id' => $user->id,
        'title' => 'Belajar TDD Laravel',
        'status' => 'todo',
    ]);
});

test('task creation requires a title', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/tasks', [
        'deadline' => '2026-11-10',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('authenticated user can view their task detail', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Saya',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Detail tugas berhasil diambil',
            'data' => [
                'id' => $task->id,
                'title' => 'Tugas Saya',
            ],
        ]);
});

test('user cannot view another users task (forbidden 403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $taskB = Task::create([
        'user_id' => $userB->id,
        'title' => 'Rahasia User B',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($userA);

    $response = $this->getJson("/api/tasks/{$taskB->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Bukan tugas milikmu',
        ]);
});

test('authenticated user can update their task', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Judul Lama',
        'deadline' => '2026-11-05',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'title' => 'Judul Baru',
        'deadline' => '2026-11-08',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Tugas berhasil diperbarui',
            'data' => [
                'title' => 'Judul Baru',
                'deadline' => '2026-11-08',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Judul Baru',
    ]);
});

test('user cannot update another users task (forbidden 403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $taskB = Task::create([
        'user_id' => $userB->id,
        'title' => 'Tugas B',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($userA);

    $response = $this->putJson("/api/tasks/{$taskB->id}", [
        'title' => 'Mencoba Ubah',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Bukan tugas milikmu',
        ]);
});

test('authenticated user can update task status via patch status endpoint', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Kerjaan',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/tasks/{$task->id}/status", [
        'status' => 'done',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Status tugas berhasil diperbarui',
            'data' => [
                'id' => $task->id,
                'status' => 'done',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'done',
    ]);
});

test('updating task status requires valid enum value (todo or done)', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Kerjaan',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/tasks/{$task->id}/status", [
        'status' => 'pending',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('authenticated user can delete their task', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Hapus',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Tugas berhasil dihapus',
        ]);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

test('user cannot delete another users task (forbidden 403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $taskB = Task::create([
        'user_id' => $userB->id,
        'title' => 'Tugas B',
        'status' => 'todo',
    ]);

    Sanctum::actingAs($userA);

    $response = $this->deleteJson("/api/tasks/{$taskB->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Bukan tugas milikmu',
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $taskB->id,
    ]);
});

test('unauthenticated user cannot access tasks endpoints', function () {
    $this->getJson('/api/tasks')->assertStatus(401);
    $this->postJson('/api/tasks', ['title' => 'Test'])->assertStatus(401);
    $this->getJson('/api/tasks/1')->assertStatus(401);
    $this->putJson('/api/tasks/1', ['title' => 'Test'])->assertStatus(401);
    $this->patchJson('/api/tasks/1/status', ['status' => 'done'])->assertStatus(401);
    $this->deleteJson('/api/tasks/1')->assertStatus(401);
});
