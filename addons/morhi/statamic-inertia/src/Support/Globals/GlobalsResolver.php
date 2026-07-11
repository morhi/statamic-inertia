<?php

namespace Morhi\StatamicInertia\Support\Globals;

use Morhi\StatamicInertia\Support\EntryTransformer;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Blink;
use Statamic\Facades\GlobalSet;
use Statamic\Globals\Variables;
use Statamic\Sites\Site;

class GlobalsResolver
{
    public function __construct(protected EntryTransformer $transformer) {}

    /**
     * Resolve the `globals` shared prop: every var whitelisted in config('statamic-inertia.globals'),
     * filtered by its scope predicates, transformed through the same fieldtype transformers
     * used for entries, and reshaped by any configured GlobalValueTransformer.
     */
    public function resolve(Site $site, ?EntryContract $entry): array
    {
        $result = [];

        foreach (config('statamic-inertia.globals', []) as $handle => $rules) {
            $variables = $this->resolveVariables($handle, $site);

            if (! $variables) {
                continue;
            }

            $normalizedRules = $this->normalizeRules($rules);

            foreach ($variables->data() as $varHandle => $rawValue) {
                $rule = $normalizedRules[$varHandle] ?? $normalizedRules['*'] ?? null;

                if (! $rule) {
                    continue;
                }

                if (! $this->matchesScope($rule['scopes'], $entry, $site)) {
                    continue;
                }

                $value = $this->transformer->transform($variables, [$varHandle])[$varHandle] ?? null;

                if ($rule['transformer']) {
                    $value = app($rule['transformer'])->transform($value);
                }

                $result[$handle][$varHandle] = $value;
            }
        }

        return $result;
    }

    /**
     * Loads the site-localized global set, memoized per (handle, site) for the life of the
     * process — avoids re-hitting the Stache for the same global set on every request when
     * many requests are served in one process (e.g. `statamic:static:warm`).
     */
    protected function resolveVariables(string $handle, Site $site): ?Variables
    {
        return Blink::once("inertia-globals-{$handle}-{$site->handle()}", function () use ($handle, $site) {
            return GlobalSet::findByHandle($handle)?->in($site->handle());
        });
    }

    /**
     * Normalizes the mixed rule list into `varHandle => ['scopes' => [...], 'transformer' => ?class-string]`.
     * Bare entries ('foo', '*') mean "exposed, unrestricted"; keyed entries are either a list of
     * `type:value` scope predicates or a GlobalValueTransformer class-string.
     */
    protected function normalizeRules(array $rules): array
    {
        $normalized = [];

        foreach ($rules as $key => $value) {
            if (is_int($key)) {
                $normalized[$value] = ['scopes' => [], 'transformer' => null];

                continue;
            }

            $normalized[$key] = is_string($value)
                ? ['scopes' => [], 'transformer' => $value]
                : ['scopes' => $value, 'transformer' => null];
        }

        return $normalized;
    }

    /** Scope predicates on a var combine with AND: every one must match for it to be exposed. */
    protected function matchesScope(array $scopes, ?EntryContract $entry, Site $site): bool
    {
        foreach ($scopes as $scope) {
            [$type, $value] = explode(':', $scope, 2);

            $matches = match ($type) {
                'site' => $site->handle() === $value,
                'collection' => $entry?->collection()?->handle() === $value,
                'blueprint' => $entry?->blueprint()?->handle() === $value,
                default => false,
            };

            if (! $matches) {
                return false;
            }
        }

        return true;
    }
}
