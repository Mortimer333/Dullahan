HTTP Models shouldn't be depending on the framework if we are trying to achieve framework-agnostic system.
Make them not dependent on the framework.
```php
use Symfony\Component\Validator\Constraints as Assert;
```
