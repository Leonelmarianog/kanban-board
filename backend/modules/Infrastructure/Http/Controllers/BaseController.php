<?php

namespace Modules\Infrastructure\Http\Controllers;

use Illuminate\Routing\Controller as LaravelController;
use Modules\Infrastructure\Http\Traits\ApiResponses;

/**
 * Base controller for handling HTTP requests and responses.
 */
abstract class BaseController extends LaravelController
{
    use ApiResponses;
}
