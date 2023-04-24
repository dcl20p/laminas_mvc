<?php
namespace Zf\Ext\Session;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Laminas\Session\Validator\ValidatorInterface;

class RemoteAddr implements ValidatorInterface
{
    /**
     * @var	string	$data
     */
    protected string $data;

    /**
     * @var	boolean	$useProxy
     */
    protected static bool $useProxy = false;

    /**
     * @var	array $trustedProxies
     */
    protected static array $trustedProxies = [];

    /**
     * @var	string $proxyHeader
     */
    protected static string $proxyHeader = 'HTTP_X_FORWARDED_FOR';


    /**
     * Constructor
     *
     * @param string|null $data
     */
    public function __construct(?string $data = null)
    {
        if (empty($data)) {
            $data = $this->getIpAddress();        
        }
        $this->data = $data;
    }

    /**
     * This method will determine if the current user IP matches the
     * IP we stored when we initialized this variable.
     *
     * @return boolean
     */
    public  function isValid(): bool
    {
        if (false == ($this->getIpAddress() === $this->getData())) {
            @unlink(DATA_PATH . '/session/' .APPLICATION_SITE. '/sess_' . session_id());
        }
        return true;
    }

    /**
     * Changes proxy handling setting.
     *
     * @param boolean $useProxy
     * @return void
     */
    public static function setUseProxy(bool $useProxy = true): void 
    {
        static::$useProxy = $useProxy;
    }

    /**
     * Checks proxy handling setting.
     *
     * @return boolean
     */
    public static function getUseProxy(): bool
    {
        return static::$useProxy;
    }

    /**
     * Set list of trusted proxy addresses
     *
     * @param array $trustedProxies
     * @return void
     */
    public static function setTrustedProxies(array $trustedProxies): void
    {
        static::$trustedProxies = $trustedProxies;
    }

    /**
     * Set the header to introspect for proxy IPs
     *
     * @param string $header
     * @return void
     */
    public static function setProxyHeader(string $header = 'X-Forwarded-For'): void
    {
        static::$proxyHeader = $header;
    }

    /**
     * Return client IP address
     *
     * @return string
     */
    protected function getIpAddress(): string
    {   
        $remoteAddress = new RemoteAddress();
        $remoteAddress->setUseProxy(static::$useProxy);
        $remoteAddress->setTrustedProxies(static::$trustedProxies);
        $remoteAddress->setProxyHeader(static::$proxyHeader);

        return $remoteAddress->getIpAddress();
    }

    /**
     * Retrieve token for validating call
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Return validator name
     *
     * @return string
     */
    public function getName(): string
    {
        return __CLASS__;
    }
}