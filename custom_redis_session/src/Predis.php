<?php

namespace Drupal\custom_redis_session;

/**
 * Class PhpRedis
 * @package Drupal\oo_auth
 */
class Predis implements ClientInterface
{
  /**
   * {@inheritdoc}
   */
  public function getClient($host = null, $port = null, $base = null, $password = null)
  {
    $client = new \Redis();
    $client->connect($host, $port);

    if (isset($password)) {
      $client->auth($password);
    }

    if (isset($base)) {
      $client->select($base);
    }

    $client->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);

    return $client;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Predis';
  }
}
