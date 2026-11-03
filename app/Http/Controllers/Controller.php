<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'To-Do List API Documentation',
    description: 'Dokumentasi resmi To-Do List API untuk proyek kolaborasi Duo Developer (BE & FE). Dibuat menggunakan Laravel Sanctum dan L5-Swagger.',
    contact: new OA\Contact(
        name: 'Tim Pengembang SMK PESAT RPL',
        email: 'developer@pesat.sch.id'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'API Server Utama'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token',
    description: "Masukkan token Sanctum Anda di sini. Cukup ketik token Anda (Swagger akan otomatis menyematkan header 'Bearer {token}')."
)]
abstract class Controller
{
    //
}
