<?php


namespace AmberCore\Rbac\Rules;


use AmberCore\Rbac\UserRbacInterface;

abstract class AbstractRule implements RuleInterface
{
    protected function checkRule(string $rule_class_name, UserRbacInterface $user, ?array $arguments): bool
    {
        return $this->createRule($rule_class_name)->execute($user, $arguments);
    }

    protected function createRule(string $rule_class_name): RuleInterface
    {
        return new $rule_class_name;
    }
}