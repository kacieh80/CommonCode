<?php
/**
 * User class
 *
 * This file describes the User classes
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Auth;

/**
 * A class to abstract access to Users regardless of the Authentication type used.
 *
 * This class is the primary method to access user information.
 * 
 * @property string $uid The user's ID or name
 * @property string $mail The user's email address
 * @property string $sn The user's surname (last name)
 * @property string $givenName The user's given name (first name)
 * @property array $host The service's the user can use to login
 */
class User extends \SerializableObject
{
    /**
     * An array to cache the title to string mappings so that they don't need to be pulled from the database
     * everytime
     */ 
    public static $titlenames = null;

    /**
     * Is this user in the Group or a child of that group?
     *
     * @param string $name The name of the group to check if the user is in
     *
     * @return boolean True if the user is in the group, false otherwise
     */
    public function isInGroupNamed($name)
    {
        return false;
    }

    public function __get($propName)
    {
        return false;
    }

    public function __set($propName, $value)
    {
    }

    public function __isset($propName)
    {
        return false;
    }

    /**
     * The list of titles for the user
     *
     * @return boolean|array The user's title(s) in user friendly strings
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function getTitleNames()
    {
        $titles = $this->title;
        if($titles === false)
        {
            return false;
        }
        if(self::$titlenames === null)
        {
            $dataSet = \DataSetFactory::getDataSetByName('profiles');
            $dataTable = $dataSet['position'];
            $titlenames = $dataTable->read();
            self::$titlenames = array();
            $count = count($titlenames);
            for($i = 0; $i < $count; $i++)
            {
                self::$titlenames[$titlenames[$i]['short_name']] = $titlenames[$i];
            }
        }
        $count = count($titles);
        for($i = 0; $i < $count; $i++)
        {
            if(isset(self::$titlenames[$titles[$i]]))
            {
                $title = self::$titlenames[$titles[$i]];
                $titles[$i] = $title['name'];
            }
        }
        return $titles;
    }

    /**
     * The groups the user is a part of
     *
     * @return boolean|array The user's Auth\Group structures
     */
    public function getGroups()
    {
        return false;
    }

    /**
     * Add a supplemental login type that the user can use to login
     *
     * @param string $provider The hostname for the provider
     */
    public function addLoginProvider($provider)
    {
        if(isset($this->host))
        {
            $tmp = $this->host;
            $tmp[] = $provider;
            $this->host = $tmp;
        }
        else
        {
            $this->host = array($provider);
        }
    }

    /**
     * Can the user login with this provider?
     *
     * @param string $provider The hostname for the provider
     *
     * @return boolean true if they can login with the provider, false otherwise
     */
    public function canLoginWith($provider)
    {
        $hosts = $this->host;
        if($hosts === false)
        {
            return false;
        }
        $count = count($hosts);
        for($i = 0; $i < $count; $i++)
        {
            if(strcasecmp($hosts[$i], $provider) === 0)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the user's password without verifying the current password
     *
     * @param string $password The new user password
     *
     * @return boolean true if the user's password was changed, false otherwise
     */
    protected function setPass($password)
    {
        return false;
    }

    /**
     * Has the user completely filled out their user profile?
     *
     * @return boolean true if the user's profile is complete, false otherwise
     */
    public function isProfileComplete()
    {
        if($this->c === false || $this->postalAddress === false ||
           $this->postalCode === false || $this->l === false ||
           $this->st === false || $this->mobile === false)
        {
            return false;
        }
        return true;
    }

    /**
     * Validate that the user's password is the specified password
     *
     * @param string $password The user's current password
     *
     * @return boolean true if the user's password is correct, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function validate_password($password)
    {
        return false;
    }

    /**
     * Validate that the user's reset hash is the sepcified hash
     *
     * @param string $hash The user's reset hash
     *
     * @return boolean true if the user's hash is correct, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function validate_reset_hash($hash)
    {
        return false;
    }

    /**
     * Change the user's password, validating the old password or reset hash
     *
     * @param string $oldpass The user's original password or reset hash if $isHash is true
     * @param string $newpass The user's new password
     * @param boolean $isHash Is $old_pass a password or a hash
     *
     * @return boolean true if the user's password was changed, false otherwise
     */
    public function change_pass($oldpass, $newpass, $isHash = false)
    {
        if($isHash === false && $this->validate_password($oldpass) === false)
        {
            throw new \Exception('Invalid Password!', 3);
        }
        if($isHash === true && $this->validate_reset_hash($oldpass) === false)
        {
            throw new \Exception('Invalid Reset Hash!', 3);
        }
        if($this->setPass($newpass) === false)
        {
            throw new \Exception('Unable to set password!', 6);
        }
        return true;
    }

    /**
     * Allow write for the user
     */
    protected function enableReadWrite()
    {
        //Make sure we are bound in write mode
        $auth = \AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('Auth\LDAPAuthenticator');
        if($ldap !== false)
        {
            $ldap->get_and_bind_server(true);
        }
    }

    /**
     * Update the user password if required
     */
    private function editUserPassword($data)
    {
        if(isset($data->password))
        {
            if(isset($data->oldpass))
            {
                $this->change_pass($data->oldpass, $data->password);
                unset($data->oldpass);
            }
            else if(isset($data->hash))
            {
                $this->change_pass($data->hash, $data->password, true);
                unset($data->hash);
            }
            unset($data->password);
        }
    }

    private function editNames($data)
    {
        if(isset($data->displayName))
        {
            $this->displayName = $data->displayName;
            unset($data->displayName);
        }
        if(isset($data->givenName))
        {
            $this->givenName = $data->givenName;
            unset($data->givenName);
        }
        if(isset($data->sn))
        {
            $this->sn = $data->sn;
            unset($data->sn);
        }
        if(isset($data->cn))
        {
            $this->cn = $data->cn;
            unset($data->cn);
        }
    }

    private function checkForUnsettableElements($data)
    {
        if(isset($data->mail))
        {
            if($data->mail !== $this->mail)
            {
                throw new \Exception('Unable to change email!');
            }
            unset($data->mail);
        }
        if(isset($data->uid))
        {
            if($data->uid !== $this->uid)
            {
                throw new \Exception('Unable to change uid!');
            }
            unset($data->uid);
        }
    }

    private function editAddressElements($data)
    {
        if(isset($data->postalAddress))
        {
            $this->postalAddress = $data->postalAddress;
            unset($data->postalAddress);
        }
        if(isset($data->l))
        {
            $this->l = $data->l;
            unset($data->l);
        }
        if(isset($data->st))
        {
            $this->st = $data->st;
            unset($data->st);
        }
        if(isset($data->postalCode))
        {
            $this->postalCode = $data->postalCode;
            unset($data->postalCode);
        }
        if(isset($data->c))
        {
            $this->c = $data->c;
            unset($data->c);
        }
    }

    private function editOrganizationElements($data)
    {
        if(isset($data->o))
        {
            $this->o = $data->o;
            unset($data->o);
        }
        if(isset($data->title))
        {
            $this->title = $data->title;
            unset($data->title);
        }
        if(isset($data->ou))
        {
            $this->ou = $data->ou;
            unset($data->ou);
        }
    }

    /**
     * Modify the user given the provided data object
     *
     * @param stdClass $data The user's new data
     *
     * @return boolean true if the user's data was changed, false otherwise
     */
    public function editUser($data)
    {
        $this->enableReadWrite();

        $this->checkForUnsettableElements($data);
        $this->editUserPassword($data);
        $this->editNames($data);
        $this->editAddressElements($data);
        $this->editOrganizationElements($data);

        if(isset($data->jpegPhoto))
        {
            $this->jpegPhoto = base64_decode($data->jpegPhoto);
            unset($data->jpegPhoto);
        }
        if(isset($data->mobile))
        {
            $this->mobile = $data->mobile;
            unset($data->mobile);
        }
    }

    /**
     * Obtain the user's password reset hash
     *
     * @return string|false A hash if available, false otherwise
     */
    public function getPasswordResetHash()
    {
        return false;
    }

    /**
     * Serialize the user data into a format usable by the json_encode method
     *
     * @return array A simple keyed array representing the user
     */
    public function jsonSerialize()
    {
        $user = array();
        $user['displayName'] = $this->displayName;
        $user['givenName'] = $this->givenName;
        $user['jpegPhoto'] = base64_encode($this->jpegPhoto);
        $user['mail'] = $this->mail;
        $user['mobile'] = $this->mobile;
        $user['uid'] = $this->uid;
        $user['o'] = $this->o;
        $user['title'] = $this->title;
        $user['titlenames'] = $this->getTitleNames();
        $user['st'] = $this->st;
        $user['l'] = $this->l;
        $user['sn'] = $this->sn;
        $user['cn'] = $this->cn;
        $user['postalAddress'] = $this->postalAddress;
        $user['postalCode'] = $this->postalCode;
        $user['c'] = $this->c;
        $user['ou'] = $this->ou;
        $user['host'] = $this->host;
        $user['class'] = get_class($this);
        return $user;
    }

    /**
     * Serialize the user data into a VCARD 2.1 format
     *
     * @return string The VCARD for the user
     */
    public function getVcard()
    {
        $ret = "BEGIN:VCARD\nVERSION:2.1\n";
        $ret .= 'N:'.$this->sn.';'.$this->givenName."\n";
        $ret .= 'FN:'.$this->givenName."\n";
        $titles = $this->title;
        if($titles !== false)
        {
            $ret .= 'TITLE:'.implode(',', $titles)."\n";
        }
        $ret .= "ORG: Austin Artistic Reconstruction\n";
        $ret .= 'TEL;TYPE=MOBILE,VOICE:'.$this->mobile."\n";
        $ret .= 'EMAIL;TYPE=PREF,INTERNET:'.$this->mail."\n";
        $ret .= "END:VCARD\n";
        return $ret;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
