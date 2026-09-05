<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Resolve route parameters by name rather than by position.
     *
     * ControllerDispatcher::dispatch() ends in
     * `$controller->{$method}(...array_values($parameters))`, where $parameters
     * is every route parameter in URI order. The public site sits inside a
     * `Route::prefix('{locale}')` group, so that order always starts with the
     * locale: /en/blog/why-early-literacy arrives as
     * ['locale' => 'en', 'slug' => 'why-early-literacy'] and `show(string $slug)`
     * was handed 'en'. PHP does not object to the surplus argument, so all five
     * detail pages quietly 404'd — each looking for the row whose slug is
     * literally "en" — while the index pages beside them rendered fine, because a
     * method with no parameters cannot be given the wrong one.
     *
     * Going through the container matches on each parameter's own name instead,
     * which is what the signatures already assume, and it is what stops a future
     * detail route from repeating the mistake by forgetting to declare $locale
     * first. Anything the method does not name is appended after what it does;
     * PHP ignores the surplus, exactly as it did before.
     *
     * This method is the extension point ControllerDispatcher looks for: it calls
     * callAction() when the controller has one and spreads positionally when it
     * does not, which is what this class was doing by staying empty.
     */
    public function callAction(string $method, array $parameters): mixed
    {
        return app()->call([$this, $method], $parameters);
    }
}
