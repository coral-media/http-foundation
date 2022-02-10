# http-foundation

Intended to override and/or extend [http-foundation](https://symfony.com/components/HttpFoundation) 
component functionality in Symfony development environments.

## Installation and configuration
Install this package using composer.

`composer require coral-media/http-foundation`

For further configuration check following sections.

## The EncryptedSessionProxy
Provides session encrypting for our session handler. Example provided below shows how to implement it using `PdoSessionhandler`.

After installation set up your `services.yaml` as follows:
```yaml
...
Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
    arguments:
        - '%env(resolve:DATABASE_URL)%'
session.handler.pdo:
    alias: 'Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler'

CoralMedia\Component\HttpFoundation\Session\Storage\Proxy\EncryptedSessionProxy:
    arguments:
        - '@session.handler.pdo'
        - '%env(resolve:SESSION_ENCRYPTION_KEY)%'
session.storage.proxy.encrypted:
    alias: 'CoralMedia\Component\HttpFoundation\Session\Storage\Proxy\EncryptedSessionProxy'
...
```

Dont forget to add your configuration in `config/packages/framework.yaml`
```yaml
framework:
...
    session:
        handler_id: session.storage.proxy.encrypted
        cookie_secure: auto
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native
        save_path: '%kernel.cache_dir%/sessions'
...
```
