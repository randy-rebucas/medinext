<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AuditLog",
 *     type="object",
 *     title="Audit Log",
 *     description="Audit log entry",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="action", type="string", example="created"),
 *     @OA\Property(property="model", type="string", example="Patient"),
 *     @OA\Property(property="model_id", type="integer", example=1),
 *     @OA\Property(property="old_values", type="object", nullable=true),
 *     @OA\Property(property="new_values", type="object", nullable=true),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ComplianceReport",
 *     type="object",
 *     title="Compliance Report",
 *     description="Compliance report data",
 *     @OA\Property(property="total_audit_entries", type="integer", example=1500),
 *     @OA\Property(property="data_access_logs", type="integer", example=800),
 *     @OA\Property(property="modification_logs", type="integer", example=500),
 *     @OA\Property(property="authentication_logs", type="integer", example=200),
 *     @OA\Property(property="compliance_score", type="number", format="float", example=95.5),
 *     @OA\Property(property="last_audit_date", type="string", format="date-time"),
 *     @OA\Property(property="violations", type="array", @OA\Items(type="string"))
 * )
 */



class AuditController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/audit/logs",
     *     summary="Get audit logs",
     *     description="Retrieve comprehensive audit logs with filtering options",
     *     operationId="getAuditLogs",
     *     tags={"Audit & Compliance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action type",
     *         required=false,
     *         @OA\Schema(type="string", example="created")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         description="Filter by model type",
     *         required=false,
     *         @OA\Schema(type="string", example="Patient")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter logs from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter logs to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Audit logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Audit logs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function logs(Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::with(['user']);

            // Apply filters
            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            if ($request->has('action')) {
                $query->where('action', $request->get('action'));
            }

            if ($request->has('model')) {
                $query->where('module', $request->get('model'));
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->get('date_to'));
            }

            $perPage = $request->get('per_page', 15);
            $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse($logs, 'Audit logs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve audit logs: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/audit/export",
     *     summary="Export audit logs",
     *     description="Export audit logs to CSV or Excel format",
     *     operationId="exportAuditLogs",
     *     tags={"Audit & Compliance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Export format",
     *         required=false,
     *         @OA\Schema(type="string", enum={"csv", "xlsx"}, example="csv")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Export logs from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Export logs to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Audit logs exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Audit logs exported successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="download_url", type="string", example="/api/audit/export/download/123"),
     *                 @OA\Property(property="file_name", type="string", example="audit_logs_2024-01-15.csv"),
     *                 @OA\Property(property="records_count", type="integer", example=1500)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'csv');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $userId = $request->get('user_id');

            // Build query for export
            $query = ActivityLog::with(['user']);

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            }

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $recordsCount = $query->count();

            // Implementation for export would go here
            // This is a placeholder response
            $result = [
                'download_url' => "/api/audit/export/download/" . uniqid(),
                'file_name' => "audit_logs_" . date('Y-m-d') . ".{$format}",
                'records_count' => $recordsCount
            ];

            return $this->successResponse($result, 'Audit logs exported successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to export audit logs: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/audit/compliance",
     *     summary="Get compliance report",
     *     description="Retrieve compliance report and audit statistics",
     *     operationId="getComplianceReport",
     *     tags={"Audit & Compliance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Report period from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Report period to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compliance report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compliance report retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function compliance(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            // Build base query
            $query = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo]);

            // Calculate compliance metrics
            $totalAuditEntries = $query->count();
            $dataAccessLogs = $query->clone()->where('action', 'viewed')->count();
            $modificationLogs = $query->clone()->whereIn('action', ['created', 'updated', 'deleted'])->count();
            $authenticationLogs = $query->clone()->where('module', 'auth')->count();

            // Calculate compliance score (placeholder logic)
            $complianceScore = min(100, max(0, 100 - ($totalAuditEntries > 0 ? 0 : 5)));

            $report = [
                'total_audit_entries' => $totalAuditEntries,
                'data_access_logs' => $dataAccessLogs,
                'modification_logs' => $modificationLogs,
                'authentication_logs' => $authenticationLogs,
                'compliance_score' => $complianceScore,
                'last_audit_date' => now()->toISOString(),
                'violations' => $complianceScore < 90 ? ['Low compliance score detected'] : []
            ];

            return $this->successResponse($report, 'Compliance report retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve compliance report: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/audit/security",
     *     summary="Get security audit",
     *     description="Retrieve security-related audit information and alerts",
     *     operationId="getSecurityAudit",
     *     tags={"Audit & Compliance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Security audit from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Security audit to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Security audit retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Security audit retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="failed_login_attempts", type="integer", example=5),
     *                 @OA\Property(property="suspicious_activities", type="integer", example=2),
     *                 @OA\Property(property="data_breach_attempts", type="integer", example=0),
     *                 @OA\Property(property="privilege_escalations", type="integer", example=1),
     *                 @OA\Property(property="security_score", type="number", format="float", example=85.5),
     *                 @OA\Property(property="alerts", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="last_security_scan", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function security(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->subWeek()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            // Build security audit query
            $query = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo]);

            // Calculate security metrics
            $failedLoginAttempts = $query->clone()->where('module', 'auth')->where('action', 'failed_login')->count();
            $suspiciousActivities = $query->clone()->where('description', 'like', '%suspicious%')->count();
            $dataBreachAttempts = $query->clone()->where('description', 'like', '%breach%')->count();
            $privilegeEscalations = $query->clone()->where('action', 'permission_granted')->count();

            // Calculate security score
            $securityScore = max(0, 100 - ($failedLoginAttempts * 2) - ($suspiciousActivities * 5) - ($dataBreachAttempts * 10) - ($privilegeEscalations * 3));

            $alerts = [];
            if ($failedLoginAttempts > 10) {
                $alerts[] = 'High number of failed login attempts detected';
            }
            if ($suspiciousActivities > 0) {
                $alerts[] = 'Suspicious activities detected';
            }
            if ($dataBreachAttempts > 0) {
                $alerts[] = 'Potential data breach attempts detected';
            }

            $securityAudit = [
                'failed_login_attempts' => $failedLoginAttempts,
                'suspicious_activities' => $suspiciousActivities,
                'data_breach_attempts' => $dataBreachAttempts,
                'privilege_escalations' => $privilegeEscalations,
                'security_score' => $securityScore,
                'alerts' => $alerts,
                'last_security_scan' => now()->toISOString()
            ];

            return $this->successResponse($securityAudit, 'Security audit retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve security audit: ' . $e->getMessage());
        }
    }
}
