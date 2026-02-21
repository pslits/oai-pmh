```skill
---
name: php-docblock
description: Write PHP docblocks for PHP 8.0+ following modern PHPDoc conventions. Use when (1) adding or updating docblocks on PHP classes, methods, or properties, (2) annotating ORM models with @property tags, (3) documenting service or manager method signatures, (4) generating PHPDoc for controllers, form requests, or service classes, or (5) asked to document PHP code. Triggers on phrases like "add docblocks", "document this class", "PHPDoc", "annotate model", or "type hints".
---

# PHP Docblock Writer

## Core Principle

PHP 8.0 native type declarations are the source of truth. Docblocks **complement** — never duplicate — what the language already expresses. Add a docblock only when it provides information beyond the signature.

**Minimal docblock philosophy for this project:**
- Let type hints speak (native types are the source of truth)
- Avoid duplication (don't repeat what's already in the signature)
- Add docblocks only when providing additional context
- Comprehensive documentation belongs in markdown files, not code comments
- Skip entire docblocks when they would only mirror the signature without adding value

### When to Write a Docblock

| Situation | Example |
|---|---|
| Description adds context | What a method does, not just its name |
| Type is more specific than the signature | `@return Collection<int, User>` vs `: Collection` |
| Array shape needs documenting | `@param array{name: string, age: int} $data` |
| Magic properties/methods exist | ORM model `@property`, service `@method` |
| `@throws` documents expected exceptions | `@throws ModelNotFoundException` |
| Deprecation notice | `@deprecated Use newMethod() instead` |

### When to Skip

- Signature already says everything and the method name is self-explanatory
- Simple getters/setters with typed properties
- Closures and short arrow functions with obvious behavior

## Tag Reference

### Standard Tags

```php
/**
 * Store a newly created resource.
 *
 * @param  StoreUserRequest  $request
 * @return JsonResponse
 *
 * @throws AuthorizationException
 */
public function store(StoreUserRequest $request): JsonResponse
```

Ordering rules:

1. Summary line (one sentence, no period if short)
2. Blank line
3. Long description (optional, wrap at 80 chars)
4. Blank line
5. Tags in this order: `@template`, `@param`, `@return`, `@throws`, `@deprecated`, `@see`

### Alignment

Align `@param` types and variable names into columns. Two spaces between type and variable:

```php
/**
 * @param  string       $name
 * @param  int          $age
 * @param  string|null  $email
 */
```

### Type Syntax

| PHP 8.0 native | Docblock equivalent (use only when adding information) |
|---|---|
| `string` | `string` |
| `?string` | `string\|null` (prefer explicit union in docblocks) |
| `int\|string` | `int\|string` |
| `array` | `array<string, mixed>`, `array{key: type}`, `list<int>` |
| `mixed` | Avoid — be specific when possible |
| `self` | `static` when return type supports chaining |
| `object` | Specific class name when known |

## Common Patterns

### ORM Models (Magic Properties and Methods)

Annotate every mapped column, cast, and relationship accessor as `@property` or `@property-read` on the class docblock:

```php
/**
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property Carbon|null $email_verified_at
 * @property string      $password
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @property-read Collection<int, Notification> $notifications
 * @property-read Collection<int, Role>         $roles
 *
 * @method static UserFactory  factory($count = null, $state = [])
 * @method static Builder|User query()
 * @method static Builder|User whereEmail(string $value)
 */
class User extends Model
```

Rules:
- Use `@property-read` for relationships and computed accessors
- Use `@property` for mapped/castable columns
- Use the cast target type (e.g., `Carbon` not `string` for date casts)
- Add `@method static` for query scopes and static factory methods

### Controller Methods

```php
/**
 * Display a listing of users.
 *
 * @return View
 */
public function index(): View

/**
 * Update the specified user.
 *
 * @param  UpdateUserRequest  $request
 * @param  User               $user
 * @return RedirectResponse
 *
 * @throws AuthorizationException
 */
public function update(UpdateUserRequest $request, User $user): RedirectResponse
```

For CRUD controllers, a one-line summary is enough — the method name (`index`, `store`, `show`, `update`, `destroy`) already communicates intent.

### Form Requests

```php
/**
 * Determine if the user is authorized.
 *
 * @return bool
 */
public function authorize(): bool

/**
 * Validation rules.
 *
 * @return array<string, ValidationRule|array<mixed>|string>
 */
public function rules(): array
```

### Service / Action Classes

```php
/**
 * Calculate the order total including tax and discounts.
 *
 * @param  Order                    $order
 * @param  Collection<int, Coupon>  $coupons
 * @return Money
 *
 * @throws InsufficientStockException
 */
public function calculateTotal(Order $order, Collection $coupons): Money
```

### Collections and Generics

Use PHPStan-style generic syntax:

```php
/** @var Collection<int, User> */

/** @return LengthAwarePaginator<User> */

/** @param  iterable<int, string>  $items */
```

Common generic forms:

| Type | Generic form |
|---|---|
| `Collection` | `Collection<TKey, TValue>` |
| `EloquentCollection` | `Collection<int, Model>` |
| `LengthAwarePaginator` | `LengthAwarePaginator<Model>` |
| `Builder` | `Builder<Model>` |
| `HasMany` | `HasMany<ChildModel>` |
| `BelongsTo` | `BelongsTo<ParentModel>` |
| `BelongsToMany` | `BelongsToMany<RelatedModel>` |

### Config and Array Shapes

```php
/**
 * @param  array{
 *     host: string,
 *     port: int,
 *     username: string,
 *     password: string,
 *     database?: string,
 * }  $config
 */
```

### Closures and Callables

```php
/** @param  Closure(User, int): bool  $callback */

/** @param  callable(Request): Response  $handler */
```

### Proxy / Facade `@method` Annotations

When a class proxies another via magic `__call`, document the proxied methods:

```php
/**
 * @method static CacheManager store(string|null $name = null)
 * @method static bool         has(string $key)
 * @method static mixed        get(string $key, mixed $default = null)
 * @method static bool         put(string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null)
 * @method static bool         forget(string $key)
 *
 * @see \Cache\CacheManager
 */
class Cache extends Proxy
```

Always add `@see` pointing to the underlying class.

## Inline Annotations

Use `/** @var Type */` for variables where the type cannot be inferred:

```php
/** @var User $user */
$user = $container->get(User::class);

/** @var Collection<int, Order> $orders */
$orders = $repository->findOrdersByUser($user);
```

Prefer single-line form for inline `@var`. Do not use `@var` when the type is already clear from context.

## Checklist

Before finalizing any docblock, verify:

- [ ] No tag duplicates the native type signature without adding specificity
- [ ] `@param` types match or refine the signature types
- [ ] `@return` is present when the return type is less specific than the docblock (e.g., `Collection` → `Collection<int, User>`)
- [ ] Array types use shape or generic syntax, never bare `array`
- [ ] `@throws` lists only exceptions the caller should handle
- [ ] Alignment is consistent within each block
- [ ] Summary line is concise and uses imperative mood ("Store", "Calculate", not "Stores", "This method calculates")
```
