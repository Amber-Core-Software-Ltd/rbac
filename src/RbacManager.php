<?php


namespace AmberCore\Rbac;


use AmberCore\Rbac\Rules\RuleInterface;

class RbacManager
{
    /** @var RbacDictionaryInterface */
    private $rbac_dictionary;

    public function __construct(RbacDictionaryInterface $rbac_dictionary)
    {
        $this->rbac_dictionary = $rbac_dictionary;
    }


    public function isGranted(string $permission_name, UserRbacInterface $user, ?array $arguments = null): bool
    {
        if (in_array($permission_name, $this->getRolesPermissions($user->getRole())))
        {
            if ($this->existRule($permission_name))
            {
                return $this->getRule($permission_name)->execute($user, $arguments);
            }
            return true;
        }

        return false;
    }

    private function existRule(string $permission_name): bool
    {
        return $this->getRuleClassName($permission_name) !== null;
    }

    private function getRule(string $permission_name): ?RuleInterface
    {
        $class_name = $this->getRuleClassName($permission_name);

        return new $class_name;
    }

    private function getRuleClassName(string $permission_name): ?string
    {
        return $this->rbac_dictionary::getRuleClassName($permission_name);
    }

    private function getRolesPermissions(string $role): array
    {
        $permissions = $this->rbac_dictionary::getPermissions($role);

        foreach ($permissions as $key => $value)
        {
            if (is_string($key) && $key === 'role')
            {
                unset($permissions[$key]);
                $permissions = array_merge($permissions, $this->getRolesPermissions($value));
            }
        }

        return array_unique($permissions);
    }
}