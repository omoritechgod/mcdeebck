<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorLogController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->only(['context', 'message', 'stack', 'extra']);

        Log::error('Frontend Error', [
            'context' => $data['context'] ?? 'unknown',
            'message' => $data['message'],
            'stack'   => $data['stack'] ?? null,
            'extra'   => $data['extra'] ?? [],
            'user_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Error logged successfully'], 201);
    }
}
