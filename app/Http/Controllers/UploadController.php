<?php

namespace App\Http\Controllers;

use App\Http\Resources\UploadResource;
use App\Jobs\ProcessCsvUpload;
use App\Models\Upload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv',
        ]);

        try {
            /** @var UploadedFile $file */
            $file = $request->file('file');
            $originalName = $file->hashName();
            $path = $file->storeAs('uploads', $originalName, 'local');
            $upload = Upload::create(['filename' => $file->getClientOriginalName(), 'storeFilename' => $originalName]);

            ProcessCsvUpload::dispatch($upload);

            $message = "File uploaded successfully.";
            if($request->ajax())
                return response()->json(['success' => true, 'message' => $message, 'path' => $path]);
            else
                return redirect()->route('index')->with('success', $message);
        } catch (Exception $e) {
            if($request->ajax())
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            else
                return redirect()->route('index')->with('error', $e->getMessage());
        }

    }

    public function list()
    {
        return UploadResource::collection(Upload::latest()->get());
    }
}
