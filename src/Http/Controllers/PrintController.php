<?php

namespace BitDreamIT\QzTray\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use BitDreamIT\QzTray\Services\PrintService;
use BitDreamIT\QzTray\Models\PrintJob;

class PrintController extends Controller
{
    protected $printService;

    public function __construct(PrintService $printService)
    {
        $this->printService = $printService;
    }

    /**
     * Print raw text
     */
    public function raw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'content' => 'required|string',
            'copies' => 'integer|min:1|max:100',
            'paper_size' => 'string|in:A4,A5,Letter,Legal,Receipt',
            'orientation' => 'string|in:portrait,landscape',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printRaw(
                $request->input('printer'),
                $request->input('content'),
                $request->only(['copies', 'paper_size', 'orientation'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Print job queued successfully',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                    'created_at' => $job->created_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print HTML
     */
    public function html(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'html' => 'required|string',
            'copies' => 'integer|min:1|max:100',
            'paper_size' => 'string|in:A4,A5,Letter,Legal',
            'orientation' => 'string|in:portrait,landscape',
            'margins' => 'string|regex:/^\d+(,\d+){0,3}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printHtml(
                $request->input('printer'),
                $request->input('html'),
                $request->only(['copies', 'paper_size', 'orientation', 'margins'])
            );

            return response()->json([
                'success' => true,
                'message' => 'HTML print job queued',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print PDF
     */
    public function pdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'pdf_url' => 'required|url',
            'copies' => 'integer|min:1|max:100',
            'page_range' => 'nullable|string|regex:/^(\d+(-\d+)?)(,\d+(-\d+)?)*$/',
            'orientation' => 'string|in:portrait,landscape',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printPdf(
                $request->input('printer'),
                $request->input('pdf_url'),
                $request->only(['copies', 'page_range', 'orientation'])
            );

            return response()->json([
                'success' => true,
                'message' => 'PDF print job queued',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print image
     */
    public function image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'image_url' => 'required|url',
            'copies' => 'integer|min:1|max:100',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'dpi' => 'nullable|integer|min:72|max:1200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printImage(
                $request->input('printer'),
                $request->input('image_url'),
                $request->only(['copies', 'width', 'height', 'dpi'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Image print job queued',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print ZPL
     */
    public function zpl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'zpl' => 'required|string',
            'copies' => 'integer|min:1|max:100',
            'label_width' => 'nullable|numeric|min:1',
            'label_height' => 'nullable|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printZpl(
                $request->input('printer'),
                $request->input('zpl'),
                $request->only(['copies', 'label_width', 'label_height'])
            );

            return response()->json([
                'success' => true,
                'message' => 'ZPL print job queued',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print ESC/POS
     */
    public function escpos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'commands' => 'required|string',
            'copies' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printEscpos(
                $request->input('printer'),
                $request->input('commands'),
                $request->only(['copies'])
            );

            return response()->json([
                'success' => true,
                'message' => 'ESC/POS print job queued',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print barcode
     */
    public function barcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer' => 'required|string',
            'data' => 'required|string',
            'type' => 'required|string|in:CODE128,CODE39,EAN13,EAN8,UPCA,UPCE,ITF14',
            'width' => 'nullable|numeric|min:1|max:10',
            'height' => 'nullable|numeric|min:10|max:500',
            'human_readable' => 'nullable|boolean',
            'copies' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = $this->printService->printBarcode(
                $request->input('printer'),
                $request->input('data'),
                $request->input('type'),
                $request->only(['width', 'height', 'human_readable', 'copies'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Barcode print job queued',
                'data' => [
                    'job_id' => $job->id,
                    'status' => $job->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get print jobs
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);
        $userId = $request->input('user_id');
        $status = $request->input('status');

        $jobs = $this->printService->getJobHistory($limit, $userId);

        if ($status) {
            $jobs = $jobs->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $jobs,
            'count' => $jobs->count(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get print job details
     */
    public function show(Request $request, string $id)
    {
        $job = PrintJob::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Get queue status
     */
    public function queueStatus(Request $request)
    {
        $status = $this->printService->getQueueStatus();

        return response()->json([
            'success' => true,
            'data' => $status,
            'timestamp' => now(),
        ]);
    }

    /**
     * Cancel print job
     */
    public function cancel(Request $request, string $id)
    {
        $job = PrintJob::findOrFail($id);

        // Only cancel if still queued or processing
        if (!in_array($job->status, ['queued', 'processing'])) {
            return response()->json([
                'success' => false,
                'error' => 'Job cannot be cancelled in its current state',
            ], 400);
        }

        $job->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Print job cancelled',
            'data' => [
                'job_id' => $job->id,
                'status' => $job->status,
            ],
        ]);
    }
}
