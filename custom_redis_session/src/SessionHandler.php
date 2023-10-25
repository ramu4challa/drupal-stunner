<?php

namespace Drupal\custom_redis_session;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Utility\Error;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;

/**
 * Default session handler.
 */
class SessionHandler extends AbstractProxy implements \SessionHandlerInterface
{

  use DependencySerializationTrait;

  /**
   * The request stack.
   *
   * @var RequestStack
   */
  protected $requestStack;

  /**
   * @var \Redis
   */
  protected $redis;

  /**
   * SessionHandler constructor.
   *
   * @param RequestStack $requestStack
   */
  public function __construct(RequestStack $requestStack)
  {
    $this->requestStack = $requestStack;
    // TODO: Store redis connection details in config.
    $this->redis = (new Predis())->getClient('redis', 6379);
  }

  /**
   * {@inheritdoc}
   */
  public function open($savePath, $name)
  {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function read($sid)
  {
    $data = '';

    if (!empty($sid)) {
      $query = $this->redis->get(Crypt::hashBase64($sid));
      $data = unserialize($query);
    }

    return (string)$data['session'];
  }

  /**
   * {@inheritdoc}
   */
  public function write($sid, $value)
  {
    // The exception handler is not active at this point, so we need to do it
    // manually.

    var_dump(['Value', $value]);
    try {
      $request = $this->requestStack->getCurrentRequest();
      $fields = [
        'uid' => $request->getSession()->get('uid', 0),
        'hostname' => $request->getClientIP(),
        'session' => $value,
        'timestamp' => REQUEST_TIME,
      ];

      $this->redis->set(
        Crypt::hashBase64($sid),
        serialize($fields),
        (int)ini_get("session.gc_maxlifetime")
      );

      return true;
    } catch (\Exception $exception) {
      require_once DRUPAL_ROOT . '/core/includes/errors.inc';
      // If we are displaying errors, then do so with no possibility of a
      // further uncaught exception being thrown.
      if (error_displayable()) {
        print '<h1>Uncaught exception thrown in session handler.</h1>';
        print '<p>' . Error::renderExceptionSafe($exception) . '</p><hr />';
      }

      return true;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function close()
  {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function destroy($sid)
  {
    // Delete session data.
    $this->redis->delete(Crypt::hashBase64($sid));

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function gc($lifetime)
  {
    // Redundant method when using Redis. You no longer have to check the session
    // timestamp as the session.gc_maxlifetime is set as TTL on write.
    return true;
  }

}
