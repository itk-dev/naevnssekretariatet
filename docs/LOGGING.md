# Logging

To ensure a thorough logging during the cases
handled by this application we use
[doctrine events](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html).

This allows us to use already defined
[Lifecycle Events](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html#lifecycle-events),
and specifying our own logic when listening and handling such events.

Logging is done on case level.
In other words, anything related to a case must be logged
whether it is editing a party,
adding the case to an agenda or simply viewing the case.

To store the logs in our database we make a LogEntry entity
with the necessary fields. That is

* Case ID
* Entity
* Entity ID
* Action
* User
* Data
* Timestamp

The reason for the Entity and Entity ID columns are
to specify which entity has been modified e.g.
Party has been updated, with Data containing the updated data.

## Workflow

If adding a feature to the application that relates to case(s)
and therefore must be logged:

* Implement feature
* Create entity listener extending `AbstractEntityListener`
* Listen to doctrine events in entity listener
  and call `logActivity($action, $args)`
* Add entity listener to entity if not already there

We keep the following structure:

```sh
/project_root
  /src
    /Entity
      SomeEntity.php
    /Logging
      /EntityListener
        AbstractEntityListener.php
        SomeEntityListener.php
      
```

### Events

The doctrine events we are interested in are

* `postRemove`
* `postUpdate`
* `postLoad`
* `postPersist`

All doctrine lifecycle events are listed
[here](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html#lifecycle-events).

### EventListener

We will keep a listener for each entity that in any shape or form
relates to a case, as any changes to such entity must be logged
on the respective case.

### Party entity example

The following is an example of how to log party updates:

#### Creating the EntityListener

```php
<?php

namespace App\EntityListener;

use App\Entity\Party;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class PartyListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    // Listening to doctrine events, here postUpdate
    
    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(Party $party, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }
}
```

#### Adding the EntityListener

```php
<?php

namespace App\Entity;

/**
 * ..
 * @ORM\EntityListeners({"App\Logging\EntityListener\PartyListener"})
 * ..
 */
class Party
{
    // ....
}
```

## Useful links

Both links beneath have documentation on
Doctrine Events &  Listeners.

* [Doctrine Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html)
* [Symfony Documentation](https://symfony.com/doc/current/doctrine/events.html)
