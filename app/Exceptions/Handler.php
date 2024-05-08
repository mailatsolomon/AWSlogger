<?php

namespace App\Exceptions;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Exceptions\SystemErrorException;
use Http\ErrorResponse;
use App\AWSLogger;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ErrorResponse;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // exceptions that has a handler in frontend
        $handledExceptions = [
            HttpException::class,
            AuthorizationException::class,
            ValidationException::class,
            CognitoIdentityProviderException::class
        ];

        foreach ($handledExceptions as $handledException) {
            if ($exception instanceof $handledException) {
                return $this->errorResponse($request, $exception);
            }
        }

        if (strtolower(env('APP_ENV')) == 'local') {
           //Do local logging if desired
        } else {
            AWSLogger::error(
                $request->except('user'),
                $exception->getMessage(),
                $exception->getTraceAsString()
            );
        }

        return $this->errorResponse(
            $request,
            new SystemErrorException()
        );
    }
}
