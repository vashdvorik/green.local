<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_pages_return_successful_responses(): void
    {
        foreach (['/', '/about', '/business', '/news', '/stories', '/media', '/partners', '/contacts'] as $path) {
            $this->get($path)->assertOk();
        }
    }
}
