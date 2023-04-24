<?php
namespace Zf\Ext\Session;
use Laminas\Session\Validator\ValidatorInterface;

class HttpUserAgent implements ValidatorInterface
{
    
    /**
     * @var	string	$data
     */
    protected string $data;

    /**
     * Constructor
     * get the current user agent and store it in the session as 'valid data'
     *
     * @param string|null $data
     */
    public function __construct(?string $data = null)
    {
        if (empty($data)) {
            $data = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }
        $this->data = $data;
    }

    /**
     * This method will determine if the current user agent matches the
     * user agent we stored when we initialized this variable.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if (false == ($userAgent === $this->getData())) {
            @unlink(DATA_PATH . '/session/' .APPLICATION_SITE . '/sess_' .session_id());
        }
        return true;
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