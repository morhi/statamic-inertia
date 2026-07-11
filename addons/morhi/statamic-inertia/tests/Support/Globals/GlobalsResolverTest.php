<?php

namespace Morhi\StatamicInertia\Tests\Support\Globals;

use Morhi\StatamicInertia\Support\EntryTransformer;
use Morhi\StatamicInertia\Support\Globals\GlobalsResolver;
use Morhi\StatamicInertia\Support\Globals\GlobalValueTransformer;
use Morhi\StatamicInertia\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;

class GlobalsResolverTest extends TestCase
{
    protected function resolver(): GlobalsResolver
    {
        return new GlobalsResolver(app(EntryTransformer::class));
    }

    protected function makeGlobalSet(string $handle, array $data): void
    {
        $set = GlobalSet::make()->handle($handle)->title($handle);
        $set->save();
        $set->in(Site::default()->handle())->data($data)->save();
    }

    public function test_unlisted_global_set_is_not_exposed(): void
    {
        $this->makeGlobalSet('footer', ['company_name' => 'Studio']);

        config(['inertia.globals' => []]);

        $result = $this->resolver()->resolve(Site::default(), null);

        $this->assertSame([], $result);
    }

    public function test_unlisted_var_within_a_whitelisted_set_is_not_exposed(): void
    {
        $this->makeGlobalSet('footer', ['company_name' => 'Studio', 'secret' => 'nope']);

        config(['inertia.globals' => [
            'footer' => ['company_name'],
        ]]);

        $result = $this->resolver()->resolve(Site::default(), null);

        $this->assertSame(['footer' => ['company_name' => 'Studio']], $result);
    }

    public function test_catch_all_exposes_vars_not_explicitly_listed(): void
    {
        $this->makeGlobalSet('footer', ['company_name' => 'Studio', 'company_address' => '123 Main St']);

        config(['inertia.globals' => [
            'footer' => ['*'],
        ]]);

        $result = $this->resolver()->resolve(Site::default(), null);

        $this->assertSame([
            'footer' => [
                'company_name'    => 'Studio',
                'company_address' => '123 Main St',
            ],
        ], $result);
    }

    public function test_scope_predicate_excludes_var_when_entry_does_not_match(): void
    {
        Collection::make('blog')->save();
        Collection::make('pages')->save();

        $blogEntry  = Entry::make()->collection('blog')->slug('post')->data(['title' => 'Post']);
        $blogEntry->save();
        $pageEntry = Entry::make()->collection('pages')->slug('home')->data(['title' => 'Home']);
        $pageEntry->save();

        $this->makeGlobalSet('footer', ['newsletter_label' => 'Subscribe']);

        config(['inertia.globals' => [
            'footer' => [
                'newsletter_label' => ['collection:blog'],
            ],
        ]]);

        $onBlogPage = $this->resolver()->resolve(Site::default(), $blogEntry);
        $onOtherPage = $this->resolver()->resolve(Site::default(), $pageEntry);

        $this->assertSame(['footer' => ['newsletter_label' => 'Subscribe']], $onBlogPage);
        $this->assertSame([], $onOtherPage);
    }

    public function test_multiple_scope_predicates_combine_with_and(): void
    {
        Collection::make('blog')->save();

        $blogEntry = Entry::make()->collection('blog')->slug('post')->data(['title' => 'Post']);
        $blogEntry->save();

        $this->makeGlobalSet('footer', ['newsletter_label' => 'Subscribe']);

        config(['inertia.globals' => [
            'footer' => [
                // Site "default" matches, but collection "news" does not exist for this entry.
                'newsletter_label' => ['collection:news', 'site:default'],
            ],
        ]]);

        $result = $this->resolver()->resolve(Site::default(), $blogEntry);

        $this->assertSame([], $result);
    }

    public function test_custom_transformer_reshapes_the_value(): void
    {
        $this->makeGlobalSet('footer', ['company_name' => 'studio']);

        config(['inertia.globals' => [
            'footer' => [
                'company_name' => UppercasingTransformer::class,
            ],
        ]]);

        $result = $this->resolver()->resolve(Site::default(), null);

        $this->assertSame(['footer' => ['company_name' => 'STUDIO']], $result);
    }
}

class UppercasingTransformer implements GlobalValueTransformer
{
    public function transform(mixed $value): mixed
    {
        return strtoupper((string) $value);
    }
}
