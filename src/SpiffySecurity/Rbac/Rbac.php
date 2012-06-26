<?php

namespace SpiffySecurity\Rbac;

use InvalidArgumentException;

class Rbac extends AbstractIterator
{
    /**
     * Add a child.
     *
     * @param string|AbstractRole $child
     * @return AbstractRole
     * @throws \InvalidArgumentException
     */
    public function addRole($child, $parents = null)
    {
        if (is_string($child)) {
            $child = new Role($child);
        }
        if (!$child instanceof AbstractRole) {
            throw new InvalidArgumentException(
                'Child must be a string or instance of SpiffySecurity\Role\AbstractRole'
            );
        }

        if ($parents) {
            foreach((array) $parents as $parent) {
                $this->getRole($parent)->addChild($child);
            }
        }

        $this->children[] = $child;
        return $this;
    }

    /**
     * Is a child with $name registered?
     *
     * @param $name
     * @return bool
     */
    public function hasRole($name)
    {
        try {
            $this->getRole($name);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get a child.
     *
     * @param string $name
     * @return AbstractRole
     * @throws \InvalidArgumentException
     */
    public function getRole($name)
    {
        $it = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $leaf) {
            if ($leaf->getName() == $name) {
                return $leaf;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'No child with name "%s" could be found',
            $name
        ));
    }

    /**
     * Checks if a role is allowed to access permission.
     *
     * @param string $role
     * @param string $permission
     * @return bool
     */
    public function isGranted($role, $permission)
    {
        $role = $this->getRole($role);
        if ($role->hasPermission($permission)) {
            return true;
        }

        $it = new \RecursiveIteratorIterator($role, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $leaf) {
            if ($leaf->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
}