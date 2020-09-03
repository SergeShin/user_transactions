<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function actAsRandomUser()
    {
        $this->actingAs(
            factory(User::class)->create()
        );
    }

    protected function actAsRandomAdmin()
    {
        $this->actingAs(
            factory(User::class)->state('admin')->create()
        );
    }
}
