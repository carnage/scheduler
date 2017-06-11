<?php

namespace Carnage\Scheduler\Service\Factory;

use Carnage\Cqrs\Aggregate\Identity\YouTubeStyleIdentityGenerator;
use Carnage\Scheduler\Domain\Model\Scheduler as SchedulerAggregate;
use Carnage\Scheduler\Domain\CommandHandler\Scheduler as SchedulerCommandHandler;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Carnage\Cqrs\Persistence\Repository\PluginManager as RepositoryManager;

class Scheduler implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator->getServiceLocator(), SchedulerCommandHandler::class);
    }

    public function __invoke(ContainerInterface $container, $name, $options = [])
    {
        $repositoryManager = $container->get(RepositoryManager::class);
        return new SchedulerCommandHandler(
            $repositoryManager->get(SchedulerAggregate::class),
            new YouTubeStyleIdentityGenerator()
        );
    }
}