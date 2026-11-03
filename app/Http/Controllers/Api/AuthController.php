<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        operationId: 'registerUser',
        summary: 'Mendaftarkan akun baru',
        description: 'Endpoint untuk registrasi akun pengguna baru. Membutuhkan nama, email unik, password minimal 8 karakter, dan konfirmasi password.',
        tags: ['Autentikasi'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data pengguna yang akan didaftarkan',
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registrasi berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Register berhasil'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'email', type: 'string', example: 'budi@example.com'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi input gagal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The email has already been taken.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);

        return response()->json([
            'message' => 'Register berhasil',
            'data' => $user,
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        operationId: 'loginUser',
        summary: 'Masuk ke aplikasi (Login)',
        description: 'Endpoint untuk login pengguna dan menghasilkan token otentikasi Sanctum.',
        tags: ['Autentikasi'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Kredensial login',
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Login berhasil'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'token', type: 'string', example: '1|2abcde345fghij678klmnop...'),
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                                        new OA\Property(property: 'email', type: 'string', example: 'budi@example.com'),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Kredensial tidak valid',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Email atau password salah'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi input gagal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The email field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/profile',
        operationId: 'getUserProfile',
        summary: 'Melihat data profil pengguna login',
        description: 'Endpoint untuk mendapatkan data akun pengguna yang sedang login.',
        tags: ['Autentikasi'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data profil berhasil didapatkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Data profil berhasil diambil'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'email', type: 'string', example: 'budi@example.com'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-11-03T00:00:00.000000Z'),
                            ]
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
    public function profile(Request $request)
    {
        return response()->json([
            'message' => 'Data profil berhasil diambil',
            'data' => $request->user(),
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'logoutUser',
        summary: 'Keluar dari aplikasi (Logout)',
        description: 'Endpoint untuk mencabut dan menghapus access token pengguna yang sedang login.',
        tags: ['Autentikasi'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logout berhasil'),
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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}