<?php

namespace Tenancy\Tests\Identification\Http;

use Tenancy\Environment;
use Tenancy\Facades\Tenancy;
use Tenancy\Identification\Drivers\Http\Contracts\IdentifiesByHttp;
use Tenancy\Identification\Drivers\Http\Providers\IdentificationProvider;
use Tenancy\Testing\TestCase;

class RequestTest extends TestCase
{
    protected $additionalProviders = [IdentificationProvider::class];

    /** @test */
    public function it_triggers_identification_on_incoming_requests()
    {
        $this->mock(Environment::class, function ($mock){
            $mock
                ->shouldReceive('isIdentified')
                ->andReturn(false);
            $mock
                ->shouldReceive('identifyTenant')
                ->once()
                ->withArgs([
                    false,
                    IdentifiesByHttp::class
                ]);
        });

        $this->get('/');
    }

    /** @test */
    public function it_does_not_trigger_identification_when_a_tenant_is_already_identified()
    {
        $this->mock(Environment::class, function ($mock){
            $mock
                ->shouldReceive('isIdentified')
                ->andReturn(true);
            $mock
                ->shouldNotReceive('identifyTenant');
        });

        $this->get('/');
    }
}
