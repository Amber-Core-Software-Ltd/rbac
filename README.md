## Composer Install
```shell
composer require "amber-core/rbac"
```

## Examples

### RbacDictionary 
```injectablephp
class RbacDictionary implements RbacDictionaryInterface
{

    public const
        // Workers Permissions
        PERMISSION_WORKER_CREATE = 'worker_create',
        PERMISSION_WORKER_UPDATE = 'worker_update',
        PERMISSION_WORKER_VIEW = 'worker_view',
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
            ],
            UserRoleDictionary::ROLE_USER => [
                self::PERMISSION_WORKER_LIST_VIEW,
                self::PERMISSION_WORKER_VIEW,
            ]
        ],

        RULES =
        [
            // If there is rule for Permission then rbac will execute rule
            // otherwise rbac will return true if permission in the rule description
            // or false if not
            self::PERMISSION_WORKER_UPDATE => WorkerUpdateRule::class,
            self::PERMISSION_WORKER_VIEW => WorkerViewRule::class,
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

### RbacManager Using

```injectablephp

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
...

if($access_manager->canUser(
    RbacDictionary::PERMISSION_WORKER_DELETE,
    $user,
    ['worker_id' => $worker->getId()]
))
{
    $worker->delete();
}
```