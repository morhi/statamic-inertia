<?php

namespace Morhi\StatamicInertia\Support\Ssr;

use Inertia\Ssr\Gateway;
use Inertia\Ssr\HasHealthCheck;
use Inertia\Ssr\HttpGateway;
use Inertia\Ssr\Response;

/**
 * Wraps Inertia's default SSR gateway to record, per request, whether SSR was
 * actually attempted and whether it succeeded. The @inertia/@inertiaHead Blade
 * directives call Gateway::dispatch() themselves and only expose the result to
 * a local view variable — nothing else in the request can observe whether SSR
 * rendered successfully. Bound as a singleton (see ServiceProvider), so the
 * InertiaAwareStaticCache middleware can inspect the same instance afterward
 * to avoid caching a page that was served without SSR while it was expected.
 */
class SsrTrackingGateway implements Gateway, HasHealthCheck
{
    private bool $attempted = false;

    private bool $succeeded = false;

    public function __construct(protected HttpGateway $gateway) {}

    public function dispatch(array $page): ?Response
    {
        $this->attempted = config('inertia.ssr.enabled', true);

        $response = $this->gateway->dispatch($page);

        $this->succeeded = $response !== null;

        return $response;
    }

    public function isHealthy(): bool
    {
        return $this->gateway->isHealthy();
    }

    /**
     * True only when SSR was configured to run for this request but the
     * dispatch came back empty (server down, unreachable, or errored).
     */
    public function dispatchFailed(): bool
    {
        return $this->attempted && ! $this->succeeded;
    }
}
