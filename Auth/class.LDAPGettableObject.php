<?php
namespace Auth;

trait LDAPGettableObject
{
    protected function getValueWithDefault($propName)
    {
        if(isset($this->valueDefaults[$propName]))
        {
            $tmp = $this->getFieldSingleValue($propName);
            if($tmp === false)
            {
                return $this->valueDefaults[$propName];
            }
            return $tmp;
        }
        return false;
    }

    protected function getMultiValueProp($propName)
    {
        if(in_array($propName, $this->multiValueProps))
        {
            $tmp = $this->getField($propName);
            if(isset($tmp['count']))
            {
                unset($tmp['count']);
            }
            return $tmp;
        }
        return false;
    }

    public function __get($propName)
    {
        $tmp = $this->getValueWithDefault($propName);
        if($tmp !== false)
        {
            return $tmp;
        }
        $tmp = $this->getMultiValueProp($propName);
        if($tmp !== false)
        {
            return $tmp;
        }
        return $this->getFieldSingleValue($propName);
    }
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */
