<?php

// app/Http/Controllers/Api/MaintenanceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Auto Maintenance")
 */
class MaintenanceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/maintenance/request",
     *     summary="Submit a vehicle maintenance request",
     *     tags={"Auto Maintenance"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location", type="string", example="123 Main Street, Lagos"),
     *             @OA\Property(property="service_type", type="string", example="Engine Repair"),
     *             @OA\Property(property="issue", type="string", example="Car won’t start and makes noise"),
     *             @OA\Property(property="needs_towing", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Request created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function submitRequest(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string',
            'service_type' => 'required|string',
            'issue' => 'required|string',
            'needs_towing' => 'required|boolean',
        ]);

        $validated['user_id'] = Auth::id();

        $request = MaintenanceRequest::create($validated);

        return response()->json([
            'message' => 'Maintenance request submitted successfully',
            'data' => $request
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance/my-requests",
     *     summary="Get logged-in user's maintenance requests",
     *     tags={"Auto Maintenance"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of maintenance requests")
     * )
     */
    public function myRequests()
    {
        $requests = MaintenanceRequest::where('user_id', Auth::id())->latest()->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/maintenance/update-status",
     *     summary="Update the status of a maintenance request",
     *     tags={"Auto Maintenance"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="request_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"accepted", "completed", "cancelled"}, example="accepted")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated"),
     *     @OA\Response(response=404, description="Request not found")
     * )
     */
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:maintenance_requests,id',
            'status' => 'required|in:accepted,completed,cancelled',
        ]);

        $maintenance = MaintenanceRequest::find($validated['request_id']);
        $maintenance->status = $validated['status'];

        if ($validated['status'] === 'accepted') {
            $maintenance->accepted_at = now();
        }

        if ($validated['status'] === 'completed') {
            $maintenance->completed_at = now();
        }

        $maintenance->save();

        return response()->json([
            'message' => 'Status updated',
            'data' => $maintenance
        ]);
    }
}
