<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    #[OA\Get(
        path: '/api/tasks',
        operationId: 'getTaskList',
        summary: 'Mendapatkan seluruh daftar tugas milik pengguna yang sedang login',
        description: 'Mengembalikan semua tugas yang dimiliki oleh pengguna yang sedang terautentikasi.',
        tags: ['Tugas (To-Do)'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Berhasil mengambil daftar tugas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Daftar tugas berhasil diambil'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: 'Mengerjakan tugas LKPD Back-End'),
                                    new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2026-11-05'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['todo', 'done'], example: 'todo'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Belum login / token tidak valid',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->latest()->get();

        return response()->json([
            'message' => 'Daftar tugas berhasil diambil',
            'data' => $tasks,
        ]);
    }

    #[OA\Post(
        path: '/api/tasks',
        operationId: 'createTask',
        summary: 'Menambahkan tugas baru',
        description: "Endpoint untuk membuat tugas to-do baru. Status otomatis bernilai 'todo'.",
        tags: ['Tugas (To-Do)'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data tugas baru',
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Belajar membuat Swagger API'),
                    new OA\Property(property: 'deadline', type: 'string', format: 'date', nullable: true, example: '2026-11-05'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tugas berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Tugas berhasil dibuat'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Belajar membuat Swagger API'),
                                new OA\Property(property: 'deadline', type: 'string', format: 'date', nullable: true, example: '2026-11-05'),
                                new OA\Property(property: 'status', type: 'string', example: 'todo'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi data tugas gagal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Belum login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date',
        ]);

        $task = $request->user()->tasks()->create([
            'title' => $data['title'],
            'deadline' => $data['deadline'] ?? null,
            'status' => 'todo',
        ]);

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data' => $task,
        ], 201);
    }

    #[OA\Get(
        path: '/api/tasks/{task}',
        operationId: 'getTaskDetail',
        summary: 'Melihat detail tugas',
        description: 'Mengambil detail tugas berdasarkan ID. Hanya pemilik tugas yang berhak melihat.',
        tags: ['Tugas (To-Do)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'task',
                description: 'ID tugas',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail tugas berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Detail tugas berhasil diambil'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Mengerjakan tugas LKPD Back-End'),
                                new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2026-11-05'),
                                new OA\Property(property: 'status', type: 'string', example: 'todo'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Bukan pemilik tugas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Bukan tugas milikmu'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Tugas tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Task] 1'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Belum login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function show(Request $request, Task $task)
    {
        $this->ownerOnly($request, $task);

        return response()->json([
            'message' => 'Detail tugas berhasil diambil',
            'data' => $task,
        ]);
    }

    #[OA\Put(
        path: '/api/tasks/{task}',
        operationId: 'updateTask',
        summary: 'Memperbarui data tugas (judul/tenggat/status)',
        description: 'Endpoint untuk memperbarui judul, tenggat waktu (deadline), atau status tugas.',
        tags: ['Tugas (To-Do)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'task',
                description: 'ID tugas yang akan diperbarui',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data tugas yang diperbarui',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Revisi Laporan Praktikum RPL'),
                    new OA\Property(property: 'deadline', type: 'string', format: 'date', nullable: true, example: '2026-11-06'),
                    new OA\Property(property: 'status', type: 'string', enum: ['todo', 'done'], example: 'done'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tugas berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Tugas berhasil diperbarui'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Revisi Laporan Praktikum RPL'),
                                new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2026-11-06'),
                                new OA\Property(property: 'status', type: 'string', example: 'done'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Bukan pemilik tugas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Bukan tugas milikmu'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi gagal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Belum login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function update(Request $request, Task $task)
    {
        $this->ownerOnly($request, $task);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|required|in:todo,done',
        ]);

        $task->update($data);

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
            'data' => $task->fresh(),
        ]);
    }

    #[OA\Patch(
        path: '/api/tasks/{task}/status',
        operationId: 'updateTaskStatus',
        summary: 'Mengubah status tugas (todo / done)',
        description: 'Endpoint khusus untuk mengubah status tugas menjadi selesai (done) atau belum selesai (todo).',
        tags: ['Tugas (To-Do)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'task',
                description: 'ID tugas yang statusnya akan diubah',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Status baru tugas',
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['todo', 'done'], example: 'done'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Status tugas berhasil diperbarui'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Mengerjakan tugas LKPD Back-End'),
                                new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2026-11-05'),
                                new OA\Property(property: 'status', type: 'string', example: 'done'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Bukan pemilik tugas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Bukan tugas milikmu'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi status gagal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The selected status is invalid.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Belum login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function updateStatus(Request $request, Task $task)
    {
        $this->ownerOnly($request, $task);

        $data = $request->validate([
            'status' => 'required|in:todo,done',
        ]);

        $task->update($data);

        return response()->json([
            'message' => 'Status tugas berhasil diperbarui',
            'data' => $task->fresh(),
        ]);
    }

    #[OA\Delete(
        path: '/api/tasks/{task}',
        operationId: 'deleteTask',
        summary: 'Menghapus tugas',
        description: 'Endpoint untuk menghapus tugas berdasarkan ID. Hanya pemilik yang dapat menghapus tugas miliknya.',
        tags: ['Tugas (To-Do)'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'task',
                description: 'ID tugas yang akan dihapus',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tugas berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Tugas berhasil dihapus'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Bukan pemilik tugas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Bukan tugas milikmu'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Tugas tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Task] 1'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Belum login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function destroy(Request $request, Task $task)
    {
        $this->ownerOnly($request, $task);

        $task->delete();

        return response()->json([
            'message' => 'Tugas berhasil dihapus',
        ]);
    }

    /**
     * Pastikan hanya pemilik tugas yang dapat mengakses/mengubah data.
     */
    private function ownerOnly(Request $request, Task $task): void
    {
        if ($task->user_id !== $request->user()->id) {
            abort(response()->json([
                'message' => 'Bukan tugas milikmu',
            ], 403));
        }
    }
}