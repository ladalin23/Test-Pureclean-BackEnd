<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];
    protected $dontFlash = ['password','password_confirmation','current_password','token'];

    // ✅ Force JSON for requests to /api/* (and when client asks for JSON)
    protected function shouldReturnJson($request, Throwable $e)
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    public function render($request, Throwable $e)
    {
        if ($this->shouldReturnJson($request, $e)) {

            // 404 — Model not found (before conversion)
            if ($e instanceof ModelNotFoundException) {
                return $this->jsonError($this->friendlyModelMessage($e), 404);
            }

            // 404 — Already converted (including abort(404))
            if ($e instanceof NotFoundHttpException) {
                $prev = $e->getPrevious();
                if ($prev instanceof ModelNotFoundException) {
                    return $this->jsonError($this->friendlyModelMessage($prev), 404);
                }
                return $this->jsonError($e->getMessage() ?: 'Not found.', 404);
            }

            // 422 — Validation
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed.',
                    'data'    => $e->errors(),
                ], 422);
            }

            // 401
            if ($e instanceof AuthenticationException) {
                return $this->jsonError('Unauthenticated.', 401);
            }

            // 403
            if ($e instanceof AuthorizationException) {
                return $this->jsonError('Forbidden.', 403);
            }

            // Any other HttpException (429, 405, etc.)
            if ($e instanceof HttpExceptionInterface) {
                return $this->jsonError($e->getMessage() ?: 'HTTP error', $e->getStatusCode());
            }

            // 500 fallback
            return $this->jsonError(app()->hasDebugModeEnabled() ? $e->getMessage() : 'Server Error', 500);
        }

        // Non-API: default HTML error pages
        return parent::render($request, $e);
    }

    private function jsonError(string $message, int $status)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => null,
        ], $status);
    }

    private function friendlyModelMessage(ModelNotFoundException $e): string
    {
        $fqcn = $e->getModel() ?: '';
        $short = class_exists($fqcn) ? class_basename($fqcn) : 'Resource';

        if ($short === 'LoyaltyCard') {
            return 'Loyalty card not found.';
        }

        // Turn "SomeModelName" → "Some model name not found."
        $pretty = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $short));
        return "{$pretty} not found.";
    }
}
