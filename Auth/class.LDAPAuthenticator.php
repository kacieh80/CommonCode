<?php
/**
 * LDAPAuthenticator class
 *
 * This file describes the LDAPAuthenticator class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Auth;

/** We need the FlipsideSettings class to determine how to connect to the LDAP server */
if(isset($GLOBALS['FLIPSIDE_SETTINGS_LOC']))
{
    require_once($GLOBALS['FLIPSIDE_SETTINGS_LOC'].'/class.FlipsideSettings.php');
}
else
{
    require_once('/var/www/secure_settings/class.FlipsideSettings.php');
}

/** 
 * Sort the provided array by the keys in $orderby 
 *
 * @param array $array The array to sort
 * @param array $orderby An array of keys to sort the array by
 */
function sort_array(&$array, $orderby)
{
    $count = count($array);
    $keys  = array_keys($orderby);
    for($i = 0; $i < $count; $i++)
    {
        for($j = $i; $j < $count; $j++)
        {
            $d = strcasecmp($array[$i][$keys[0]][0], $array[$j][$keys[0]][0]);
            switch($orderby[$keys[0]])
            {
                case 1:
                    if($d > 0) swap($array, $i, $j);
                    break;
                case 0:
                    if($d < 0) swap($array, $i, $j);
                    break;
            }
        }
    }
}

/**
 * Swap two elements of the provided array
 *
 * @param array $array The array to swap values in
 * @param mixed $i The key of the first element to swap
 * @param mixed $j The key of the second element to swap
 */
function swap(&$array, $i, $j)
{
    $tmp = $array[$i];
    $array[$i] = $array[$j];
    $array[$j] = $tmp;
}

/**
 * An Authenticator class which uses LDAP as its backend storage mechanism
 */
class LDAPAuthenticator extends Authenticator
{
    /** The URL for the LDAP host */
    private $host;
    /** The base DN for all users in the LDAP server */
    public  $user_base;
    /** The base DN for all groups in the LDAP server */
    public  $group_base;
    /** The DN to use to bind if not binding as the user */
    private $bind_dn;
    /** The password to use to bind if not binding as the user */
    private $bind_pass;

    /**
     * Create an LDAP Authenticator
     *
     * @param array $params Parementers needed to initialize the authenticator
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->host       = $this->getHostParam($params);
        $this->user_base  = $this->getParam($params, 'user_base');
        $this->group_base = $this->getParam($params, 'group_base');
        $this->bind_dn    = $this->getParam($params, 'bind_dn', '$ldap_auth', 'read_write_pass');
        $this->bind_pass  = $this->getParam($params, 'bind_pass', '$ldap_auth', 'read_write_user');
    }

    /**
     * Return the host string for this authenticator
     *
     * @param array $params The initial parameters of the authenticator
     *
     * @return string The host string
     *
     * @SuppressWarnings("StaticAccess")
     */
    private function getHostParam($params)
    {
        if(isset($params['host']))
        {
            return $params['host'];
        }
        if(isset(\FlipsideSettings::$ldap['proto']))
        {
            return \FlipsideSettings::$ldap['proto'].'://'.\FlipsideSettings::$ldap['host'];
        }
        return \FlipsideSettings::$ldap['host'];
    }

    /**
     * Return the required paramter for this authenticator
     *
     * @param array  $params The initial parameters of the authenticator
     * @param string $paramName The name of the parameter in the $paramsArray
     * @param string $settingsLocation The location in the FlipsideSettings class
     * @param string $settingsName The name in the FlipsideSettings class
     *
     * @return mixed The paramter value
     *
     * @SuppressWarnings("StaticAccess")
     */
    private function getParam($params, $paramName, $settingsLocation = '$ldap', $settingsName = false)
    {
        if($settingsName === false)
        {
            $settingsName = $paramName;
        }
        if(isset($params[$paramName]))
        {
            return $params[$paramName];
        }
        if($settingsLocation === '$ldap')
        {
            return \FlipsideSettings::$ldap[$settingsName];
        }
        return \FlipsideSettings::$ldap_auth[$settingsName];
    }

    /**
     * Return an instance to the \LDAP\LDAPServer object instance
     *
     * @param boolean $bind_write Should we be able to write to the server?
     *
     * @return \LDAP\LDAPServer|false The server instance if the binding was successful, otherwise false
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function get_and_bind_server($bind_write = false)
    {
        $server = \LDAP\LDAPServer::getInstance();
        $server->user_base = $this->user_base;
        $server->group_base = $this->group_base;
        $server->connect($this->host);
        if($bind_write === false)
        {
            $ret = $server->bind();
        }
        else
        {
            $ret = $server->bind($this->bind_dn, $this->bind_pass);
        }
        if($ret === false)
        {
            return false;
        }
        return $server;
    }

    /**
     * Log the user in provided the credetials
     *
     * @param string $username The UID or email address for the user
     * @param string $password The password for the user
     *
     * @return mixed False if the login failed and array otherwise
     */
    public function login($username, $password)
    {
        $server = $this->get_and_bind_server();
        if($server === false)
        {
            return false;
        }
        $filter = new \Data\Filter("uid eq $username or mail eq $username");
        $user = $server->read($this->user_base, $filter);
        if($user === false || count($user) === 0)
        {
            return false;
        }
        $user = $user[0];
        $server->unbind();
        $ret = $server->bind($user->dn, $password);
        if($ret !== false)
        {
            return array('res'=>true, 'extended'=>$user); 
        }
        return false;
    }

    /**
     * Does this array represent a successful login?
     *
     * @param array $data The array data stored in the session after a login call
     *
     * @return boolean True if the user is logged in, false otherwise
     */
    public function isLoggedIn($data)
    {
        if(isset($data['res']))
        {
            return $data['res'];
        }
        return false;
    }

    /**
     * Obtain the currently logged in user from the session data
     *
     * @param \stdClass	$data The AuthData from the session
     *
     * @return \Auth\LDAPUser The LDAPUser represented by this data
     */
    public function getUser($data)
    {
        return new LDAPUser($data);
    }

    public function getGroupByName($name)
    {
        $server = $this->get_and_bind_server();
        if($server === false)
        {
            return false;
        }
        return LDAPGroup::from_name($name, $server);
    }

    public function getGroupsByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        $server = $this->get_and_bind_server();
        if($server === false)
        {
            return false;
        }
        if($filter === false)
        {
            $filter = new \Data\Filter('cn eq *');
        }
        $groups = $server->read($this->group_base, $filter);
        if($groups === false)
        {
            return false;
        }
        $this->processFilteringParams($groups, $select, $top, $skip, $orderby);
        $count = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            $groups[$i] = new LDAPGroup($groups[$i]);
            if($select !== false)
            {
                $groups[$i] = json_decode(json_encode($groups[$i]), true);
                $groups[$i] = array_intersect_key($groups[$i], array_flip($select));
            }
        }
        return $groups;
    }

    public function getActiveUserCount()
    {
        $server = $this->get_and_bind_server();
        if($server === false)
        {
            return false;
        }
        return $server->count($this->user_base);
    }

    /**
     # @param array           $data The array data to filter and sort
     * @param boolean|array   $select The fields to return
     * @param boolean|integer $top The number of records to return
     * @param boolean|integer $skip The number of records to skip
     * @param boolean|array   $orderby The fields to sort by
     */
    private function processFilteringParams(&$data, &$select, $top, $skip, $orderby)
    {
        if($orderby !== false)
        {
            sort_array($data, $orderby);
        }
        if($select !== false)
        {
            $select = array_flip($select);
        }
        if($skip !== false && $top !== false)
        {
            $data = array_slice($data, $skip, $top);
        }
        else if($top !== false)
        {
            $data = array_slice($data, 0, $top);
        }
        else if($skip !== false)
        {
            $data = array_slice($data, $skip);
        }
    }

    public function getUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        $server = $this->get_and_bind_server();
        if($server === false)
        {
            return false;
        }
        if($filter === false)
        {
            $filter = new \Data\Filter('cn eq *');
        }
        $users = $server->read($this->user_base, $filter, false, $select);
        if($users === false)
        {
            return false;
        }
        $this->processFilteringParams($users, $select, $top, $skip, $orderby);
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $tmp = new LDAPUser($users[$i]);
            if($select !== false)
            {
                $tmp = $tmp->jsonSerialize();
                $tmp = array_intersect_key($tmp, $select);
            }
            $users[$i] = $tmp;
        }
        return $users;
    }

    public function activatePendingUser($user)
    {
        $this->get_and_bind_server(true);
        $new_user = new LDAPUser();
        $new_user->setUID($user->getUID());
        $email = $user->getEmail();
        $new_user->setEmail($email);
        $pass = $user->getPassword();
        if($pass !== false)
        {
            $new_user->setPass($pass);
        }
        $sn = $user->getLastName();
        if($sn !== false)
        {
            $new_user->setLastName($sn);
        }
        $givenName = $user->getGivenName();
        if($givenName !== false)
        {
            $new_user->setGivenName($givenName);
        }
        $hosts = $user->getLoginProviders();
        if($hosts !== false)
        {
            $count = count($hosts);
            for($i = 0; $i < $count; $i++)
            {
                $new_user->addLoginProvider($hosts[$i]);
            }
        }
        $ret = $new_user->flushUser();
        if($ret)
        {
            $user->delete();
        }
        $users = $this->getUsersByFilter(new \Data\Filter('mail eq '.$email));
        if($users === false || !isset($users[0]))
        {
            throw new \Exception('Error creating user!');
        }
        return $users[0];
    }

    public function getUserByResetHash($hash)
    {
        $users = $this->getUsersByFilter(new \Data\Filter("uniqueIdentifier eq $hash"));
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        return $users[0];
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
