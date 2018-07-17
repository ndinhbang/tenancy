<?php

namespace Tenancy\Tests\Facades;

use Tenancy\Environment;
use Tenancy\Facades\Tenancy;
use Tenancy\Identification\Contracts\ResolvesTenants;
use Tenancy\Identification\Events\Resolving;
use Tenancy\Tests\Mocks\Tenant;
use Tenancy\Tests\TestCase;

class TenancyTest extends TestCase
{
    /** @var Tenant */
    protected $tenant;

    protected function afterSetUp()
    {
        /** @var ResolvesTenants $resolver */
        $resolver = $this->app->make(ResolvesTenants::class);
        $this->tenant = factory(Tenant::class)->make();

        $resolver->addModel(Tenant::class);
    }

    /**
     * @test
     */
    public function can_proxy_environment_calls()
    {
        $this->assertNull(Tenancy::getTenant());

        $this->assertInstanceOf(Environment::class, Tenancy::setTenant($this->tenant));

        $this->assertEquals($this->tenant->name, optional(Tenancy::getTenant())->name);
    }

    /**
     * @test
     * @covers \Tenancy\Environment::setIdentified
     */
    public function setting_identified_ignores_auto_identification()
    {
        $this->events->listen(Resolving::class, function (Resolving $event) {
            return $this->tenant;
        });

        Tenancy::setIdentified(true);

        $this->assertNull(Tenancy::getTenant());

        Tenancy::setIdentified(false);

        $this->assertNotNull(Tenancy::getTenant());
    }

    /**
     * @test
     * @covers \Tenancy\Environment::getTenant
     */
    public function refreshing_loads_new_tenant()
    {
        $this->assertNull(Tenancy::getTenant());

        $this->events->listen(Resolving::class, function (Resolving $event) {
            return $this->tenant;
        });

        $this->assertNull(Tenancy::getTenant());
        $this->assertNotNull(Tenancy::getTenant(true));
    }

    /**
     * @test
     */
    public function can_retrieve_system_connection()
    {
        $this->assertEquals(
            Environment::getDefaultSystemConnectionName(),
            Tenancy::getSystemConnection()->getName()
        );
    }

    /**
     * @test
     */
    public function can_retrieve_tenant_connection()
    {
        $this->assertEquals(
            config('tenancy.database.tenant-connection-name'),
            Tenancy::getTenantConnection()->getName()
        );
    }
}
