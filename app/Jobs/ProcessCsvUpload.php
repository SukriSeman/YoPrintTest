<?php

namespace App\Jobs;

use App\Events\UploadStatusUpdated;
use App\Models\Product;
use App\Models\Upload;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessCsvUpload implements ShouldQueue
{
    use Queueable;

    public $timeout = 0;
    public $tries = 1;
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(private Upload $upload) { }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->upload->update(['status' => Upload::STATUS_PROCESSING]);
        event(new UploadStatusUpdated($this->upload));

        $fullPath = Storage::disk('local')->path('uploads/' . $this->upload->storeFilename);
        if (!file_exists($fullPath)) {
            $this->upload->update(['status' => Upload::STATUS_FAILED]);
            Log::error(__CLASS__."::".__FUNCTION__."() ERROR: File not found [$fullPath]");
            event(new UploadStatusUpdated($this->upload));
            return;
        }

        try {
            $csv = fopen($fullPath, 'r');
            if (!$csv) {
                throw new Exception("Unable to open CSV file [$fullPath]");
            }

            fgetcsv($csv); //skip header
            $chunkSize = 500;
            $totalRow = 0;
            $rowsBuffer = $chainJobs = [];

            while (($row = fgetcsv($csv)) !== false) {
                $rowsBuffer[] = $row;
                $totalRow++;

                if (count($rowsBuffer) >= $chunkSize) {
                    $chainJobs[] = new ProcessCsvChunk($rowsBuffer, $this->upload->id);
                    $rowsBuffer = [];
                }
            }

            if (!empty($rowsBuffer)) {
                $chainJobs[] = new ProcessCsvChunk($rowsBuffer, $this->upload->id);
            }

            Log::debug(__CLASS__."::".__FUNCTION__."() total_rows : $totalRow");
            $this->upload->update(['total_rows' => $totalRow]);

            Bus::chain($chainJobs)->dispatch();

        } catch (Exception $e) {
            Log::error(__CLASS__."::".__FUNCTION__."() ERROR: " . $e->getMessage());
            $this->upload->update(['status' => Upload::STATUS_FAILED]);
            throw $e;
        } catch (Throwable $th) {
            Log::error(__CLASS__."::".__FUNCTION__."() ERROR: " . $th->getMessage());
            $this->upload->update(['status' => Upload::STATUS_FAILED]);
            throw $th;
        } finally {
            if (isset($csv) && is_resource($csv)) {
                fclose($csv);
            }
            Log::debug(__CLASS__."::".__FUNCTION__."() Finish");
            event(new UploadStatusUpdated($this->upload));
        }
    }
}
