<?php

namespace BitDreamIT\QzTray\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use BitDreamIT\QzTray\Models\PrintJob;
use BitDreamIT\QzTray\Events\PrintJobQueued;

class PrintService
{
    /**
     * Queue a print job
     */
    public function queueJob(array $data)
    {
        $jobData = $this->validatePrintJob($data);

        // Create print job record
        $printJob = PrintJob::create([
            'user_id' => auth()->id(),
            'printer' => $jobData['printer'],
            'type' => $jobData['type'],
            'data' => json_encode($jobData['data']),
            'options' => json_encode($jobData['options'] ?? []),
            'status' => 'queued',
            'ip_address' => request()->ip(),
        ]);

        // Dispatch event
        event(new PrintJobQueued($printJob));

        // Log the job
        Log::info('Print job queued', [
            'job_id' => $printJob->id,
            'type' => $printJob->type,
            'printer' => $printJob->printer,
            'user' => auth()->user()->name ?? 'anonymous',
        ]);

        return $printJob;
    }

    /**
     * Validate print job data
     */
    protected function validatePrintJob(array $data)
    {
        $required = ['printer', 'type', 'data'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        $validTypes = ['raw', 'html', 'pdf', 'image', 'zpl', 'escpos'];

        if (!in_array($data['type'], $validTypes)) {
            throw new \InvalidArgumentException("Invalid print type: {$data['type']}");
        }

        return [
            'printer' => $data['printer'],
            'type' => $data['type'],
            'data' => $data['data'],
            'options' => $data['options'] ?? [],
            'copies' => $data['copies'] ?? 1,
            'paper_size' => $data['paper_size'] ?? config('qz-tray.print.default_paper_size'),
            'orientation' => $data['orientation'] ?? config('qz-tray.print.default_orientation'),
        ];
    }

    /**
     * Print raw text
     */
    public function printRaw(string $printer, string $text, array $options = [])
    {
        return $this->queueJob([
            'printer' => $printer,
            'type' => 'raw',
            'data' => $text,
            'options' => $options,
        ]);
    }

    /**
     * Print HTML
     */
    public function printHtml(string $printer, string $html, array $options = [])
    {
        return $this->queueJob([
            'printer' => $printer,
            'type' => 'html',
            'data' => $html,
            'options' => $options,
        ]);
    }

    /**
     * Print PDF from URL
     */
    public function printPdf(string $printer, string $pdfUrl, array $options = [])
    {
        return $this->queueJob([
            'printer' => $printer,
            'type' => 'pdf',
            'data' => $pdfUrl,
            'options' => $options,
        ]);
    }

    /**
     * Print image
     */
    public function printImage(string $printer, string $imageUrl, array $options = [])
    {
        return $this->queueJob([
            'printer' => $printer,
            'type' => 'image',
            'data' => $imageUrl,
            'options' => $options,
        ]);
    }

    /**
     * Print ZPL commands
     */
    public function printZpl(string $printer, string $zplCommands, array $options = [])
    {
        return $this->queueJob([
            'printer' => $printer,
            'type' => 'zpl',
            'data' => $zplCommands,
            'options' => $options,
        ]);
    }

    /**
     * Print ESC/POS commands
     */
    public function printEscpos(string $printer, string $commands, array $options = [])
    {
        return $this->queueJob([
            'printer' => $printer,
            'type' => 'escpos',
            'data' => $commands,
            'options' => $options,
        ]);
    }

    /**
     * Print barcode
     */
    public function printBarcode(string $printer, string $data, string $type = 'CODE128', array $options = [])
    {
        $zplCommands = $this->generateZplBarcode($data, $type, $options);

        return $this->printZpl($printer, $zplCommands, $options);
    }

    /**
     * Generate ZPL barcode commands
     */
    protected function generateZplBarcode(string $data, string $type, array $options)
    {
        $width = $options['width'] ?? 2;
        $height = $options['height'] ?? 100;
        $humanReadable = $options['human_readable'] ?? true;

        $zpl = "^XA\n";
        $zpl .= "^FO50,50\n";
        $zpl .= "^BY{$width},3,{$height}\n";
        $zpl .= "^B{$type},N,N,N\n";
        $zpl .= "^FD{$data}^FS\n";

        if ($humanReadable) {
            $zpl .= "^FO50,150^A0N,50,50^FD{$data}^FS\n";
        }

        $zpl .= "^XZ";

        return $zpl;
    }

    /**
     * Get job history
     */
    public function getJobHistory(int $limit = 50, int $userId = null)
    {
        $query = PrintJob::latest();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get queue status
     */
    public function getQueueStatus()
    {
        return [
            'queued' => PrintJob::where('status', 'queued')->count(),
            'processing' => PrintJob::where('status', 'processing')->count(),
            'completed' => PrintJob::where('status', 'completed')->count(),
            'failed' => PrintJob::where('status', 'failed')->count(),
            'total_today' => PrintJob::whereDate('created_at', today())->count(),
        ];
    }
}
