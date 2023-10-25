<?php

namespace Drupal\custom_redis_session;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

class CustomRedisServiceProvider extends ServiceProviderBase
{
  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container)
  {
    $container->getDefinition('session_handler.storage')
      ->setClass('Drupal\custom_redis_session\SessionHandler')
      ->setArguments([
        new Reference('request_stack')
      ]);
  }
}
