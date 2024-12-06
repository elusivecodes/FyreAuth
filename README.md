# FyreAuth

**FyreAuth** is a free, open-source authentication/authorization library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Methods](#methods)
- [Access](#access)
- [Identifier](#identifier)
- [Authenticators](#authenticators)
    - [Cookie](#cookie)
    - [Session](#session)
    - [Token](#token)
- [Policy Registry](#policy-registry)
- [Policies](#policies)
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


## Basic Usage

- `$container` is a [*Container*](https://github.com/elusivecodes/FyreContainer).
- `$router` is a [*Router*](https://github.com/elusivecodes/FyreRouter).
- `$config` is a [*Config*](https://github.com/elusivecodes/FyreConfig).

```php
$auth = new Auth($container, $router, $config)
```

Default configuration options will be resolved from the "*Auth*" key in the [*Config*](https://github.com/elusivecodes/FyreConfig).

- `$options` is an array containing the configuration options.
    - `loginRoute` is a string representing the login [route](https://github.com/elusivecodes/FyreRouter) alias, and will default to "*login*".
    - `authenticators` is an array containing configuration options for the [*authenticators*](#authenticators).
    - `identifier` is an array containing configuration options for the [*Identifier*](#identifier).
        - `identifierFields` is string orn array containing the identifier field name(s), and will default to "*email*".
        - `passwordField` is a string representing the password field name, and will default to "*password*".
        - `modelAlias` is a string representing the model alias, and will default to "*Users*".

```php
$container->use(Config::class)->set('Auth', $options);
```

**Autoloading**

It is recommended to bind the *Auth* to the [*Container*](https://github.com/elusivecodes/FyreContainer) as a singleton.

```php
$container->singleton(Auth::class);
```

Any dependencies will be injected automatically when loading from the [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$auth = $container->use(Auth::class);
```


## Methods

**Access**

Get the [*Access*](#access).

```php
$access = $auth->access();
```

**Add Authenticator**

Add an [*Authenticator*](#authenticators).

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

**Get Login URL**

Get the login URL.

- `$redirect` is a string or [*Uri*](https://github.com/elusivecodes/FyreURI) representing the redirect URL, and will default to *null*.

```php
$url = $auth->getLoginUrl($redirect);
```

**Identifier**

Get the [*Identifier*](#identifier).

```php
$identifier = $auth->identifier();
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


## Access

**After**

Execute a callback after checking rules.

- `$afterCallback` is a *Closure* that accepts the current user, access rule name, current result and any additional arguments.

```php
$access->after($afterCallback);
```

**Allows**

Check whether an access rule is allowed.

- `$rule` is a string representing the access rule name or [*Policy*](#policies) method.

Any additional arguments supplied will be passed to the access rule callback or [*Policy*](#policies) method.

```php
$result = $access->allows($rule, ...$args);
```

**Any**

Check whether any access rule is allowed.

- `$rules` is an array containing access rule names or [*Policy*](#policies) methods.

Any additional arguments supplied will be passed to the access rule callbacks or [*Policy*](#policies) methods.

```php
$result = $access->any($rules, ...$args);
```

**Authorize**

Authorize an access rule.

- `$rule` is a string representing the access rule name or [*Policy*](#policies) method.

Any additional arguments supplied will be passed to the access rule callback or [*Policy*](#policies) method.

```php
$access->authorize($rule, ...$args);
```

**Before**

Execute a callback before checking rules.

- `$beforeCallback` is a *Closure* that accepts the current user, access rule name and any additional arguments.

```php
$access->before($beforeCallback);
```

**Clear**

Clear all rules and callbacks.

```php
$access->clear();
```

**Define**

Define an access rule.

- `$rule` is a string representing the access rule name.
- `$callback` is a *Closure* that accepts the current user and any additional arguments.

```php
$access->define($rule, $callback);
```

**Denies**

Check whether an access rule is not allowed.

- `$rule` is a string representing the access rule name or [*Policy*](#policies) method.

Any additional arguments supplied will be passed to the access rule callback or [*Policy*](#policies) method.

```php
$result = $access->denies($rule, ...$args);
```

**None**

Check whether no access rule is allowed.

- `$rules` is an array containing access rule names or [*Policy*](#policies) methods.

Any additional arguments supplied will be passed to the access rule callbacks or [*Policy*](#policies) methods.

```php
$result = $access->none($rules, ...$args);
```


## Identifier

**Attempt**

Attempt to identify a user.

- `$identifier` is a string representing the user identifier.
- `$password` is a string representing the user password.

```php
$user = $identifier->attempt($identifier, $password);
```

**Get Identifier Fields**

Get the user identifier fields.

```php
$identifierFields = $identifier->getIdentifierFields();
```

**Get Model**

Get the identity [*Model*](https://github.com/elusivecodes/FyreORM#models).

```php
$model = $identifier->getModel();
```

**Get Password Field**

Get the user password field.

```php
$passwordField = $identifier->getPasswordField();
```

**Identify**

Find an identity by identifier.

- `$identifier` is a string representing the user identifier.

```php
$user = $identifier->identify($identifier);
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

- `$auth` is an *Auth*.
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
$authenticator = new CookieAuthenticator($auth, $options);
```

This authenticator is only active when the `$rememberMe` argument is set to *true* in the `$auth->attempt` or `$auth->login` methods.

### Session

```php
use Fyre\Auth\Authenticators\SessionAuthenticator;
```

The session authenticator can be loaded using custom configuration.

- `$auth` is an *Auth*.
- `$session` is a [*Session*](https://github.com/elusivecodes/FyreSession).
- `$options` is an array containing configuration options.
    - `sessionKey` is a string representing the session key, and will default to "*auth*".
    - `sessionField` is a string representing the session field of the user, and will default to "*id*".

```php
$authenticator = new SessionAuthenticator($auth, $session, $options);
```

### Token

```php
use Fyre\Auth\Authenticators\TokenAuthenticator;
```

The token authenticator can be loaded using custom configuration.

- `$auth` is an *Auth*.
- `$options` is an array containing configuration options.
    - `tokenHeader` is a string representing the token header name, and will default to "*Authorization*".
    - `tokenHeaderPrefix` is a string representing the token header prefix, and will default to "*Bearer*".
    - `tokenQuery` is a string representing the query parameter, and will default to *null*.
    - `tokenField` is a string representing the token field of the user, and will default to "*token*".

```php
$authenticator = new TokenAuthenticator($auth, $options);
```


## Policy Registry

```php
use Fyre\Auth\PolicyRegistry;
```

- `$container` is a [*Container*](https://github.com/elusivecodes/FyreContainer).
- `$inflector` is an [*Inflector*](https://github.com/elusivecodes/FyreInflector).

```php
$policyRegistry = new PolicyRegistry($container, $inflector);
```

**Add Namespace**

Add a namespace for loading policies.

- `$namespace` is a string representing the namespace.

```php
$policyRegistry->addNamespace($namespace);
```

**Build**

Build a [*Policy*](#policies).

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.

```php
$policy = $policyRegistry->build($alias);
```

**Clear**

Clear all namespaces and policies.

```php
$policyRegistry->clear();
```

**Get Namespaces**

Get the namespaces.

```php
$namespaces = $policyRegistry->namespaces();
```

**Has Namespace**

Determine if a namespace exists.

- `$namespace` is a string representing the namespace.

```php
$hasNamespace = $policyRegistry->hasNamespace($namespace);
```

**Map**

Map an alias to a [*Policy*](#policies) class name.

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.
- `$className` is a string representing the [*Policy*](#policies) class name.

```php
$policyRegistry->map($alias, $className);
```

**Remove Namespace**

Remove a namespace.

- `$namespace` is a string representing the namespace.

```php
$policyRegistry->removeNamespace($namespace);
```

**Resolve Alias**

Resolve a modal alias.

- `$alias` is a model alias or class name.

```php
$alias = $policyRegistry->resolveAlias($alias);
```

**Unload**

Unload a policy.

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.

```php
$policyRegistry->unload($alias);
```

**Use**

Load a shared [*Policy*](#policies) instance.

- `$alias` is a string representing the [*Model*](https://github.com/elusivecodes/FyreORM#models) alias or class name.

```php
$policy = $policyRegistry->use($alias);
```


## Policies

Policies can be created by suffixing the singular model alias with "*Policy*" as the class name.

Policy rules should be represented as methods on the class, that accept the current user and resolved [*Entity*](https://github.com/elusivecodes/FyreEntity) as arguments.


## Middleware

### Auth Middleware

```php
use Fyre\Auth\Middleware\AuthMiddleware;
```

This middleware will authenticate using the loaded authenticators, and add any authentication headers to the response.

- `$auth` is an *Auth*.

```php
$middleware = new AuthMiddleware($auth);
```

Any dependencies will be injected automatically when loading from the [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$middleware = $container->use(AuthMiddleware::class);
```

**Handle**

Handle a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$next` is a *Closure*.

```php
$response = $middleware->handle($request, $next);
```

This method will return a [*ClientResponse*](https://github.com/elusivecodes/FyreServer#client-responses).

### Authenticated Middleware

```php
use Fyre\Auth\Middleware\AuthenticatedMiddleware;
```

This middleware will throw an [*UnauthorizedException*](https://github.com/elusivecodes/FyreError#http-exceptions) or return a login [*RedirectResponse*](https://github.com/elusivecodes/FyreServer#redirect-responses) if the user is not authenticated.

- `$auth` is an *Auth*.

```php
$middleware = new AuthenticatedMiddleware($auth);
```

Any dependencies will be injected automatically when loading from the [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$middleware = $container->use(AuthenticatedMiddleware::class);
```

**Handle**

Handle a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$next` is a *Closure*.

```php
$response = $middleware->handle($request, $next);
```

This method will return a [*ClientResponse*](https://github.com/elusivecodes/FyreServer#client-responses).

### Authorized Middleware

```php
use Fyre\Auth\Middleware\AuthorizedMiddleware;
```

This middleware will throw a [*ForbiddenException*](https://github.com/elusivecodes/FyreError#http-exceptions) or a login [*RedirectResponse*](https://github.com/elusivecodes/FyreServer#redirect-responses) if the user is not authorized.

- `$auth` is an *Auth*.

```php
$middleware = new AuthorizedMiddleware($auth);
```

Any dependencies will be injected automatically when loading from the [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$middleware = $container->use(AuthorizedMiddleware::class);
```

**Handle**

Handle a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$next` is a *Closure*.

```php
$response = $middleware->handle($request, $next);
```

This method will return a [*ClientResponse*](https://github.com/elusivecodes/FyreServer#client-responses).

### Unauthenticated Middleware

```php
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
```

This middleware will throw a [*NotFoundException*](https://github.com/elusivecodes/FyreError#http-exceptions) if the user is authenticated.

- `$auth` is an *Auth*.

```php
$middleware = new UnauthenticatedMiddleware($auth);
```

Any dependencies will be injected automatically when loading from the [*Container*](https://github.com/elusivecodes/FyreContainer).

```php
$middleware = $container->use(UnauthenticatedMiddleware::class);
```

**Handle**

Handle a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).

- `$request` is a [*ServerRequest*](https://github.com/elusivecodes/FyreServer#server-requests).
- `$next` is a *Closure*.

```php
$response = $middleware->handle($request, $next);
```

This method will return a [*ClientResponse*](https://github.com/elusivecodes/FyreServer#client-responses).