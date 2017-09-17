<?php

namespace Carnage\Scheduler\Cli\Command;

use Carnage\Cqrs\Command\CommandBusInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WakeupFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        return Wakeup::build(
            $serviceLocator->get(CommandBusInterface::class)
        );
    }
}
