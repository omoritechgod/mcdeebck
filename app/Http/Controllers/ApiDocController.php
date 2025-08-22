<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="McDee Platform API",
 *         description="Multifunctional Marketplace & Services API",
 *         @OA\Contact(
 *             email="support@mcdee.test"
 *         )
 *     ),
 *     @OA\Server(
 *         url="http://127.0.0.1:8000",
 *         description="Local Server"
 *     )
 * )
 */
class ApiDocController extends Controller {}
