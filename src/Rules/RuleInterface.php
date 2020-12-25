<?php


namespace AmberCore\Rbac\Rules;


use AmberCore\Rbac\UserRbacInterface;

interface RuleInterface
{
    public function execute(UserRbacInterface $user, ?array $arguments): bool;
}