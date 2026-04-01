<?php

declare(strict_types=1);

namespace ACA\ACF;

use AC;
use AC\DI\Container;
use AC\Plugin\Version;
use AC\Services;
use ACA\ACF;
use ACP\Addon;
use ACP\Service\IntegrationStatus;

final class AdvancedCustomFields implements Addon
{

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get_id(): string
    {
        return 'acf';
    }

    private function get_acf_version(): Version
    {
        return new Version(acf()->version);
    }

    public function register(): void
    {
        if ( ! class_exists('acf', false)) {
            return;
        }

        if ($this->get_acf_version()->is_lt(new Version('5.7'))) {
            return;
        }

        $this->define_factories();
        $this
            ->create_services()
            ->register();
    }

    private function define_factories(): void
    {
        AC\ColumnFactories\Aggregate::add($this->container->get(ACF\ColumnFactories\FieldsFactory::class));
        AC\ColumnFactories\Aggregate::add($this->container->get(ACF\ColumnFactories\OrderFieldsFactory::class));
    }

    private function create_services(): Services
    {
        $services = new Services([
            new IntegrationStatus('ac-addon-acf'),
        ]);

        $class_names = [
            Service\ColumnGroup::class,
            Service\EditingFix::class,
            Service\Scripts::class,
        ];

        foreach ($class_names as $service) {
            $services->add($this->container->get($service));
        }

        return $services;
    }

}