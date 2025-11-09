<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
        $this->renderable(function (\Exception $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = 500; // Default to 500
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $statusCode = 403;
                } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                } elseif ($e->getCode() == 401) {
                    $statusCode = 401;
                }

                return response()->json([
                    'message' => $e->getMessage() ?: 'Une erreur est survenue',
                    'status' => 'error'
                ], $statusCode);
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
