<?php


namespace AmberCore\Rbac;


interface RbacDictionaryInterface
{
    public static function getRuleClassName(string $permission_name): ?string;

    public static function getPermissions(string $role): array;
}