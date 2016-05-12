<?php
namespace Auth;
require_once("/var/www/secure_settings/class.FlipsideSettings.php");

class LDAPGroup extends Group
{
    use LDAPCachableObject;

    private $ldap_obj;
    private $server;

    function __construct($data)
    {
        $this->ldap_obj = $data;
        $this->server = \LDAP\LDAPServer::getInstance();
        if(!is_object($data))
        {
            throw new \Exception('Unable to setup LDAPGroup!');
        }
    }

    public function getGroupName()
    {
        return $this->getFieldSingleValue('cn');
    }

    public function getDescription()
    {
        return $this->getFieldSingleValue('description');
    }

    public function setDescription($name)
    {
        return $this->setField('description', $name);
    }

    private function getMembersField()
    {
        $raw_members = $this->getField('member');
        if($raw_members === false)
        {
            $raw_members = $this->getField('uniquemember');
        }
        if($raw_members === false)
        {
            $raw_members = $this->getField('memberuid');
        }
        if(!isset($raw_members['count']))
        {
            $raw_members['count'] = count($raw_members);
        }
        return $raw_members;
    }

    private function getIDFromDN($dn)
    {
        $split = explode(',', $dn);
        if(strncmp('cn=', $split[0], 3) === 0)
        {
            return substr($split[0], 3);
        }
        return substr($split[0], 4);
    }

    public function getMemberUids($recursive=true)
    {
        $members = array();
        $raw_members = $this->getMembersField();
        for($i = 0; $i < $raw_members['count']; $i++)
        {
            if($recursive && strncmp($raw_members[$i], 'cn=', 3) === 0)
            {
                $child = self::from_dn($raw_members[$i], $this->server);
                if($child !== false)
                {
                    $members = array_merge($members, $child->members());
                }
            }
            else
            {
                array_push($members, $raw_members[$i]);
            }
        }
        $count = count($members);
        for($i = 0; $i < $count; $i++)
        {
            $members[$i] = $this->getIDFromDN($members[$i]);
        }
        return $members;
    }

    private function getObjectFromDN($dn)
    {
        $split = explode(',', $dn);
        if(strncmp('cn=', $dn, 3) === 0)
        {
            if(count($split) === 1)
            {
                return LDAPGroup::from_name($dn, $this->server);
            }
            return LDAPGroup::from_name(substr($split[0], 3), $this->server);
        }
        if(count($split) === 1)
        {
            return LDAPUser::from_name($dn, $this->server);
        }
        return LDAPUser::from_name(substr($split[0], 4), $this->server);
    }

    public function members($details=false, $recursive=true, $includeGroups=true)
    {
        $members = array();
        $raw_members = $this->getMembersField();
        for($i = 0; $i < $raw_members['count']; $i++)
        {
            if($recursive && strncmp($raw_members[$i], 'cn=', 3) === 0)
            {
                $child = self::from_dn($raw_members[$i], $this->server);
                if($child !== false)
                {
                    $members = array_merge($members, $child->members());
                }
            }
            else if($includeGroups === false && strncmp($raw_members[$i], 'cn=', 3) === 0)
            {
                //Drop this member
            }
            else
            {
                array_push($members, $raw_members[$i]);
            }
        }
        if($details === true)
        {
            $details = array();
            $count = count($members);
            for($i = 0; $i < $count; $i++)
            {
                $details[$i] = $this->getObjectFromDN($members[$i]);
            }
            unset($members);
            $members = $details;
        }
        return $members;
    }

    public function getNonMemebers($select=false)
    {
        $data = array();
        $group_filter = '(&(cn=*)(!(cn='.$this->getGroupName().'))';
        $user_filter = '(&(cn=*)';
        $members = $this->members();
        $count = count($members);
        for($i = 0; $i < $count; $i++)
        {
            $dn_comps = explode(',',$members[$i]);
            if(strncmp($members[$i], "uid=", 4) == 0)
            {
                $user_filter.='(!('.$dn_comps[0].'))';
            }
            else
            {
                $group_filter.='(!('.$dn_comps[0].'))';
            }
        }
        $user_filter.=')';
        $group_filter.=')';
        $groups = $this->server->read($this->server->group_base, $group_filter);
        $count = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            if($groups[$i] === false || $groups[$i] === null) continue;
            array_push($data, new LDAPGroup($groups[$i]));
        }
        $users = $this->server->read($this->server->user_base, $user_filter, false, $select);
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            array_push($data, new LDAPUser($users[$i]));
        } 
        return $data;
    }

    public function clearMembers()
    {
        if(isset($this->ldap_obj['member']))
        {
            $this->ldap_obj['member'] = array();
        }
        else if(isset($this->ldap_obj['uniquemember']))
        {
            $this->ldap_obj['uniquemember'] = array();
        }
        else if(isset($this->ldap_obj['memberuid']))
        {
            $this->ldap_obj['memberuid'] = array();
        }
    }

    public function addMember($name, $isGroup=false, $flush=true)
    {
        $dn = false;
        if($isGroup)
        {
            $dn = 'cn='.$name.','.$this->server->group_base;
        }
        else
        {
            $dn = 'uid='.$name.','.$this->server->user_base;
        }
        $raw_members = false;
        $propName = false;
        if(isset($this->ldap_obj['member']))
        {
            $raw_members = $this->ldap_obj['member'];
            $propName = 'member';
        }
        else if(isset($this->ldap_obj['uniquemember']))
        {
            $raw_members = $this->ldap_obj['uniquemember'];
            $propName = 'uniqueMember';
        }
        else if(isset($this->ldap_obj['memberuid']))
        {
            $raw_members = $this->ldap_obj['memberuid'];
            $propName = 'memberuid';
        }
        if(in_array($dn, $raw_members) || in_array($name, $raw_members))
        {
            return true;
        }
        if($propName === 'memberuid')
        {
            if($isGroup)
            {
                throw new \Exception('Unable to add a group as a child of this group type');
            }
            array_push($raw_members, $name);
        }
        else
        {
            array_push($raw_members, $dn);
        }
        $tmp = strtolower($propName);
        $this->ldap_obj->$tmp = $raw_members;
        if($flush === true)
        {
            $obj = array('dn'=>$this->ldap_obj->dn);
            $obj[$propName] = $raw_members;
            return $this->server->update($obj);
        }
        else
        {
            return true;
        }
    }

    static function from_dn($dn, $data=false)
    {
        if($data === false)
        {
            throw new \Exception('data must be set for LDAPGroup');
        }
        $group = $data->read($dn, false, true);
        if($group === false || !isset($group[0]))
        {
            return false;
        }
        return new static($group[0]);
    }

    static function from_name($name, $data=false)
    {
        if($data === false)
        {
            throw new \Exception('data must be set for LDAPGroup');
        }
        $filter = new \Data\Filter("cn eq $name");
	$group = $data->read($data->group_base, $filter);
        if($group === false || !isset($group[0]))
        {
            return false;
        }
        return new static($group[0]);
    }
}
?>
