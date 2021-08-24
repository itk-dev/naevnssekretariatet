# Deletion

This being a case management system means we have to be cautious
when deleting data. In fact, we want to avoid actual deletion of
documents, board members etc, as citizen at any point can ask
for access to records (Danish: aktindsigt). 

For this reason we introduce the `SoftDeletableEntity` trait. 

## Useful links

* [PHP traits](https://www.php.net/manual/en/language.oop5.traits.php)

## The trait

The idea behind the `SoftDeletableEntity` trait is very simple. Rather than deleting
data we add a boolean property called `softDeleted`, which is then
`true` or `false` depending on whether it is considered deleted or not.

```php
<?php

namespace App\Traits;

trait SoftDeletableEntity
{
    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $softDeleted = false;

    public function getSoftDeleted(): ?bool
    {
        return $this->softDeleted;
    }

    public function setSoftDeleted(bool $softDeleted): self
    {
        $this->softDeleted = $softDeleted;

        return $this;
    }
}

```

### Using the trait

To use it simply import the trait and use it as seen below.

```php
<?php

namespace App\Entity;

use App\Traits\SoftDeletableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=SomeEntityRepository::class)
 */
class SomeEntity
{
    use SoftDeletableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    public function getId(): ?UuidV4
    {
        return $this->id;
    }
    
    // More properties
}
```