# Composer Install
```shell
composer require "amber-core/rbac"
```

# Using

## RbacManager Using

```php
if($access_manager->canUser(
    RbacDictionary::PERMISSION_WORKER_DELETE,
    $user,
    ['worker' => $worker]
))
{
    $worker->delete();
}
```

### Access Manager Sample

```php
class AccessManager
{
    private $rbac;
    
    public function __construct()
    {
        $this->rbac = new \AmberCore\Rbac\RbacManager($this->getRbacDictionary());
    }

    public function getRbacDictionary(): \AmberCore\Rbac\RbacDictionaryInterface
    {
        return new RbacDictionary();
    }
    
    public function canUser(
        string $permission_name,
        \AmberCore\Rbac\UserRbacInterface $user,
        ?array $arguments
    ): bool 
    {
        return $this->rbac->isGranted($permission_name, $user, $arguments); 
    }
}
```

### RbacDictionary Sample
```php
class RbacDictionary implements RbacDictionaryInterface
{

    public const
        // Workers Permissions
        PERMISSION_WORKER_CREATE = 'worker_create',
        PERMISSION_WORKER_READ = 'worker_read',
        PERMISSION_WORKER_UPDATE = 'worker_update',
        PERMISSION_WORKER_DELETE = 'worker_delete',
        
        PERMISSION_WORKER_LIST_VIEW = 'worker_list_view',

        PERMISSIONS =
        [
            UserRoleDictionary::ROLE_ADMIN => [
                //Admin can everything that can Accountant
                'role' => UserRoleDictionary::ROLE_ACCOUNTANT
            ],
            UserRoleDictionary::ROLE_ACCOUNTANT => [
                // Accountant can everything that can User plus it own permissions
                'role' => UserRoleDictionary::ROLE_USER,
                self::PERMISSION_WORKER_CREATE,
                self::PERMISSION_WORKER_UPDATE,
                self::PERMISSION_WORKER_DELETE,
            ],
            UserRoleDictionary::ROLE_USER => [
                self::PERMISSION_WORKER_LIST_VIEW,
                self::PERMISSION_WORKER_READ,
            ]
        ],

        RULES =
        [
            // If there is rule for Permission then rbac will execute rule
            // otherwise rbac will return true if permission in the rule description
            // or false if not
            self::PERMISSION_WORKER_UPDATE => WorkerUpdateRule::class,
            self::PERMISSION_WORKER_DELETE => WorkerDeleteRule::class,
            self::PERMISSION_WORKER_READ => WorkerViewRule::class,
        ];

    public static function getPermissions(string $role): array
    {
        return self::PERMISSIONS[$role];
    }

    public static function getRuleClassName(string $permission_name): ?string
    {
        if (array_key_exists($permission_name, self::RULES))
        {
            return self::RULES[$permission_name];
        }

        return null;
    }
}
```

### Rule Sample

```php
class WorkerDeleteRule extends \AmberCore\Rbac\Rules\AbstractRule
{
    /**
    * @param UserEntity $user
    * @param array|null $arguments
    * @return bool
    */
    public function execute(\AmberCore\Rbac\UserRbacInterface $user, ?array $arguments): bool
    {
        /** @var UserEntity $worker */
        $worker = $arguments['worker'];

        return
            // return true if they are in the one company
            $this->checkRule(
                IsInOneCompanyRule::class,
                $user,
                ['user' => $worker]
            ) &&
            // return true if user is manager for this worker
            $user->isManagerForWorker($worker);
    }
}

class IsInOneCompanyRule extends \AmberCore\Rbac\Rules\AbstractRule
{
    /**
    * @param UserEntity $user
    * @param array|null $arguments
    * @return bool
    */
    public function execute(\AmberCore\Rbac\UserRbacInterface $user, ?array $arguments): bool
    {
        /** @var UserEntity $target_user */
        $target_user = $arguments['user'];

        return $user->getCompany()->getId() === $target_user->getCompany()->getId();
    }
}
```