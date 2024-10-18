# FyreAuth

**FyreAuth** is a free, open-source authentication/authorization library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Auth](#auth)
- [Identity](#identity)
- [Access](#access)
- [Policy Registry](#policy-registry)
- [Policies](#policies)
- [Authenticators](#authenticators)
    - [Cookie](#cookie)
    - [Session](#session)
    - [Token](#token)
- [Middleware](#middleware)



## Installation

**Using Composer**

```
composer require fyre/auth
```

In PHP:

```php
use Fyre\Auth\Auth;
```


## Auth

The *Auth* class provides the basis for authentication.

```php
$auth = new Auth();
```

**Instance**

Load a shared *Auth* instance.

```php
$auth = Auth::instance();
```

**Set Instance**

Set a shared *Auth* instance.

- `$instance` is an *Auth*, or a *Closure* that returns an *Auth*.

```php
Auth::setInstance($instance);
```

### Auth Methods

**Add Authenticator**

- `$authenticator` is an [*Authenticator*](#authenticators).
- `$key` is a string representing the authenticator key, and will default to the [*Authenticator*](#authenticators) class name.

```php
$auth->addAuthenticator($authenticator, $key);
```

**Attempt**

Attempt to login as a user.

- `$identifier` is a string representing the user identifier.
- `$password` is a string representing the user password.
- `$rememberMe` is a boolean indicating whether the user should be remembered, and will default to *false*.

```php
$user = $auth->attempt($identifier, $password, $rememberMe);
```

**Authenticator**

Get an authenticator by key.

- `$key` is a string representing the authenticator key.

```php
$authenticator = $auth->authenticator($key);
```

**Authenticators**

Get the authenticators.

```php
$authenticators = $auth->authenticators();
```

**Is Logged In**

Determine if the current user is logged in.

```php
$isLoggedIn = $auth->isLoggedIn();
```

**Login**

Login as a user.

- `$user` is an [*Entity*](https://github.com/elusivecodes/FyreEntity) representing the user.
- `$rememberMe` is a boolean indicating whether the user should be remembered, and will default to *false*.

```php
$auth->login($user, $rememberMe);
```

**Logout**

Logout the current user.

```php
$auth->logout();
```

**User**

Get the current user.

```php
$user = $auth->user();
```


## Identity

The *Identity* class provides the basis for user identification.

```php
use Fyre\Auth\Identity;
```

**Attempt**

Attempt to identify a user.

- `$identifier` is a string representing the user identifier.
- `$password` is a string representing the user password.

```php
$user = Identity::attempt($identifier, $password);
```

**Get Identifier Fields**

Get the user identifier fields.

```php
$identifierFields = Identity::getIdentifierFields();
```

**Get Model**

Get the identity [*Model*](https://github.com/elusivecodes/FyreORM#models).

```php
$model = Identity::getModel();
```

**Get Password Field**

Get the user password field.

```php
$passwordField = Identity::getPasswordField();
```

**Identify**

Find an identity by identifier.

- `$identifier` is a string representing the user identifier.

```php
$user = Identity::identify($identifier);
```

**Set Identifier Fields**

Get the user identifier fields.

- `$identifierFields` is an array containing the user identifier fields.

```php
Identity::setIdentifierFields($identifierFields);
```

**Set Model**

Set the identity [*Model*](https://github.com/elusivecodes/FyreORM#models).

- `$model` is a [*Model*](https://github.com/elusivecodes/FyreORM#models).

```php
Identity::setModel($model);
```

**Set Password Field**

Get the user password field.

- `$passwordField` is an string representing the user password field.

```php
Identity::setPasswordField($passwordField);
```


## Access

The *Access* class provides the basis for authorization.

```php
use Fyre\Auth\Access;
```

**After**

Execute a callback after checking rules.

- `$afterCallback` is a *Closure* that accepts the current user, access rule name, current result and any additional arguments.

```php
Access::after($afterCallback);
```

**Allows**

Check whether an access rule is allowed.

- `$rule` is a string representing the access rule name or [*Policy*](#policies) method.

Any additional arguments supplied will be passed to the access rule callback or [*Policy*](#policies) method.

```php
$result = Access::allows($rule, ...$args);
```

**Any**

Check whether any access rule is allowed.

- `$rules` is an array containing access rule names or [*Policy*](#policies) methods.

Any additional arguments supplied will be passed to the access rule callbacks or [*Policy*](#policies) methods.

```php
$result = Access::any($rules, ...$args);
```

**Authorize**

Authorize an access rule.

- `$rule` is a string representing the access rule name or [*Policy*](#policies) method.

Any additional arguments supplied will be passed to the access rule callback or [*Policy*](#policies) method.

```php
Access::authorize($rule, ...$args);
```

**Before**

Execute a callback before checking rules.

- `$beforeCallback` is a *Closure* that accepts the current user, access rule name and any additional arguments.

```php
Access::before($beforeCallback);
```

**Clear**

Clear all rules and callbacks.

```php
Access::clear();
```

**Define**

Define an access rule.

- `$rule` is a string representing the access rule name.
- `$callback` is a *Closure* that accepts the current user and any additional arguments.

```php
Access::define($rule, $callback);
```

**Denies**

Check whether an access rule is not allowed.

- `$rule` is a string representing the access rule name or [*Policy*](#policies) method.

Any additional arguments supplied will be passed to the access rule callback or [*Policy*](#policies) method.

```php
$result = Access::denies($rule, ...$args);
```

**None**

Check whether no access rule is allowed.

- `$rules` is an array containing access rule names or [*Policy*](#policies) methods.

Any additional arguments supplied will be passed to the access rule callbacks or [*Policy*](#policies) methods.

```php
$result = Access::none($rules, ...$args);
```


## Policy Registry

**Add Namespace**

Add a namespace for loading policies.

- `$namespace` is a string representing the namespace.

```php
PolicyRegistry::addNamespace($namespace);
```

**Clear**

Clear all namespaces and policies.

```php
PolicyRegistry::clear();
```

**Get Namespaces**

Get the namespaces.

```php
$namespaces = PolicyRegistry::namespaces();
```

**Has Namespace**

Determine if a namespace exists.

- `$namespace` is a string representing the namespace.

```php
$hasNamespace = PolicyRegistry::hasNamespace($namespace);
```

**Load**

Load a [*Policy*](#policies).

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.

```php
$policy = PolicyRegistry::load($alias);
```

**Map**

Map an alias to a [*Policy*](#policies) class name.

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.
- `$className` is a string representing the [*Policy*](#policies) class name.

```php
PolicyRegistry::map($alias, $className);
```

**Remove Namespace**

Remove a namespace.

- `$namespace` is a string representing the namespace.

```php
PolicyRegistry::removeNamespace($namespace);
```

**Unload**

Unload a policy.

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.

```php
PolicyRegistry::unload($alias);
```

**Use**

Load a shared [*Policy*](#policies) instance.

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.

```php
$policy = PolicyRegistry::use($alias);
```


## Policies

Custom policies can be created by extending the `\Fyre\Auth\Policy` class, suffixing the singular alias with "*Policy*".

Policy rules should be represented as methods on the class, that accept the current user and resolved [*Entity*](https://github.com/elusivecodes/FyreEntity) as arguments.

**Get Model**

Get the [*Model*](https://github.com/elusivecodes/FyreORM#models).

```php
$model = $policy->getModel();
```

**Resolve Entity**

Resolve an [*Entity*](https://github.com/elusivecodes/FyreEntity) from access rule arguments.

- `$args` is an array containing the access rule arguments.

```php
$entity = $policy->resolveEntity($args);
```


## Authenticators

Custom authenticators can be created by extending the `\Fyre\Auth\Authenticator` class, and overwriting the below methods as required.

**Authenticate**

Authenticate a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).

```php
$user = $authenticator->authenticate($request);
```

**Before Response**

Update the [*ClientResponse*](https://github.com/elusivecodes/FyreServer#client-responses) before sending to client.

- `$response` is a [*ClientResponse*](https://github.com/elusivecodes/FyreServer#client-responses).

```php
$response = $authenticator->beforeResponse($response);
```

**Login**

Login as a user.

- `$user` is an [*Entity*](https://github.com/elusivecodes/FyreEntity) representing the user.
- `$rememberMe` is a boolean indicating whether the user should be remembered, and will default to *false*.

```php
$authenticator->login($user, $rememberMe);
```

**Logout**

Logout the current user.

```php
$authenticator->logout();
```


### Cookie

```php
use Fyre\Auth\Authenticators\CookieAuthenticator;
```

The cookie authenticator can be loaded using custom configuration.

- `$options` is an array containing configuration options.
    - `cookieName` is a string representing the cookie name, and will default to "*auth*".
    - `cookieOptions` is an array containing additional options for setting the cookie.
        - `expires` is a number representing the cookie lifetime, and will default to *null*.
        - `domain` is a string representing the cookie domain, and will default to "".
        - `path` is a string representing the cookie path, and will default to "*/*".
        - `secure` is a boolean indicating whether to set a secure cookie, and will default to *false*.
        - `httpOnly` is a boolean indicating whether to the cookie should be HTTP only, and will default to *true*.
        - `sameSite` is a string representing the cookie same site, and will default to "*Lax*".
    - `identifierField` is a string representing the identifier field of the user, and will default to "*email*".
    - `passwordfield` is a string representing the password field of the user, and will default to "*password*".
    - `salt` is a string representing the salt to use when generating the token, and will default to *null*.

```php
$authenticator = new CookieAuthenticator($options);
```

This authenticator is only active when the `$rememberMe` argument is set to *true* in the `Auth::attempt` and `Auth::login` methods.

### Session

```php
use Fyre\Auth\Authenticators\SessionAuthenticator;
```

The session authenticator can be loaded using custom configuration.

- `$options` is an array containing configuration options.
    - `sessionKey` is a string representing the session key, and will default to "*auth*".
    - `sessionField` is a string representing the session field of the user, and will default to "*id*".

```php
$authenticator = new SessionAuthenticator($options);
```

### Token

```php
use Fyre\Auth\Authenticators\TokenAuthenticator;
```

The token authenticator can be loaded using custom configuration.

- `$options` is an array containing configuration options.
    - `tokenHeader` is a string representing the token header name, and will default to "*Authorization*".
    - `tokenHeaderPrefix` is a string representing the token header prefix, and will default to "*Bearer*".
    - `tokenQuery` is a string representing the query parameter, and will default to *null*.
    - `tokenField` is a string representing the token field of the user, and will default to "*token*".

```php
$authenticator = new TokenAuthenticator($options);
```


## Middleware

### Auth Middleware

```php
use Fyre\Auth\Middleware\AuthMiddleware;
```

This middleware will authenticate using the loaded authenticators, and add any authentication headers to the response.

```php
$middleware = new AuthMiddleware();
```

**Process**

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$handler` is a [*RequestHandler*](https://github.com/elusivecodes/FyreMiddleware#request-handlers).

```php
$response = $middleware->process($request, $handler);
```

### Authenticated Middleware

This middleware will throw an [*UnauthorizedException*](https://github.com/elusivecodes/FyreError#http-exceptions) if the user is not authenticated.

```php
use Fyre\Auth\Middleware\AuthenticatedMiddleware;
```

```php
$middleware = new AuthenticatedMiddleware();
```

**Process**

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$handler` is a [*RequestHandler*](https://github.com/elusivecodes/FyreMiddleware#request-handlers).

```php
$response = $middleware->process($request, $handler);
```

### Authorized Middleware

This middleware will throw a [*ForbiddenException*](https://github.com/elusivecodes/FyreError#http-exceptions) if the user is not authorized.

```php
use Fyre\Auth\Middleware\AuthorizedMiddleware;
```

```php
$middleware = new AuthorizedMiddleware();
```

**Process**

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$handler` is a [*RequestHandler*](https://github.com/elusivecodes/FyreMiddleware#request-handlers).

```php
$response = $middleware->process($request, $handler, ...$args);
```

### Unauthenticated Middleware

This middleware will redirect a [*RedirectResponse*](https://github.com/elusivecodes/FyreServer#redirect-responses) if the user is authenticated.

```php
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
```

- `$redirect` is a [*Uri*](https://github.com/elusivecodes/FyreURI) or string representing the URI to redirect to, and will default to "*/*".

```php
$middleware = new UnauthenticatedMiddleware($redirect);
```

**Process**

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$handler` is a [*RequestHandler*](https://github.com/elusivecodes/FyreMiddleware#request-handlers).

```php
$response = $middleware->process($request, $handler);
```