<?php

namespace Edalzell\Blade;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = false;

    public function boot()
    {
        parent::boot();

        $this->bootDirectives();
    }

    private function bootDirectives()
    {
        $this->bootBard();
        $this->bootCollection();
        $this->bootGlobal();
    }

    private function bootBard()
    {
        Blade::directive(
            'bard',
            fn ($expression) => $this->startPHPLoop("Facades\Edalzell\Blade\Directives\Bard::handle(${expression})", 'set')
        );

        Blade::directive('endbard', fn () => $this->endPHPLoop());
    }

    private function bootCollection()
    {
        Blade::directive(
            'collection',
            fn ($expression) => $this->startPHPLoop("Facades\Edalzell\Blade\Directives\Collection::handle(${expression})", 'entry')
        );

        Blade::directive('endcollection', fn () => $this->endPHPLoop());
    }

    private function bootGlobal()
    {
        Blade::directive(
            'globalset',
            function ($expression) {
                if (Str::contains($expression, ',')) {
                    return $this->php('echo Facades\Edalzell\Blade\Directives\GlobalSet::handleKey('.$expression.');');
                }

                return $this->php('extract($globalset = Facades\Edalzell\Blade\Directives\GlobalSet::handleSet('.$expression.'));');
            }
        );

        Blade::directive(
            'endglobalset',
            fn () => '<?php
                foreach($globalset as $key => $value) {
                    unset($key);
                }
                unset($globalset);
            ?>'
        );
    }

    private function startPHPLoop($arrayStatement, $as)
    {
        return $this->php("foreach(${arrayStatement} as $${as}) {");
    }

    private function endPHPLoop()
    {
        return $this->php('}');
    }

    private function php($php)
    {
        return "<?php {$php} ?>";
    }
}
