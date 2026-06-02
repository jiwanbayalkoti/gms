<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PostTooLargeException $e, $request) {
            $limit = ini_get('post_max_size') ?: 'unknown';
            $message = "Upload धेरै ठूलो छ (PHP limit: {$limit}). Video ~50MB सम्म चाहिन्छ भने XAMPP मा post_max_size र upload_max_filesize बढाउनुहोस् (उदा. 128M / 64M) र Apache restart गर्नुहोस्।";

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 413);
            }

            return back()->withInput()->with('error', $message);
        });
    }
}
