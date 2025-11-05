<?php

namespace App\Jobs;

use App\Events\UploadStatusUpdated;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCsvChunk implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $rows, private int $uploadId) { }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $upload = Upload::find($this->uploadId);

        try {
            foreach ($this->rows as $row) {
                Product::updateOrInsert(
                    ['unique_key' => $row[0]],
                    [
                        'product_title'           => $row[1] ?? null,
                        'product_description'     => $row[2] ?? null,
                        'style_no'                => $row[3] ?? null,
                        'sanmar_mainframe_color'  => $row[28] ?? null,
                        'size'                    => $row[18] ?? null,
                        'color_name'              => $row[14] ?? null,
                        'piece_price'             => $row[21] ?? null,
                    ]
                );
            }
            $upload->increment('processed_rows', count($this->rows));

            $progress = intval(($upload->processed_rows / $upload->total_rows) * 100);
            $upload->update(['progress' => $progress]);
            event(new UploadStatusUpdated($upload));

            if ($upload->processed_rows >= $upload->total_rows) {
                $upload->update(['status' => Upload::STATUS_COMPLETED, 'progress' => 100]);
            }
        } catch (Throwable $th) {
            Log::error(__CLASS__."::".__FUNCTION__."() ERROR: " . $th->getMessage());
            $upload->update(['status' => Upload::STATUS_FAILED]);
            throw $th;
        } finally {
            Log::debug(__CLASS__."::".__FUNCTION__."() Finish");
            event(new UploadStatusUpdated($upload));
        }
    }
}
