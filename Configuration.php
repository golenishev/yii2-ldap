<?php

namespace chrmorandi\ldap;

use chrmorandi\ldap\exceptions\ConfigurationException;
use chrmorandi\ldap\exceptions\InvalidArgumentException;
use chrmorandi\ldap\objects\DistinguishedName;
use Traversable;

class Configuration extends \yii\base\Object
{
    /**
     * The LDAP base dn.
     *
     * @var string
     */
    protected $baseDn;

    /**
     * The integer to instruct the LDAP connection
     * whether or not to follow referrals.
     *
     * https://msdn.microsoft.com/en-us/library/ms677913(v=vs.85).aspx
     *
     * @var bool
     */
    protected $followReferrals = false;

    /**
     * The LDAP port to use when connecting to
     * the domain controllers.
     *
     * @var string
     */
    protected $port = ConnectionInterface::PORT;

    /**
     * Determines whether or not to use TLS
     * with the current LDAP connection.
     *
     * @var bool
     */
    protected $useTLS = false;

    /**
     * The domain controllers to connect to.
     *
     * @var array
     */
    protected $domainControllers = [];

    /**
     * The LDAP account suffix.
     *
     * @var string
     */
    protected $accountSuffix;

    /**
     * The LDAP account prefix.
     *
     * @var string
     */
    protected $accountPrefix;

    /**
     * The LDAP administrator username.
     *
     * @var string
     */
    private $adminUsername;

    /**
     * The LDAP administrator password.
     *
     * @var string
     */
    private $adminPassword;

    /**
     * The LDAP administrator account suffix.
     *
     * @var string
     */
    private $adminAccountSuffix;

    /**
     * Constructor.
     *
     * @param array|Traversable $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($options = [])
    {
        $this->fill($options);
    }

    /**
     * Sets the base DN property.
     *
     * @param string $dn
     */
    public function setBaseDn($dn)
    {
        $this->baseDn = $dn;
    }

    /**
     * Returns the Base DN string.
     *
     * @return string|null
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * Sets the follow referrals option.
     *
     * @param bool $bool
     */
    public function setFollowReferrals($bool)
    {
        $this->followReferrals = (bool) $bool;
    }

    /**
     * Returns the follow referrals option.
     *
     * @return int
     */
    public function getFollowReferrals()
    {
        return $this->followReferrals;
    }

    /**
     * Sets the port option to use when connecting.
     *
     * @param $port
     *
     * @throws ConfigurationException
     */
    public function setPort($port)
    {
        if (!is_numeric($port)) {
            throw new ConfigurationException('Your configured LDAP port must be an integer.');
        }

        $this->port = (string) $port;
    }

    /**
     * Returns the port option.
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the option whether or not to use TLS when connecting.
     *
     * @param $bool
     *
     * @throws ConfigurationException
     */
    public function setUseTLS($bool)
    {
        $bool = (bool) $bool;
        $this->useTLS = $bool;
    }

    /**
     * Returns the use TLS option.
     *
     * @return bool
     */
    public function getUseTLS()
    {
        return $this->useTLS;
    }

    /**
     * Sets the domain controllers option.
     *
     * @param array $hosts
     *
     * @throws ConfigurationException
     */
    public function setDomainControllers(array $hosts)
    {
        if (count($hosts) === 0) {
            $message = 'You must specify at least one domain controller.';

            throw new ConfigurationException($message);
        }

        $this->domainControllers = $hosts;
    }

    /**
     * Returns the domain controllers option.
     *
     * @return array
     */
    public function getDomainControllers()
    {
        return $this->domainControllers;
    }

    /**
     * Sets the account prefix option.
     *
     * @param string $suffix
     */
    public function setAccountPrefix($suffix)
    {
        $this->accountPrefix = (string) $suffix;
    }

    /**
     * Sets the account suffix option.
     *
     * @param string $suffix
     */
    public function setAccountSuffix($suffix)
    {
        $this->accountSuffix = (string) $suffix;
    }

    /**
     * Returns the account prefix option.
     *
     * @return string|null
     */
    public function getAccountPrefix()
    {
        return $this->accountPrefix;
    }

    /**
     * Returns the account suffix option.
     *
     * @return string|null
     */
    public function getAccountSuffix()
    {
        return $this->accountSuffix;
    }

    /**
     * Sets the administrators username option.
     *
     * @param string $username
     */
    public function setAdminUsername($username)
    {
        $this->adminUsername = (string) $username;
    }

    /**
     * Returns the administrator username option.
     *
     * @return string|null
     */
    public function getAdminUsername()
    {
        return $this->adminUsername;
    }

    /**
     * Sets the administrators password option.
     *
     * @param string $password
     */
    public function setAdminPassword($password)
    {
        $this->adminPassword = (string) $password;
    }

    /**
     * Returns the administrators password option.
     *
     * @return string|null
     */
    public function getAdminPassword()
    {
        return $this->adminPassword;
    }

    /**
     * Sets the administrators account suffix option.
     *
     * @param $suffix
     */
    public function setAdminAccountSuffix($suffix)
    {
        $this->adminAccountSuffix = (string) $suffix;
    }

    /**
     * Returns the administrators account suffix option.
     *
     * @return string|null
     */
    public function getAdminAccountSuffix()
    {
        return $this->adminAccountSuffix;
    }

    /**
     * Returns the administrators credentials.
     *
     * @return array
     */
    public function getAdminCredentials()
    {
        return [
            $this->adminUsername,
            $this->adminPassword,
            $this->adminAccountSuffix,
        ];
    }

    /**
     * Fills each configuration option with the supplied array.
     *
     * @param array|Traversable $options
     */
    protected function fill($options = [])
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s expects an array or Traversable argument; received "%s"',
                    __METHOD__,
                    (is_object($options) ? get_class($options) : gettype($options))
                )
            );
        }

        foreach ($options as $key => $value) {
            $method = 'set'.$this->normalizeKey($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /**
     * Normalize array key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function normalizeKey($key)
    {
        $key = str_replace('_', '', strtolower($key));

        return $key;
    }
}
