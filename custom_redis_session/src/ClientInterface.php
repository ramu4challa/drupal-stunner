<?php

namespace Drupal\custom_redis_session;

/**
 * Interface ClientInterface
 */
interface ClientInterface
{
  /**
   * Get the connected client instance.
   *
   * @param null $host
   * @param null $port
   * @param null $base
   *
   * @return mixed
   */
  public function getClient($host = NULL, $port = NULL, $base = NULL);

  /**
   * Get underlying library name used.
   *
   * This can be useful for contribution code that may work with only some of
   * the provided clients.
   *
   * @return string
   */
  public function getName();
}
